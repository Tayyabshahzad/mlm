<?php

namespace App\Console\Commands;

use App\Models\SavingInstalment;
use App\Models\SavingInstalmentCommission;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Scans all confirmed Saving Plan instalments and creates pending commission
 * records in saving_instalment_commissions for every eligible upline that has
 * NOT already received a commission for that instalment.
 *
 * The records are created with status = 'pending' so an admin can review them
 * in Admin → Saving Accounts → Instalment Commissions before sending.
 *
 * Use --pay to also pay them immediately (creates wallet entries).
 *
 * This command is IDEMPOTENT: re-running it will never create duplicates because
 * the unique constraint on (saving_instalment_id, ancestor_id, level) prevents it.
 */
class GenerateMissedSavingCommissions extends Command
{
    protected $signature = 'saving-plan:generate-missed-commissions
                            {--dry-run  : Preview what would be created without writing anything}
                            {--pay      : Also pay the generated commissions immediately (create wallet entries)}
                            {--user-id= : Restrict to a single member (by user ID)}
                            {--instalment-id= : Restrict to a single instalment (by ID)}';

    protected $description = 'Backfill missing Saving Plan instalment commissions (instalments #2+) for all eligible uplines.';

    // Saving-tree commission rates (fallback when settings table has no value)
    private const DEFAULT_RATES = [
        1 => 7.00, 2 => 2.00, 3 => 1.00,
        4 => 1.00, 5 => 1.00, 6 => 1.00, 7 => 1.00,
    ];

    // 7x7 rule: level N requires N direct saving-tree referrals
    private const TEAM_REQUIREMENTS = [
        1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7,
    ];

