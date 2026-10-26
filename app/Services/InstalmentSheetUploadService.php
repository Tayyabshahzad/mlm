<?php

namespace App\Services;

use App\Models\BankPaymentReconciliation;
use App\Models\InstalmentPaymentAudit;
use App\Models\InstalmentUploadBatch;
use App\Models\InstalmentUploadRow;
use App\Models\SavingInstalment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Payment sheet upload (admin / data entry operator).
 *
 * Columns: username (required), email (optional), txn id (optional), amount (required).
 * Each valid row submits the member's next unpaid saving instalment — status
 * "submitted", exactly like a member's own submission — so the admin still
 * confirms it from Pending Confirmations.
 *
 * All-or-nothing: the whole sheet is validated first; if any row has a problem
 * nothing is saved. The write itself runs in one transaction, so an unexpected
 * failure also rolls everything back.
 */
class InstalmentSheetUploadService
{
    public const PAYMENT_METHOD = 'sheet_upload';
    public const MAX_ROWS = 1000;

    private const USERNAME_KEYS = ['username', 'user_name', 'user'];
    private const EMAIL_KEYS    = ['email', 'email_address'];
    private const TXN_KEYS      = ['txn_id', 'txnid', 'txn', 'transaction_id', 'transaction', 'trx_id', 'tid'];
    private const AMOUNT_KEYS   = ['amount', 'amount_usd', 'paid_amount'];

    /** @var string[] required header columns missing from the last sheet read */
    private array $missingColumns = [];

    public function __construct(
        private SavingAccountService $savingAccountService,
        private DigitalCardService $digitalCardService,
    ) {}

