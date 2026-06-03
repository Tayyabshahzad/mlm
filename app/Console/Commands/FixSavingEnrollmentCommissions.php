<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\SavingInstalment;
use App\Services\SavingAccountService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSavingEnrollmentCommissions extends Command
{
    protected $signature = 'saving:fix-enrollment-commissions
                            {--dry-run : Preview without making changes}';

    protected $description = 'Activate enrolled standard users whose instalment #1 is confirmed but saving_enrollment_activated is false, then fire missed commissions.';

    public function __construct(private SavingAccountService $savingService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        // Find enrolled standard users whose instalment #1 is confirmed+deposited
        // but saving_enrollment_activated is still false
        $affected = User::where('account_type', '!=', 'saving')
            ->where('saving_enrolled', true)
            ->where('saving_enrollment_activated', false)
            ->whereHas('savingInstalments', function ($q) {
                $q->where('instalment_number', 1)
                  ->where('status', 'confirmed')
                  ->whereNotNull('deposited_at');
            })
            ->get();

        if ($affected->isEmpty()) {
            $this->info('No affected users found. Nothing to fix.');
            return self::SUCCESS;
        }

        $this->info("Found {$affected->count()} affected user(s):\n");

        $rows = [];
        foreach ($affected as $user) {
            $instalment = SavingInstalment::where('user_id', $user->id)
                ->where('instalment_number', 1)
                ->first();

            $alreadyFired = DB::table('wallets')
                ->where('user_id', $user->id)
                ->where('wallet_type', 'direct_indirect')
                ->where('source_type', 'saving_instalment')
                ->exists();

            $rows[] = [
                $user->id,
                $user->username,
                '$' . number_format($instalment->amount, 2),
                $instalment->confirmed_at,
                $alreadyFired ? 'YES (skip)' : 'NO (will fire)',
            ];
        }

        $this->table(
            ['ID', 'Username', 'Inst#1 Amount', 'Confirmed At', 'Commission Already Fired?'],
            $rows
        );

        if ($isDryRun) {
            $this->warn('Dry run complete. Run without --dry-run to apply fixes.');
            return self::SUCCESS;
        }

        if (!$this->confirm('Proceed with fixing these users?', true)) {
            $this->info('Aborted.');
            return self::SUCCESS;
        }

        foreach ($affected as $user) {
            DB::transaction(function () use ($user) {
                // Step 1: activate enrollment
                $user->update([
                    'saving_enrollment_activated'    => true,
                    'saving_enrollment_activated_at' => now(),
                    'saving_enrollment_activated_by' => null,
                ]);
                $user->refresh();

                // Step 2: check if commission already fired for this user
                $alreadyFired = DB::table('wallets')
                    ->where('user_id', $user->id)
                    ->where('wallet_type', 'direct_indirect')
                    ->where('source_type', 'saving_instalment')
                    ->exists();

                if ($alreadyFired) {
                    $this->line("  <comment>SKIPPED commission for {$user->username} — already fired before.</comment>");
                    return;
                }

                // Step 3: calculate correct totalCredit (same logic as creditDepositToWallet)
                $instalment = SavingInstalment::where('user_id', $user->id)
                    ->where('instalment_number', 1)
                    ->first();

                $amount = $instalment->submitted_amount ?? $instalment->amount;

                $registrationPartial = 0.0;
                if ($user->saving_total_deposited == 0) {
                    $savingFee  = (float) ($user->saving_initial_fee ?? 0);
                    $savingPaid = (float) ($user->saving_initial_payment ?? 0);
                    $registrationPartial = max(0.0, $savingPaid - $savingFee);
                }

                $totalCredit = $amount + $registrationPartial;

                // Step 4: fire missed commission
                $this->savingService->assignSavingCommissions($user, $totalCredit);

                $this->line("  <info>FIXED {$user->username} — commission fired on \${$totalCredit}.</info>");
            });
        }

        $this->info("\nDone.");
        return self::SUCCESS;
    }
}
