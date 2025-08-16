<?php
/**
 * SAFE Deletion Script for Farm Data with Automatic Rollback
 *
 * This script deletes a farm and all its associated data, including users who
 * belong exclusively to this farm. It is wrapped in a transaction, so if any
 * part of the deletion fails, all changes will be rolled back.
 *
 * Usage: php scripts/delete_farm_data.php <farm_uuid>
 */

// Bootstrap Laravel application
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Farm;
use Illuminate\Support\Facades\Storage;

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
echo "  - Herds and Livestocks\n";
echo "  - Rations and Feeding History\n";
echo "  - Team Members and Invitations\n";
echo "  - User accounts that belong ONLY to this farm\n\n";

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
    'livestock_weights' => 0, 'livestock_milkings' => 0, 'feeding_leftovers' => 0,
    'herd_feedings' => 0, 'ration_items' => 0, 'rations' => 0, 'livestocks' => 0,
    'herds' => 0, 'farm_users' => 0, 'farm_invites' => 0, 'users' => 0, 'farms' => 0,
];

try {
    DB::beginTransaction();
    echo "\n🔒 Database transaction started.\n";

    // --- Step 1: Identify and Delete Exclusive Users ---
    echo "👥 Identifying users to delete...\n";
    $farmUserIds = DB::table('farm_user')->where('farm_id', $farmUuid)->pluck('user_id');
    
    $usersToDelete = [];
    foreach ($farmUserIds as $userId) {
        // Check if the user belongs to any other farm
        $otherFarmsCount = DB::table('farm_user')->where('user_id', $userId)->where('farm_id', '!=', $farmUuid)->count();
        if ($otherFarmsCount === 0) {
            $usersToDelete[] = $userId;
        }
    }

    // --- Step 2: Delete Farm-Specific Data ---
    echo "🗑️  Deleting related records...\n";
    $herdIds = DB::table('herds')->where('farm_id', $farmUuid)->pluck('id');
    $livestockIds = DB::table('livestocks')->where('farm_id', $farmUuid)->pluck('id');
    $rationIds = DB::table('rations')->where('farm_id', $farmUuid)->pluck('id');
    $herdFeedingIds = DB::table('herd_feedings')->whereIn('herd_id', $herdIds)->pluck('id');

    // 🐮 DELETE photos before nuking livestocks
    $photos = DB::table('livestocks')->where('farm_id', $farmUuid)->pluck('photo');
    $totalPhotos = 0;
    $deletedPhotos = 0;

    foreach ($photos as $photoJson) {
        if ($photoJson) {
            $paths = json_decode($photoJson, true);
            if (is_array($paths)) {
                $totalPhotos += count($paths);
            }
        }
    }

    echo "📸 Found {$totalPhotos} livestock photo(s) scheduled for deletion.\n";

    foreach ($photos as $photoJson) {
        if ($photoJson) {
            $paths = json_decode($photoJson, true);
            if (is_array($paths)) {
                foreach ($paths as $path) {
                    $fullPath = public_path('storage/' . ltrim($path, '/'));
                    if (file_exists($fullPath)) {
                        if (@unlink($fullPath)) {
                            echo "✅ Deleted: {$fullPath}\n";
                            $deletedPhotos++;
                        } else {
                            echo "❌ Failed to delete: {$fullPath}\n";
                        }
                    } else {
                        echo "⚠️ File not found: {$fullPath}\n";
                    }
                }
            }
        }
    }

    echo "🖼️ Deleted {$deletedPhotos}/{$totalPhotos} livestock photo(s).\n";


    $stats['livestock_weights'] = DB::table('livestock_weights')->whereIn('livestock_id', $livestockIds)->delete();
    $stats['livestock_milkings'] = DB::table('livestock_milkings')->whereIn('livestock_id', $livestockIds)->delete();
    $stats['feeding_leftovers'] = DB::table('feeding_leftovers')->whereIn('feeding_id', $herdFeedingIds)->delete();
    $stats['herd_feedings'] = DB::table('herd_feedings')->whereIn('herd_id', $herdIds)->delete();
    $stats['ration_items'] = DB::table('ration_items')->whereIn('ration_id', $rationIds)->delete();
    $stats['rations'] = DB::table('rations')->where('farm_id', $farmUuid)->delete();
    $stats['livestocks'] = DB::table('livestocks')->where('farm_id', $farmUuid)->delete();
    $stats['herds'] = DB::table('herds')->where('farm_id', $farmUuid)->delete();
    $stats['farm_invites'] = DB::table('farm_invites')->where('farm_id', $farmUuid)->delete();
    
    // --- Step 3: Delete Farm-User Associations and the Users Themselves ---
    echo "👥 Removing farm-user associations...\n";
    $stats['farm_users'] = DB::table('farm_user')->where('farm_id', $farmUuid)->delete();
    echo "✅ Deleted {$stats['farm_users']} farm-user association(s).\n";
    
    if (!empty($usersToDelete)) {
        echo "👤 Deleting user accounts exclusive to this farm...\n";
        $stats['users'] = DB::table('users')->whereIn('id', $usersToDelete)->delete();
        echo "✅ Deleted {$stats['users']} user account(s) exclusive to this farm.\n";
    } else {
        echo "✅ No exclusive users found to delete.\n";
    }

    // --- Step 4: Delete the Farm ---
    echo "🗑️  Deleting the farm record...\n";
    $stats['farms'] = DB::table('farms')->where('id', $farmUuid)->delete();

    DB::commit();
    echo "\n🔓 Transaction committed successfully!\n";

    // --- Success Summary ---
    printSuccessSummary($stats);

} catch (Exception $e) {
    DB::rollBack();
    printFailureSummary($e);
    exit(1);
}

echo "🎉 Deletion script finished.\n";

function printSuccessSummary(array $stats): void
{
    echo "\n" . str_repeat("=", 70) . "\n";
    echo "🎉 DELETION COMPLETED SUCCESSFULLY!\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($stats as $table => $count) {
        if ($count > 0) {
            $tableName = str_replace('_', ' ', $table);
            echo "✅ Deleted {$count} record(s) from '{$tableName}'.\n";
        }
    }
    echo str_repeat("=", 70) . "\n";
}

function printFailureSummary(Exception $e): void
{
    echo "\n" . str_repeat("!", 70) . "\n";
    echo "💥 DELETION FAILED - ROLLING BACK ALL CHANGES!\n";
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
    echo "✅ All database changes have been rolled back.\n";
    echo str_repeat("!", 70) . "\n";
}

