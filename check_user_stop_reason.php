<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('username', 'Sahibzada2727')->first();

echo "User: " . $user->username . " (ID: " . $user->id . ")\n";
echo "ROI Status: " . ($user->roi_status ?? 'NULL') . "\n";
echo "Stop Reason: " . ($user->stop_reason ?? 'NULL') . "\n";
echo "ROI Start Date: " . ($user->roi_start_date ?? 'NULL') . "\n";
echo "ROI End Date: " . ($user->roi_end_date ?? 'NULL') . "\n";
echo "\n";

// Check if user can receive ROI
$accountService = app(App\Services\AccountManagementService::class);
$canReceive = $accountService->canReceiveRoi($user);

echo "Can Receive ROI: " . ($canReceive ? 'YES' : 'NO') . "\n";
echo "\n";

// Get ROI stats
$stats = $accountService->getRoiAccountStats($user);
echo "=== ROI Account Stats ===\n";
echo "Total ROI Paid: $" . number_format($stats['total_roi_paid'], 2) . "\n";
echo "2X Limit: $" . number_format($stats['two_x_limit'], 2) . "\n";
echo "Remaining to 2X: $" . number_format($stats['remaining_to_2x'], 2) . "\n";
echo "Has Reached 2X: " . ($stats['has_reached_2x'] ? 'YES' : 'NO') . "\n";