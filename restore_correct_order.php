<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Audit;
use App\Models\Livestock;
use App\Models\Farm;

echo "=== RESTORING IN CORRECT ORDER ===\n\n";

// Step 1: Restore farms FIRST (since livestock references farms)
echo "Step 1: Restoring original farms...\n";
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
        
        try {
            $farm->save();
            $restoredFarms++;
            echo "✅ Restored farm: {$values['name']}\n";
        } catch (Exception $e) {
            echo "❌ Error restoring farm: " . $e->getMessage() . "\n";
        }
    }
}

// Step 2: Now restore livestock
echo "\nStep 2: Restoring original livestock...\n";
$livestockAudits = Audit::where('auditable_type', 'App\Models\Livestock')
    ->where('event', 'created')
    ->whereDate('created_at', '<', today())
    ->get();

$restoredLivestock = 0;
foreach ($livestockAudits as $audit) {
    $values = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
    
    if ($values) {
        $livestock = new Livestock();
        $livestock->id = $audit->auditable_id;
        
        foreach ($values as $key => $value) {
            if ($key !== 'id') {
                $livestock->{$key} = $value;
            }
        }
        
        $livestock->created_at = $audit->created_at;
        $livestock->updated_at = $audit->created_at;
        
        try {
            $livestock->save();
            $restoredLivestock++;
            echo "✅ Restored livestock: {$values['name']} (Tag: {$values['tag_id']})\n";
        } catch (Exception $e) {
            echo "❌ Error restoring livestock {$values['name']}: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== RESTORATION SUMMARY ===\n";
echo "✅ Restored $restoredFarms farm records\n";
echo "✅ Restored $restoredLivestock livestock records\n";

if ($restoredFarms > 0 || $restoredLivestock > 0) {
    echo "\n🎉 SUCCESS: Your business data has been recovered!\n";
} else {
    echo "\n⚠️  No data was restored - please check for errors above\n";
}
