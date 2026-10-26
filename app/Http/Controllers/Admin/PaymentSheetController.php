<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InstalmentPaymentAudit;
use App\Models\InstalmentUploadBatch;
use App\Models\InstalmentUploadRow;
use App\Services\InstalmentSheetUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Payment sheet upload — admins and data entry operators record payments the
 * owner received on behalf of members. Instalments go to admin confirmation.
 */
class PaymentSheetController extends Controller
{
    public function __construct(private InstalmentSheetUploadService $sheets) {}

    public function index()
    {
        $user = Auth::user();

        $batches = InstalmentUploadBatch::with(['uploader:id,name,username', 'rows.instalment:id,status,transaction_id'])
            ->when(!$user->hasRole('admin'), fn ($q) => $q->where('uploaded_by', $user->id)) // operators see their own sheets
            ->latest('id')
            ->paginate(20);

        return view('admin.payment-sheets.index', compact('batches'));
    }

    /** Data Entry Operator dashboard: only payment-sheet figures for this operator. */
    public function dashboard()
    {
        $user = Auth::user();

        $recentSheets = InstalmentUploadBatch::with('rows.instalment:id,status,transaction_id')
            ->where('uploaded_by', $user->id)
            ->latest('id')->limit(5)->get();

        // Payments from this operator's sheets that the admin rejected (instalment no longer carries the sheet's txn id)
        $exp = InstalmentUploadRow::EXPECTED_TXN_SQL;
        $rejectedRows = InstalmentUploadRow::from('instalment_upload_rows as r')
            ->select('r.*')
            ->join('instalment_upload_batches as b', 'b.id', '=', 'r.batch_id')
            ->leftJoin('saving_instalments as si', 'si.id', '=', 'r.saving_instalment_id')
            ->where('b.uploaded_by', $user->id)
            ->whereRaw("NOT (si.transaction_id <=> {$exp} AND si.status IN ('confirmed', 'submitted'))")
            ->with(['user:id,name,username', 'instalment:id,instalment_number,status,notes'])
            ->latest('r.id')->limit(5)->get();

        $rejectedUploads = InstalmentPaymentAudit::where('action', InstalmentPaymentAudit::SHEET_REJECTED)
            ->where('actor_id', $user->id)
            ->latest('id')->limit(5)->get();

        return view('admin.payment-sheets.dashboard', [
            'stats'           => $this->sheets->statsFor($user),
            'recentSheets'    => $recentSheets,
            'rejectedRows'    => $rejectedRows,
            'rejectedUploads' => $rejectedUploads,
        ]);
    }

    public function template()
    {
        return Excel::download(new class implements FromArray, WithHeadings {
            public function headings(): array { return ['Username', 'Email', 'Txn ID', 'Amount']; }
            public function array(): array { return []; }
        }, 'payment-sheet-template.xlsx');
    }

    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'sheet' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
        ], [
            'sheet.mimes' => 'Please upload an Excel (.xlsx / .xls) or CSV file.',
        ]);

        try {
            $result = $this->sheets->upload($request->file('sheet'), Auth::user());
        } catch (\Throwable $e) {
            Log::error('Payment sheet upload failed: ' . $e->getMessage(), ['exception' => $e]);
            return back()->with('error', 'Upload failed and nothing was saved: ' . $e->getMessage());
        }

        if (!$result['ok']) {
            return back()
                ->with('sheet_errors', $result['errors'])
                ->with('error', 'The sheet was NOT uploaded — ' . count($result['errors']) . ' problem(s) found. Nothing was saved. Fix the rows below and upload again.');
        }

        $batch = $result['batch'];

        return redirect()->route('payment-sheets.show', $batch)
            ->with('success', "Sheet uploaded: {$batch->rows_count} instalment(s) submitted for admin confirmation (total \$" . number_format((float) $batch->total_amount, 2) . ').');
    }

    public function show(InstalmentUploadBatch $batch)
    {
        $this->authorizeBatch($batch);
        $batch->load(['uploader:id,name,username', 'rows.user:id,name,username', 'rows.instalment:id,instalment_number,status,confirmed_at,transaction_id']);

        return view('admin.payment-sheets.show', compact('batch'));
    }

    public function download(InstalmentUploadBatch $batch)
    {
        $this->authorizeBatch($batch);
        abort_unless($batch->stored_path && Storage::disk('local')->exists($batch->stored_path), 404);

        return Storage::disk('local')->download($batch->stored_path, $batch->original_file_name);
    }

    private function authorizeBatch(InstalmentUploadBatch $batch): void
    {
        $user = Auth::user();
        abort_unless($user->hasRole('admin') || (int) $batch->uploaded_by === (int) $user->id, 403);
    }
}
