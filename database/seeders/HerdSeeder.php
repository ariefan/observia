<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HerdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // Create 10 herds for each farm
        $farms = \App\Models\Farm::all();
        foreach ($farms as $farm) {
            \App\Models\Herd::factory()
                ->count(10)
                ->create(['farm_id' => $farm->id]);
        }
    }
}