    public function handle(): int
    {
        $isDryRun  = $this->option('dry-run');
        $shouldPay = $this->option('pay') && !$isDryRun;

        if ($isDryRun) {
            $this->warn('DRY RUN — nothing will be written to the database.');
        } elseif ($shouldPay) {
            $this->warn('PAY MODE — commissions will be created AND paid immediately.');
        } else {
            $this->info('PENDING MODE — commission records will be created with status = pending.');
            $this->info('  Use Admin → Saving → Instalment Commissions to review and send them.');
        }

        $this->newLine();

        $setting = Setting::first();

        // ── Build instalment query ──────────────────────────────────────────────
        $query = SavingInstalment::with('user')
            ->where('status', 'confirmed')
            ->whereNotNull('deposited_at')
            ->where('instalment_number', '>', 1); // instalment #1 commissions fire on activation

        if ($userId = $this->option('user-id')) {
            $query->where('user_id', (int) $userId);
        }

        if ($instalmentId = $this->option('instalment-id')) {
            $query->where('id', (int) $instalmentId);
        }

        $instalments = $query->orderBy('user_id')->orderBy('instalment_number')->get();

        if ($instalments->isEmpty()) {
            $this->info('No confirmed instalments found matching the given criteria.');
            return self::SUCCESS;
        }

        $this->info("Scanning {$instalments->count()} confirmed instalment(s)...");
        $this->newLine();

        // ── Per-instalment processing ───────────────────────────────────────────
        $totalUsersProcessed    = 0;
        $totalInstalmentsScanned = 0;
        $totalRecordsCreated    = 0;
        $totalAmountQueued      = 0.0;
        $lastUserId             = null;

        $previewRows = [];

        foreach ($instalments as $instalment) {
            $user = $instalment->user;

            if (!$user) {
                continue;
            }

            // Account must be active (can_login for dedicated saving users;
            // saving_enrollment_activated for enrolled standard users).
            $isActive = ($user->account_type === 'saving' && $user->can_login)
                || ($user->saving_enrolled && $user->saving_enrollment_activated);

            if (!$isActive) {
                continue;
            }

            if (!$user->saving_registration_completed) {
                continue;
            }

            if ($user->id !== $lastUserId) {
                $totalUsersProcessed++;
                $lastUserId = $user->id;
            }

            $totalInstalmentsScanned++;

            // Commission base: gross instalment amount (instalment #2–25 only)
            $baseAmount = (float) ($instalment->submitted_amount ?? $instalment->amount);

            // Fetch saving-tree ancestors (levels 1–7)
            $ancestors = DB::table('referral_trees')
                ->select('ancestor_id', 'level')
                ->where('descendant_id', $user->id)
                ->where('tree_type', 'saving')
                ->where('level', '>=', 1)
                ->where('level', '<=', 7)
                ->orderBy('level')
                ->get();

            foreach ($ancestors as $ancestor) {
                $ancestorUser = User::where('blocked', false)->find($ancestor->ancestor_id);
                if (!$ancestorUser) {
                    continue;
                }

                if ($ancestorUser->account_type === 'saving' && !$ancestorUser->saving_registration_completed) {
                    continue;
                }

                // 7x7 qualification check
                $required = self::TEAM_REQUIREMENTS[$ancestor->level] ?? 0;
                $directs  = DB::table('referral_trees')
                    ->where('ancestor_id', $ancestorUser->id)
                    ->where('level', 1)
                    ->where('tree_type', 'saving')
                    ->count();

                if ($directs < $required) {
                    continue;
                }

                // Determine commission rate
                $fieldName  = "saving_commission_l{$ancestor->level}";
                $percentage = $setting && isset($setting->$fieldName)
                    ? (float) $setting->$fieldName
                    : (self::DEFAULT_RATES[$ancestor->level] ?? 0);

                if ($percentage <= 0) {
                    continue;
                }

                $commissionAmount = round(($baseAmount * $percentage) / 100, 4);
                $type             = $ancestor->level === 1 ? 'direct' : 'indirect';

                // ── Step 1: already tracked by the new system? ──────────────────
                $alreadyTracked = DB::table('saving_instalment_commissions')
                    ->where('saving_instalment_id', $instalment->id)
                    ->where('ancestor_id', $ancestorUser->id)
                    ->where('level', $ancestor->level)
                    ->exists();

                if ($alreadyTracked) {
                    continue;
                }

                // ── Step 2: already paid by the OLD system with a non-zero amount?
                // The old system fired instalment #1 commissions and stored them in
                // the wallets table without any tracking record.
                // We only treat a wallet entry as "already paid" if balance > 0.
                // $0 entries exist because commission rates were 0 in settings at the
                // time — those are treated as "not paid" and will be included in the backfill.
                $oldWalletEntry = DB::table('wallets')
                    ->where('user_id',     $ancestorUser->id)  // recipient = ancestor
                    ->where('wallet_from', $user->id)           // source    = this member
                    ->where('wallet_type', 'direct_indirect')
                    ->where('source_type', 'saving_instalment')
                    ->where('level',       (string) $ancestor->level) // stored as string
                    ->where('balance',     '>', 0)              // only genuine payments
                    ->first();

                if ($oldWalletEntry) {
                    // Commission was already paid by the old system with a real amount.
                    // Create a reconciliation record (status = paid) so the tracking
                    // table is complete — but do NOT create another wallet entry.
                    if (!$isDryRun) {
                        try {
                            DB::table('saving_instalment_commissions')->insertOrIgnore([
                                'saving_instalment_id' => $instalment->id,
                                'user_id'              => $user->id,
                                'ancestor_id'          => $ancestorUser->id,
                                'level'                => $ancestor->level,
                                'instalment_number'    => $instalment->instalment_number,
                                'commission_amount'    => $oldWalletEntry->balance,
                                'percentage'           => $percentage,
                                'commission_type'      => $type,
                                'status'               => 'paid',
                                'wallet_id'            => $oldWalletEntry->id,
                                'processed_at'         => $oldWalletEntry->created_at,
                                'notes'                => 'Reconciled from old system payment (pre-tracking)',
                                'created_at'           => now(),
                                'updated_at'           => now(),
                            ]);
                        } catch (\Illuminate\Database\QueryException $e) {
                            if ($e->errorInfo[1] != 1062) {
                                throw $e;
                            }
                        }
                    }
                    // Don't count reconciled records as "new" — they were already paid
                    continue;
                }

                // ── Step 3: genuinely missing — commission was never paid ─────────
                // ── DRY RUN: just collect preview rows ──────────────────────────
                if ($isDryRun) {
                    $previewRows[] = [
                        $user->username,
                        "#{$instalment->instalment_number}",
                        '$' . number_format($baseAmount, 2),
                        $ancestorUser->username,
                        "L{$ancestor->level}",
                        "{$percentage}%",
                        '$' . number_format($commissionAmount, 4),
                    ];
                    $totalRecordsCreated++;
                    $totalAmountQueued += $commissionAmount;
                    continue;
                }

                // ── WRITE: insert pending record ────────────────────────────────
                try {
                    DB::table('saving_instalment_commissions')->insert([
                        'saving_instalment_id' => $instalment->id,
                        'user_id'              => $user->id,
                        'ancestor_id'          => $ancestorUser->id,
                        'level'                => $ancestor->level,
                        'instalment_number'    => $instalment->instalment_number,
                        'commission_amount'    => $commissionAmount,
                        'percentage'           => $percentage,
                        'commission_type'      => $type,
                        'status'               => 'pending',
                        'processed_at'         => null,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);

                    $totalRecordsCreated++;
                    $totalAmountQueued += $commissionAmount;

                    // ── PAY immediately if --pay flag set ──────────────────────
                    if ($shouldPay) {
                        $record = SavingInstalmentCommission::where('saving_instalment_id', $instalment->id)
                            ->where('ancestor_id', $ancestorUser->id)
                            ->where('level', $ancestor->level)
                            ->first();

                        if ($record) {
                            $wallet = Wallet::create([
                                'user_id'          => $ancestorUser->id,
                                'wallet_type'      => 'direct_indirect',
                                'balance'          => $commissionAmount,
                                'direct_balance'   => $type === 'direct' ? $commissionAmount : 0.00,
                                'indirect_balance' => $type === 'indirect' ? $commissionAmount : 0.00,
                                'total_amount'     => $commissionAmount,
                                'level'            => $ancestor->level,
                                'wallet_from'      => $user->id,
                                'commission_type'  => $type,
                                'percentage'       => $percentage,
                                'source_type'      => 'saving_instalment',
                                'description'      => "Backfilled saving commission — instalment #{$instalment->instalment_number}",
                                'created_at'       => now(),
                                'updated_at'       => now(),
                            ]);

                            $record->update([
                                'status'       => 'paid',
                                'wallet_id'    => $wallet->id,
                                'processed_at' => now(),
                            ]);
                        }
                    }
                } catch (\Illuminate\Database\QueryException $e) {
                    if ($e->errorInfo[1] == 1062) {
                        // Another run already inserted this — skip silently
                        continue;
                    }
                    Log::error("GenerateMissedSavingCommissions DB error: " . $e->getMessage());
                    $this->error("DB error for instalment {$instalment->id} ancestor {$ancestorUser->id}: " . $e->getMessage());
                }
            }
        }

        // ── Output ─────────────────────────────────────────────────────────────
        if ($isDryRun && !empty($previewRows)) {
            $this->table(
                ['Member', 'Instalment', 'Base Amount', 'Upline', 'Level', 'Rate', 'Commission'],
                $previewRows
            );
        }

        $this->newLine();
        $this->info('══════════════════════ Summary ══════════════════════');
        $this->line("  Users processed    : {$totalUsersProcessed}");
        $this->line("  Instalments scanned: {$totalInstalmentsScanned}");

        if ($isDryRun) {
            $this->line("  Records to create  : {$totalRecordsCreated}");
            $this->line("  Total amount       : \$" . number_format($totalAmountQueued, 4));
            $this->warn('  Run without --dry-run to apply changes.');
        } else {
            $action = $shouldPay ? 'paid' : 'queued as pending';
            $this->line("  Records {$action}  : {$totalRecordsCreated}");
            $this->line("  Total amount       : \$" . number_format($totalAmountQueued, 4));
            if (!$shouldPay && $totalRecordsCreated > 0) {
                $this->info('  Visit Admin → Saving → Instalment Commissions to review and send.');
            }
        }

        return self::SUCCESS;
    }
}
