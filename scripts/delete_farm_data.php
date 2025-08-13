<?php
/**
 * SAFE Deletion Script for Farm Data with Automatic Rollback
 *
 * This script deletes a farm and all its associated data from the database.
 * It is wrapped in a transaction, so if any part of the deletion fails,
 * all changes will be rolled back.
 *
 * Usage: php scripts/delete_farm_data.php <farm_uuid>
 */

// Bootstrap Laravel application
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Farm;

// --- Main Execution ---

// Get the Farm UUID from the command-line argument
if (!isset($argv[1])) {
    echo "💥 Error: Missing Farm UUID.\n";
    echo "Usage: php " . basename(__FILE__) . " <farm_uuid>\n";
    exit(1);
}
$farmUuid = $argv[1];

// Find the farm
$farm = Farm::find($farmUuid);
if (!$farm) {
    echo "💥 Error: Farm with UUID '{$farmUuid}' not found.\n";
    exit(1);
}

// --- Confirmation ---
echo "⚠️  WARNING: This is a destructive action and cannot be undone.\n";
echo "You are about to delete the farm '{$farm->name}' (UUID: {$farmUuid}) and all its related data, including:\n";
echo "  - Herds\n";
echo "  - Livestocks\n";
echo "  - Rations and Feeding History\n";
echo "  - Livestock Health and Weight Records\n";
echo "  - Team Members and Invitations\n\n";

$handle = fopen("php://stdin", "r");
echo "Type 'DELETE' to confirm: ";
$confirmation = trim(fgets($handle));
fclose($handle);

if ($confirmation !== 'DELETE') {
    echo "❌ Deletion cancelled.\n";
    exit(0);
}

// --- Deletion Process ---
$stats = [
    'livestock_weights' => 0,
    'livestock_milkings' => 0,
    'feeding_leftovers' => 0,
    'herd_feedings' => 0,
    'ration_items' => 0,
    'rations' => 0,
    'livestocks' => 0,
    'herds' => 0,
    'farm_users' => 0,
    'farm_invites' => 0,
    'farms' => 0,
];

try {
    DB::beginTransaction();
    echo "\n🔒 Database transaction started.\n";

    // Get IDs of related data
    $herdIds = DB::table('herds')->where('farm_id', $farmUuid)->pluck('id');
    $livestockIds = DB::table('livestocks')->where('farm_id', $farmUuid)->pluck('id');
    $rationIds = DB::table('rations')->where('farm_id', $farmUuid)->pluck('id');
    $herdFeedingIds = DB::table('herd_feedings')->whereIn('herd_id', $herdIds)->pluck('id');

    // Delete dependent records first (reverse order of creation)
    echo "🗑️  Deleting related records...\n";

    $stats['livestock_weights'] = DB::table('livestock_weights')->whereIn('livestock_id', $livestockIds)->delete();
    $stats['livestock_milkings'] = DB::table('livestock_milkings')->whereIn('livestock_id', $livestockIds)->delete();
    $stats['feeding_leftovers'] = DB::table('feeding_leftovers')->whereIn('feeding_id', $herdFeedingIds)->delete();
    $stats['herd_feedings'] = DB::table('herd_feedings')->whereIn('herd_id', $herdIds)->delete();
    $stats['ration_items'] = DB::table('ration_items')->whereIn('ration_id', $rationIds)->delete();
    $stats['rations'] = DB::table('rations')->where('farm_id', $farmUuid)->delete();
    $stats['livestocks'] = DB::table('livestocks')->where('farm_id', $farmUuid)->delete();
    $stats['herds'] = DB::table('herds')->where('farm_id', $farmUuid)->delete();
    $stats['farm_users'] = DB::table('farm_user')->where('farm_id', $farmUuid)->delete();
    $stats['farm_invites'] = DB::table('farm_invites')->where('farm_id', $farmUuid)->delete();
    
    // Finally, delete the farm itself
    echo "🗑️  Deleting the farm record...\n";
    $stats['farms'] = DB::table('farms')->where('id', $farmUuid)->delete();

    DB::commit();
    echo "\n🔓 Transaction committed successfully!\n";

    // --- Success Summary ---
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 DELETION COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($stats as $table => $count) {
        if ($count > 0) {
            echo "✅ Deleted {$count} record(s) from '{$table}'.\n";
        }
    }
    echo str_repeat("=", 70) . "\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n" . str_repeat("!", 70) . "\n";
    echo "💥 DELETION FAILED - ROLLING BACK ALL CHANGES!\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "✅ All database changes have been rolled back.\n";
    echo str_repeat("!", 70) . "\n";
    exit(1);
}

echo "🎉 Deletion script finished.\n";

