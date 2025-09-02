<?php
require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Audit;
use App\Models\Livestock;
use App\Models\Farm;

echo "=== ATTEMPTING DATA RECOVERY FROM AUDIT LOGS ===\n\n";

// Get all creation events for livestock (this should be your original data)
$livestockCreations = Audit::where('auditable_type', 'App\Models\Livestock')
    ->where('event', 'created')
    ->whereDate('created_at', '<', today()) // Created before today
    ->get();

echo "Found {$livestockCreations->count()} livestock creation records from before today\n";

if ($livestockCreations->count() > 0) {
    echo "\nSample original livestock data:\n";
    foreach ($livestockCreations->take(3) as $audit) {
        $values = is_string($audit->new_values) ? json_decode($audit->new_values, true) : $audit->new_values;
        echo "ID: {$audit->auditable_id}\n";
        echo "Created: {$audit->created_at}\n";
        if ($values && isset($values['name'])) {
            echo "Name: {$values['name']}\n";
            echo "Tag: " . ($values['tag_id'] ?? 'N/A') . "\n";
        }
        echo "---\n";
    }
}

// Check farm creations too
$farmCreations = Audit::where('auditable_type', 'App\Models\Farm')
    ->where('event', 'created')
    ->whereDate('created_at', '<', today())
    ->get();

echo "\nFound {$farmCreations->count()} farm creation records from before today\n";

echo "\n=== RECOVERY PLAN ===\n";
echo "1. We can restore {$livestockCreations->count()} livestock records\n";
echo "2. We can restore {$farmCreations->count()} farm records\n";
echo "3. Need to clear current seeded data first\n";
echo "4. Then restore from audit 'new_values'\n";
