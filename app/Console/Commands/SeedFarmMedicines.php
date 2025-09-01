<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Farm;

class SeedFarmMedicines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'farm:seed-medicines {--farm-id= : Seed medicines for a specific farm ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default medicine inventory items for farms';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $farmId = $this->option('farm-id');
        
        if ($farmId) {
            // Seed medicines for specific farm
            $farm = Farm::find($farmId);
            if (!$farm) {
                $this->error("Farm with ID {$farmId} not found.");
                return 1;
            }
            
            $this->info("Seeding medicines for farm: {$farm->name}");
            $farm->createDefaultMedicines();
            $this->info("✅ Medicines seeded successfully for {$farm->name}");
            
        } else {
            // Seed medicines for all farms
            $farms = Farm::all();
            $this->info("Seeding medicines for {$farms->count()} farms...");
            
            $bar = $this->output->createProgressBar($farms->count());
            $bar->start();
            
            foreach ($farms as $farm) {
                $farm->createDefaultMedicines();
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine();
            $this->info("✅ Medicines seeded successfully for all farms");
        }
        
        return 0;
    }
}