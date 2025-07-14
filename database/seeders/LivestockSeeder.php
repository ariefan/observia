<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Farm;
use App\Models\Breed;
use App\Models\Livestock;
use App\Models\LivestockWeight;
use App\Models\LivestockMilking;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class LivestockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // \DB::table('users')->truncate();
        \DB::table('farms')->truncate();
        \DB::table('livestocks')->truncate();
        \DB::table('livestock_weights')->truncate();
        \DB::table('livestock_milking')->truncate();

        $user = User::FirstOrCreate(
            [ 'email' => 'test@aifarm.id' ],
            [
                'name' => 'Admin Test',
                'password' => Hash::make('password123'),
            ]
        );

        $breeds = Breed::all();

        for ($i = 0; $i < 3; $i++) {
            $farm = Farm::factory()->create([
                'user_id' => $user->id,
                'name' => 'Aifarm ' . ($i + 1),
            ]);

            $user->farms()->attach($farm, ['role' => 'admin']);

            $livestocks = Livestock::factory()->count(8)->create([
                'farm_id' => $farm->id,
                'breed_id' => $breeds->random()->id,
            ]);

            foreach ($livestocks as $livestock) {
                $livestock->update(['aifarm_id' => generateAifarmId()]);
            }

            // Add some parentage
            foreach ($livestocks as $livestock) {
                if (rand(0, 1)) {
                    $maleParent = $livestocks->where('sex', 'M')->where('id', '!=', $livestock->id)->random();
                    if ($maleParent) {
                        $livestock->update(['male_parent_id' => $maleParent->id]);
                    }
                }
                if (rand(0, 1)) {
                    $femaleParent = $livestocks->where('sex', 'F')->where('id', '!=', $livestock->id)->random();
                    if ($femaleParent) {
                        $livestock->update(['female_parent_id' => $femaleParent->id]);
                    }
                }
            }

            // Add weight and milking data for the last 3 months
            foreach ($livestocks as $livestock) {
                $this->createWeightData($livestock, $user->id);
                
                // Only create milking data for female livestock
                if ($livestock->sex === 'F') {
                    $this->createMilkingData($livestock, $user->id);
                }
            }
        }
    }

    /**
     * Create weight data for livestock for the last 3 months
     */
    private function createWeightData(Livestock $livestock, $userId): void
    {
        if (!$userId) {
            throw new \Exception("userId is NULL for livestock {$livestock->id}");
        }
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        // Base weight with some variation
        $baseWeight = rand(35, 100); // kg, varies by livestock
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Create 2 weight records per day
            for ($i = 0; $i < 2; $i++) {
                // Add some weight progression over time and daily variation
                $daysFromStart = $startDate->diffInDays($currentDate);
                $weightProgression = $daysFromStart * rand(1, 3) / 10; // Slow weight gain
                $dailyVariation = rand(-5, 5); // Daily weight variation
                
                $weight = $baseWeight + $weightProgression + $dailyVariation;
                
                LivestockWeight::create([
                    'livestock_id' => $livestock->id,
                    'weight' => round($weight, 2),
                    'date' => $currentDate->toDateString(),
                    'user_id' => $userId,
                    'device_id' => 'SCALE_' . rand(1000, 9999),
                ]);
            }
            
            $currentDate->addDay();
        }
    }

    /**
     * Create milking data for female livestock for the last 3 months
     */
    private function createMilkingData(Livestock $livestock, $userId): void
    {
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        // Base milk volume with some variation
        $baseMilkVolume = rand(1, 4); // liters per session
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            // Create 2 milking sessions per day (morning and evening)
            $sessions = [
                ['time' => '08:00:00', 'session' => 'morning'],
                ['time' => '16:00:00', 'session' => 'afternoon']
            ];
            
            foreach ($sessions as $session) {
                // Add some seasonal variation and daily fluctuation
                $daysFromStart = $startDate->diffInDays($currentDate);
                $seasonalVariation = sin($daysFromStart / 30) * 2; // Seasonal milk production
                $dailyVariation = rand(-2, 3); // Daily milk variation
                
                $milkVolume = $baseMilkVolume + $seasonalVariation + $dailyVariation;
                $milkVolume = max(1, $milkVolume); // Ensure minimum 1 liter
                
                LivestockMilking::create([
                    'livestock_id' => $livestock->id,
                    'milk_volume' => round($milkVolume, 2),
                    'date' => $currentDate->toDateString(),
                    'time' => $session['time'],
                    'session' => $session['session'],
                    'user_id' => $userId,
                    'device_id' => 'MILKER_' . rand(1000, 9999),
                    'notes' => rand(0, 10) === 0 ? 'Good quality milk' : null, // Random notes 10% of the time
                ]);
            }
            
            $currentDate->addDay();
        }
    }
}