    /**
     * @return array{ok:bool, batch?:InstalmentUploadBatch, errors?:array<int,array{row:int,username:string,message:string}>}
     */
    public function upload(UploadedFile $file, User $uploader): array
    {
        // Copy the upload into our own private storage first and read it from
        // there. On Windows/fcgid the PHP upload temp file can sit in
        // C:\Windows\Temp where realpath() fails, so anything relying on
        // UploadedFile::getRealPath() (Laravel Excel, storeAs) breaks with
        // "Path cannot be empty". getPathname() + our own copy avoids that.
        $ext      = strtolower($file->getClientOriginalExtension() ?: 'xlsx');
        $incoming = 'payment-sheets/incoming/' . Str::uuid() . '.' . $ext;
        $this->stageUpload($file, $incoming);

        $storedPath = null;
        try {
            $rows   = $this->readSheet($incoming);
            $errors = $this->missingColumns
                ? [['row' => 1, 'username' => '', 'message' => 'Missing required column(s): ' . implode(', ', $this->missingColumns) . '. Use the template: Username, Email, Txn ID, Amount.']]
                : $this->validate($rows);

            if ($errors) {
                Storage::disk('local')->delete($incoming);
                $this->audit(InstalmentPaymentAudit::SHEET_REJECTED, $uploader,
                    "Payment sheet \"{$file->getClientOriginalName()}\" rejected: " . count($errors) . ' problem(s); nothing was saved.',
                    ['file' => $file->getClientOriginalName(), 'rows' => count($rows), 'errors' => array_slice($errors, 0, 50)]);

                return ['ok' => false, 'errors' => $errors];
            }

            $batch = DB::transaction(function () use ($rows, $file, $uploader, $incoming, $ext, &$storedPath) {
                $batch = InstalmentUploadBatch::create([
                    'uploaded_by'        => $uploader->id,
                    'original_file_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                    'rows_count'         => count($rows),
                    'total_amount'       => round(array_sum(array_column($rows, 'amount')), 2),
                ]);

                foreach ($rows as $row) {
                    // Re-check under a row lock — something may have changed since validation.
                    $inst = SavingInstalment::whereKey($row['instalment_id'])->lockForUpdate()->first();
                    if (!$inst || $inst->status !== 'pending' && $inst->status !== 'missed') {
                        throw new \RuntimeException("Row {$row['row']} ({$row['username']}): instalment changed while uploading. Nothing was saved — please upload again.");
                    }

                    $txn = $row['txn_id'] !== '' ? $row['txn_id'] : "SHEET-{$batch->id}-{$row['row']}";

                    $inst->update([
                        'status'           => 'submitted',
                        'transaction_id'   => $txn,
                        'payment_method'   => self::PAYMENT_METHOD,
                        'submitted_amount' => $row['amount'],
                        'submitted_at'     => now(),
                        'notes'            => "Entered by @{$uploader->username} via payment sheet #{$batch->id} (row {$row['row']}).",
                    ]);

                    InstalmentUploadRow::create([
                        'batch_id'             => $batch->id,
                        'row_number'           => $row['row'],
                        'user_id'              => $row['user_id'],
                        'saving_instalment_id' => $inst->id,
                        'username'             => $row['username'],
                        'email'                => $row['email'] !== '' ? $row['email'] : null,
                        'txn_id'               => $row['txn_id'] !== '' ? $row['txn_id'] : null,
                        'amount'               => $row['amount'],
                    ]);
                }

                // Keep the original sheet with the batch
                $storedPath = "payment-sheets/batch-{$batch->id}-" . now()->format('YmdHis') . '.' . $ext;
                Storage::disk('local')->move($incoming, $storedPath);
                $batch->update(['stored_path' => $storedPath]);

                $this->audit(InstalmentPaymentAudit::SHEET_UPLOADED, $uploader,
                    "Payment sheet #{$batch->id} \"{$batch->original_file_name}\": " . count($rows) . ' instalment(s) submitted for admin confirmation, total $' . number_format((float) $batch->total_amount, 2) . '.',
                    ['batch_id' => $batch->id, 'rows' => count($rows), 'total' => (float) $batch->total_amount]);

                return $batch;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete(array_filter([$incoming, $storedPath]));
            throw $e;
        }

        return ['ok' => true, 'batch' => $batch];
    }

    /** Copy the uploaded file into private storage using its plain temp path (no realpath()). */
    private function stageUpload(UploadedFile $file, string $target): void
    {
        $source   = $file->getPathname();
        $contents = $source !== '' && is_file($source) ? file_get_contents($source) : false;

        if ($contents === false || $contents === '') {
            throw new \RuntimeException('The uploaded file could not be read. Please choose the file again and re-upload.');
        }

        Storage::disk('local')->put($target, $contents);
    }

    /**
     * Read the first worksheet into normalised rows. Header names are matched
     * loosely ("Txn ID", "Transaction ID", "txn_id" …). Fully empty rows are skipped.
     *
     * @return array<int, array{row:int, username:string, email:string, txn_id:string, amount_raw:string}>
     */
    public function readSheet(string $path, string $disk = 'local'): array
    {
        $sheets = Excel::toArray(new class implements WithHeadingRow {}, $path, $disk);
        $data   = $sheets[0] ?? [];

        // Required header columns present?
        $this->missingColumns = [];
        if ($data) {
            $headers = array_keys($data[0]);
            if (!array_intersect(self::USERNAME_KEYS, $headers)) {
                $this->missingColumns[] = 'Username';
            }
            if (!array_intersect(self::AMOUNT_KEYS, $headers)) {
                $this->missingColumns[] = 'Amount';
            }
        }

        $rows = [];
        foreach ($data as $i => $raw) {
            $get = function (array $keys) use ($raw): string {
                foreach ($keys as $k) {
                    if (array_key_exists($k, $raw) && $raw[$k] !== null) {
                        return trim((string) $raw[$k]);
                    }
                }
                return '';
            };

            $row = [
                'row'        => $i + 2, // header is row 1
                'username'   => $get(self::USERNAME_KEYS),
                'email'      => $get(self::EMAIL_KEYS),
                'txn_id'     => $get(self::TXN_KEYS),
                'amount_raw' => $get(self::AMOUNT_KEYS),
            ];

            if ($row['username'] === '' && $row['email'] === '' && $row['txn_id'] === '' && $row['amount_raw'] === '') {
                continue;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Validate every row (fills user_id / instalment_id / amount on success).
     *
     * @return array<int, array{row:int, username:string, message:string}>
     */
    public function validate(array &$rows): array
    {
        $errors = [];
        $fail   = function (array $row, string $msg) use (&$errors) {
            $errors[] = ['row' => $row['row'], 'username' => $row['username'], 'message' => $msg];
        };

        if (!$rows) {
            return [['row' => 0, 'username' => '', 'message' => 'The sheet has no data rows. Required columns: username, amount (optional: email, txn id).']];
        }
        if (count($rows) > self::MAX_ROWS) {
            return [['row' => 0, 'username' => '', 'message' => 'Too many rows (' . count($rows) . '). Maximum ' . self::MAX_ROWS . ' per sheet.']];
        }

        $usernameCount = array_count_values(array_map(fn ($r) => mb_strtolower($r['username']), array_filter($rows, fn ($r) => $r['username'] !== '')));
        $txnKeys = array_map(fn ($r) => $r['txn_id'] !== '' ? BankPaymentReconciliation::normaliseReference($r['txn_id']) : null, $rows);
        $txnCount = array_count_values(array_filter($txnKeys));

        foreach ($rows as $i => &$row) {
            $before = count($errors);

            // username
            $user = null;
            if ($row['username'] === '') {
                $fail($row, 'Username is required.');
            } else {
                if (($usernameCount[mb_strtolower($row['username'])] ?? 0) > 1) {
                    $fail($row, 'This username appears more than once in the sheet.');
                }
                $user = User::where('username', $row['username'])->first();
                if (!$user) {
                    $fail($row, 'Username not found.');
                } elseif ($user->blocked) {
                    $fail($row, 'This member is blocked.');
                }
            }

            // email (optional, must match the member if given)
            if ($row['email'] !== '' && $user && strcasecmp($row['email'], (string) $user->email) !== 0) {
                $fail($row, 'Email does not match this username.');
            }

            // amount
            $amountClean = str_replace([',', '$', ' '], '', $row['amount_raw']);
            if ($row['amount_raw'] === '') {
                $fail($row, 'Amount is required.');
            } elseif (!is_numeric($amountClean) || (float) $amountClean <= 0) {
                $fail($row, "Amount \"{$row['amount_raw']}\" is not a valid positive number.");
            }
            $row['amount'] = is_numeric($amountClean) ? round((float) $amountClean, 2) : 0.0;

            // txn id (optional, must be unique)
            if ($row['txn_id'] !== '') {
                if (mb_strlen($row['txn_id']) > 100) {
                    $fail($row, 'Txn ID is longer than 100 characters.');
                } elseif (($txnCount[$txnKeys[$i]] ?? 0) > 1) {
                    $fail($row, "Txn ID \"{$row['txn_id']}\" appears more than once in the sheet.");
                } elseif ($where = $this->txnInUse($row['txn_id'])) {
                    $fail($row, "Txn ID \"{$row['txn_id']}\" is {$where}.");
                }
            }

            // instalment
            if ($user && !$user->blocked) {
                if (!$this->digitalCardService->userHasSavingPlan($user)) {
                    $fail($row, 'This member is not on a saving instalment plan.');
                } elseif ($awaiting = SavingInstalment::where('user_id', $user->id)->where('status', 'submitted')->orderBy('instalment_number')->first()) {
                    $fail($row, "Instalment #{$awaiting->instalment_number} is already awaiting admin confirmation — confirm or reject it first.");
                } elseif (!$next = $this->savingAccountService->nextDueInstalment($user)) {
                    $fail($row, 'This member has no unpaid instalment.');
                } elseif (is_numeric($amountClean) && (float) $amountClean > 0) {
                    $required = $this->savingAccountService->requiredInstalmentTotal($next);
                    if (round((float) $amountClean, 2) !== round($required, 2)) {
                        $fail($row, 'Amount $' . number_format((float) $amountClean, 2) . " does not match instalment #{$next->instalment_number} (\$" . number_format($required, 2) . ' required).');
                    }
                    $row['instalment_id'] = $next->id;
                }
            }

            if (count($errors) === $before) {
                $row['user_id'] = $user->id;
            }
        }
        unset($row);

        return $errors;
    }

    /**
     * Dashboard figures for one uploader's payment sheets.
     */
    public function statsFor(User $uploader): array
    {
        $exp = InstalmentUploadRow::EXPECTED_TXN_SQL;

        $agg = DB::table('instalment_upload_rows as r')
            ->join('instalment_upload_batches as b', 'b.id', '=', 'r.batch_id')
            ->leftJoin('saving_instalments as si', 'si.id', '=', 'r.saving_instalment_id')
            ->where('b.uploaded_by', $uploader->id)
            ->selectRaw("
                COUNT(*) AS records,
                COALESCE(SUM(r.amount), 0) AS amount,
                SUM(CASE WHEN si.status = 'confirmed' AND si.transaction_id = {$exp} THEN 1 ELSE 0 END) AS confirmed,
                COALESCE(SUM(CASE WHEN si.status = 'confirmed' AND si.transaction_id = {$exp} THEN r.amount END), 0) AS confirmed_amount,
                SUM(CASE WHEN si.status = 'submitted' AND si.transaction_id = {$exp} THEN 1 ELSE 0 END) AS awaiting,
                COALESCE(SUM(CASE WHEN si.status = 'submitted' AND si.transaction_id = {$exp} THEN r.amount END), 0) AS awaiting_amount,
                SUM(CASE WHEN b.created_at >= ? THEN 1 ELSE 0 END) AS month_records,
                COALESCE(SUM(CASE WHEN b.created_at >= ? THEN r.amount END), 0) AS month_amount
            ", [now()->startOfMonth(), now()->startOfMonth()])
            ->first();

        $records  = (int) $agg->records;
        $rejected = $records - (int) $agg->confirmed - (int) $agg->awaiting;

        $sheets = InstalmentUploadBatch::where('uploaded_by', $uploader->id);

        return [
            'sheets'           => (clone $sheets)->count(),
            'sheets_today'     => (clone $sheets)->whereDate('created_at', today())->count(),
            'rejected_uploads' => InstalmentPaymentAudit::where('action', InstalmentPaymentAudit::SHEET_REJECTED)->where('actor_id', $uploader->id)->count(),
            'records'          => $records,
            'amount'           => (float) $agg->amount,
            'confirmed'        => (int) $agg->confirmed,
            'confirmed_amount' => (float) $agg->confirmed_amount,
            'awaiting'         => (int) $agg->awaiting,
            'awaiting_amount'  => (float) $agg->awaiting_amount,
            'rejected'         => $rejected,
            'rejected_amount'  => round((float) $agg->amount - (float) $agg->confirmed_amount - (float) $agg->awaiting_amount, 2),
            'month_records'    => (int) $agg->month_records,
            'month_amount'     => (float) $agg->month_amount,
            'success_rate'     => $records ? round((int) $agg->confirmed / $records * 100, 1) : 0,
        ];
    }

    /** Where a transaction id is already used (instalment or bank reconciliation), if anywhere. */
    private function txnInUse(string $txn): ?string
    {
        $key = BankPaymentReconciliation::normaliseReference($txn);

        $inst = SavingInstalment::with('user:id,username')
            ->whereIn('status', ['submitted', 'confirmed'])
            ->whereRaw("UPPER(REPLACE(transaction_id, ' ', '')) = ?", [$key])
            ->first();
        if ($inst) {
            return "already used on instalment #{$inst->instalment_number} of @{$inst->user?->username}";
        }

        return BankPaymentReconciliation::where('reference_key', $key)->exists()
            ? 'already used on a verified bank payment'
            : null;
    }

    private function audit(string $action, User $uploader, string $description, array $details): void
    {
        InstalmentPaymentAudit::create([
            'action'      => $action,
            'actor_id'    => $uploader->id,
            'description' => mb_substr($description, 0, 1000),
            'details'     => $details,
            'ip_address'  => app()->runningInConsole() ? null : Request::ip(),
        ]);
    }
}
