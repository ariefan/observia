<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Audit;

echo "=== CHECKING AUDIT RECORDS FOR DATA RECOVERY ===\n\n";

// Check deletions today
$deletedToday = Audit::where('event', 'deleted')
    ->whereDate('created_at', today())
    ->get(['auditable_type', 'auditable_id', 'old_values', 'created_at']);

echo "Records DELETED today: " . $deletedToday->count() . "\n";

$deletedToday->groupBy('auditable_type')->each(function ($audits, $type) {
    echo "- $type: " . $audits->count() . " deleted\n";
});

echo "\n=== SAMPLE DELETED RECORDS (for restoration) ===\n";

// Show some deleted livestock records
$deletedLivestock = $deletedToday->where('auditable_type', 'App\Models\Livestock')->take(3);

foreach ($deletedLivestock as $audit) {
    echo "Livestock ID: {$audit->auditable_id}\n";
    echo "Deleted at: {$audit->created_at}\n";
    echo "Old values: " . (is_string($audit->old_values) ? $audit->old_values : json_encode($audit->old_values)) . "\n";
    echo "---\n";
}

echo "\n=== RECOVERY POSSIBLE ===\n";
echo "We can restore from these audit records!\n";
