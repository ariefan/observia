<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Audit;
use App\Models\Livestock;
use App\Models\Farm;

echo "=== RESTORING YOUR BUSINESS DATA ===\n\n";

// Step 1: Clear seeded data (created today)
echo "Step 1: Clearing seeded test data...\n";
$deletedLivestock = Livestock::whereDate('created_at', today())->delete();
$deletedFarms = Farm::whereDate('created_at', today())->delete();

echo "Cleared $deletedLivestock test livestock and $deletedFarms test farms\n\n";

// Step 2: Restore original livestock from audit
echo "Step 2: Restoring original livestock...\n";
$livestockAudits = Audit::where('auditable_type', 'App\Models\Livestock')
    ->where('event', 'created')
    ->whereDate('created_at', '<', today())
    ->get();

$restoredLivestock = 0;
foreach ($livestockAudits as $audit) {
    $values = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
    
    if ($values) {
        // Restore with original ID and data
        $livestock = new Livestock();
        $livestock->id = $audit->auditable_id;
        
        foreach ($values as $key => $value) {
            if ($key !== 'id') { // Don't overwrite the ID
                $livestock->{$key} = $value;
            }
        }
        
        // Set original timestamps
        $livestock->created_at = $audit->created_at;
        $livestock->updated_at = $audit->created_at;
        
        $livestock->save();
        $restoredLivestock++;
        
        echo "Restored livestock: {$values['name']} (Tag: {$values['tag_id']})\n";
    }
}

// Step 3: Restore original farms
echo "\nStep 3: Restoring original farms...\n";
$farmAudits = Audit::where('auditable_type', 'App\Models\Farm')
    ->where('event', 'created')
    ->whereDate('created_at', '<', today())
    ->get();

$restoredFarms = 0;
foreach ($farmAudits as $audit) {
    $values = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
    
    if ($values) {
        $farm = new Farm();
        $farm->id = $audit->auditable_id;
        
        foreach ($values as $key => $value) {
            if ($key !== 'id') {
                $farm->{$key} = $value;
            }
        }
        
        $farm->created_at = $audit->created_at;
        $farm->updated_at = $audit->created_at;
        
        $farm->save();
        $restoredFarms++;
        
        echo "Restored farm: {$values['name']}\n";
    }
}

echo "\n=== RESTORATION COMPLETE ===\n";
echo "Restored $restoredLivestock livestock records\n";
echo "Restored $restoredFarms farm records\n";
echo "Your business data has been recovered!\n";
