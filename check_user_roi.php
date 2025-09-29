<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$user = App\Models\User::where('username', 'Sahibzada2727')->first();

echo "=== Recent ROI Entries ===\n";
$roiEntries = App\Models\Wallet::where('user_id', $user->id)
    ->where('wallet_type', 'roi')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

foreach ($roiEntries as $entry) {
    echo $entry->created_at->format('Y-m-d H:i:s') . ' | $' . number_format($entry->balance, 2) .
         ' | ' . $entry->percentage . '% | Source: ' . ($entry->source_type ?? 'N/A') . "\n";
}

echo "\n=== ROI Transactions ===\n";
$roiTransactions = App\Models\ROITransaction::where('user_id', $user->id)
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($roiTransactions as $trans) {
    echo $trans->created_at->format('Y-m-d H:i:s') . ' | $' . number_format($trans->amount, 2) .
         ' | ' . $trans->percentage . '% | Trigger: ' . ($trans->trigger_reason ?? 'N/A') . "\n";
}