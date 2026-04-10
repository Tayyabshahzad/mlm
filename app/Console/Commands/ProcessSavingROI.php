<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\SavingAccountService;
use Illuminate\Console\Command;

class ProcessSavingROI extends Command
{
    protected $signature = 'roi:process-saving
                            {--user-id= : Process ROI for a specific saving user ID}
                            {--dry-run  : Preview eligible users without processing}';

    protected $description = 'Process daily saving account ROI for eligible saving users';

    public function __construct(private SavingAccountService $savingAccountService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $userId = $this->option('user-id');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN — no changes will be made.');
        }

        if ($userId) {
            return $this->processSingle((int) $userId, $dryRun);
        }

        return $this->processAll($dryRun);
    }

    private function processSingle(int $userId, bool $dryRun): int
    {
        $user = User::find($userId);

        if (!$user) {
            $this->error("User ID {$userId} not found.");
            return Command::FAILURE;
        }

        if ($user->account_type !== 'saving') {
            $this->error("User {$user->username} is not a saving account user.");
            return Command::FAILURE;
        }

        $this->info("Processing saving ROI for: {$user->username} (id={$user->id})");

        if ($dryRun) {
            $eligible = $this->savingAccountService->canReceiveSavingRoi($user);
            $this->line($eligible ? '  ✓ Eligible' : '  ✗ Not eligible');
            return Command::SUCCESS;
        }

        $result = $this->savingAccountService->processSavingRoi($user);
        $this->printResult($user, $result);

        return Command::SUCCESS;
    }

    private function processAll(bool $dryRun): int
    {
        $users = User::where('account_type', 'saving')
            ->where('can_login', true)
            ->where('saving_registration_completed', true)
            ->where('blocked', false)
            ->where('freez_wallet', false)
            ->where('saving_total_deposited', '>', 0)
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
            $result = $this->savingAccountService->processSavingRoi($user);
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
