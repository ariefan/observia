<?php

namespace Database\Seeders;

use App\Models\Breed;
use App\Models\Species;
use Illuminate\Database\Seeder;

class BreedSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Breed::truncate();
        $domba = Species::where('name', 'Domba')->first();
        $kambing = Species::where('name', 'Kambing')->first();

        $breeds = [
            ['species_id' => $domba->id, 'code' => 'DLK', 'name' => 'Lokal'],
            ['species_id' => $domba->id, 'code' => 'DGR', 'name' => 'Garut'],
            ['species_id' => $domba->id, 'code' => 'DMI', 'name' => 'Merino'],
            ['species_id' => $domba->id, 'code' => 'DMG', 'name' => 'Mega'],
            ['species_id' => $domba->id, 'code' => 'DTX', 'name' => 'Texel'],
            ['species_id' => $domba->id, 'code' => 'DDP', 'name' => 'Dolper'],
            ['species_id' => $domba->id, 'code' => 'DEG', 'name' => 'Ekor Gemuk'],
            ['species_id' => $domba->id, 'code' => 'DPT', 'name' => 'Batur'],
            ['species_id' => $domba->id, 'code' => 'DAW', 'name' => 'Awassi'],
            ['species_id' => $kambing->id, 'code' => 'KSN', 'name' => 'Sanen'],
            ['species_id' => $kambing->id, 'code' => 'KSE', 'name' => 'Sapera'],
            ['species_id' => $kambing->id, 'code' => 'KEW', 'name' => 'Etawa'],
            ['species_id' => $kambing->id, 'code' => 'KBE', 'name' => 'Boer'],
            ['species_id' => $kambing->id, 'code' => 'KAI', 'name' => 'Alpine'],
            ['species_id' => $kambing->id, 'code' => 'KJR', 'name' => 'Jawa Randu'],
            ['species_id' => $kambing->id, 'code' => 'KKA', 'name' => 'Kacang'],
            ['species_id' => $kambing->id, 'code' => 'KAN', 'name' => 'Anglo Nubian'],
            ['species_id' => $kambing->id, 'code' => 'KAO', 'name' => 'Anglopera'],
            ['species_id' => $kambing->id, 'code' => 'KNR', 'name' => 'Nigerian'],
            ['species_id' => $kambing->id, 'code' => 'KPG', 'name' => 'Pygmy'],
            ['species_id' => $kambing->id, 'code' => 'KTN', 'name' => 'Toggenburg'],
            ['species_id' => $kambing->id, 'code' => 'KSD', 'name' => 'Senduro'],
            ['species_id' => $kambing->id, 'code' => 'PEW', 'name' => 'Peranakan Etawa'],
            ['species_id' => $kambing->id, 'code' => 'SPF1', 'name' => 'Sapera F1'],
            ['species_id' => $kambing->id, 'code' => 'SPF2', 'name' => 'Sapera F2'],
            ['species_id' => $kambing->id, 'code' => 'SPF3', 'name' => 'Sapera F3'],
            ['species_id' => $kambing->id, 'code' => 'SPF4', 'name' => 'Sapera F4'],
            ['species_id' => $kambing->id, 'code' => 'SHM1', 'name' => 'Shami'],
        ];

        foreach ($breeds as $breed) {
            Breed::create($breed);
        }
    }
}
