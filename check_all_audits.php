<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Audit;

echo "=== ALL AUDIT RECORDS ===\n";

// Check all audit records
$totalAudits = Audit::count();
echo "Total audit records: $totalAudits\n\n";

// Check by type
$auditsByType = Audit::selectRaw('auditable_type, event, COUNT(*) as count')
    ->groupBy('auditable_type', 'event')
    ->orderBy('count', 'desc')
    ->get();

echo "Audit records by type and event:\n";
foreach ($auditsByType as $audit) {
    echo "- {$audit->auditable_type} {$audit->event}: {$audit->count}\n";
}

echo "\n=== RECENT AUDITS ===\n";
$recentAudits = Audit::latest()->limit(10)->get(['auditable_type', 'auditable_id', 'event', 'created_at']);

foreach ($recentAudits as $audit) {
    echo "{$audit->created_at}: {$audit->auditable_type} ID {$audit->auditable_id} - {$audit->event}\n";
}
