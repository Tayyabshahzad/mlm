<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SavingAccountService;
use Illuminate\Console\Command;

class ProcessSavingROI extends Command
{
    protected $signature = 'roi:process-saving
                            {--user-id= : Process ROI for a specific saving user ID}
                            {--date=    : Process ROI for a specific date (Y-m-d), e.g. 2026-05-02}
                            {--dry-run  : Preview eligible users without processing}';

    protected $description = 'Process daily saving account ROI for eligible saving users';

    public function __construct(private SavingAccountService $savingAccountService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $userId  = $this->option('user-id');
        $dryRun  = $this->option('dry-run');
        $forDate = $this->option('date')
            ? \Carbon\Carbon::parse($this->option('date'))->startOfDay()
            : \Carbon\Carbon::today();

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        $this->info('Processing saving ROI for: ' . $forDate->toDateString());

        if ($userId) {
            return $this->processSingle((int) $userId, $dryRun, $forDate);
        }

        return $this->processAll($dryRun, $forDate);
    }

    private function processSingle(int $userId, bool $dryRun, \Carbon\Carbon $forDate): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User ID {$userId} not found.");
            return Command::FAILURE;
        }

        $isSavingParticipant = $user->account_type === 'saving'
            || ($user->saving_enrolled && $user->saving_initial_payment > 0);

        if (!$isSavingParticipant) {
            $this->error("User {$user->username} is not a saving plan participant.");
            return Command::FAILURE;
        }

        $this->info("Processing saving ROI for: {$user->username} (id={$user->id})");

        if ($dryRun) {
            $eligible = $this->savingAccountService->canReceiveSavingRoi($user);
            $this->line($eligible ? '  ✓ Eligible' : '  ✗ Not eligible');
            return Command::SUCCESS;
        }

        $result = $this->savingAccountService->processSavingRoi($user, $forDate);
        $this->printResult($user, $result);

        return Command::SUCCESS;
    }

    private function processAll(bool $dryRun, \Carbon\Carbon $forDate): int
    {
        // Include both dedicated saving account users AND enrolled standard users
        // who actually signed up (saving_initial_payment > 0).
        // Auto-enrolled sponsors (saving_initial_payment = 0) are excluded.
        $users = User::where(function ($q) {
                $q->where('account_type', 'saving')
                  ->orWhere(function ($q2) {
                      $q2->where('saving_enrolled', true)
                         ->where('saving_initial_payment', '>', 0);
                  });
            })
            ->where('saving_registration_completed', true)
            ->where('blocked', false)
            ->where('freez_wallet', false)
            ->where('saving_total_deposited', '>', 0)
            ->where(function ($q) {
                // Dedicated saving users need can_login; enrolled users need saving_enrollment_activated
                $q->where(function ($q2) {
                    $q2->where('account_type', 'saving')->where('can_login', true);
                })->orWhere(function ($q2) {
                    $q2->where('saving_enrolled', true)->where('saving_enrollment_activated', true);
                });
            })
            ->get();

        $this->info("Found {$users->count()} eligible saving account users.");

        if ($dryRun) {
            $this->table(
                ['ID', 'Username', 'Deposited', 'Can Receive ROI'],
                $users->map(fn ($u) => [
                    $u->id,
                    $u->username,
                    '$' . number_format($u->saving_total_deposited, 2),
                    $this->savingAccountService->canReceiveSavingRoi($u) ? 'Yes' : 'No (already paid today or plan ended)',
                ])
            );
            return Command::SUCCESS;
        }

        $processed = 0;
        $skipped   = 0;
        $total     = 0;

        foreach ($users as $user) {
            $result = $this->savingAccountService->processSavingRoi($user, $forDate);
            $this->printResult($user, $result);

            if ($result['success']) {
                $processed++;
                $total += $result['amount'];
            } else {
                $skipped++;
            }
        }

        $this->newLine();
        $this->info("Done. Processed: {$processed} | Skipped: {$skipped} | Total ROI: $" . number_format($total, 2));

        return Command::SUCCESS;
    }

    private function printResult(User $user, array $result): void
    {
        if ($result['success']) {
            $this->line("  ✓ {$user->username} — ROI: $" . number_format($result['amount'], 2));
        } else {
            $this->line("  ✗ {$user->username} — Skipped: {$result['message']}");
        }
    }
}
