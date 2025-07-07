<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Farm;
use App\Models\Breed;
use App\Models\Livestock;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

            $livestocks = Livestock::factory()->count(15)->create([
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
        }
    }
}
