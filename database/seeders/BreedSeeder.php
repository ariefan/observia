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
        // Get species - use updateOrCreate to avoid duplicating data
        $domba = Species::where('name', 'Domba')->first();
        $kambing = Species::where('name', 'Kambing')->first();
        $sapi = Species::where('name', 'Sapi')->first();
        $kerbau = Species::where('name', 'Kerbau')->first();

        $breeds = [
            // Domba (Sheep) Breeds
            ['species_id' => $domba->id, 'code' => 'DLK', 'name' => 'Lokal'],
            ['species_id' => $domba->id, 'code' => 'DGR', 'name' => 'Garut'],
            ['species_id' => $domba->id, 'code' => 'DMI', 'name' => 'Merino'],
            ['species_id' => $domba->id, 'code' => 'DMG', 'name' => 'Mega'],
            ['species_id' => $domba->id, 'code' => 'DTX', 'name' => 'Texel'],
            ['species_id' => $domba->id, 'code' => 'DDP', 'name' => 'Dolper'],
            ['species_id' => $domba->id, 'code' => 'DEG', 'name' => 'Ekor Gemuk'],
            ['species_id' => $domba->id, 'code' => 'DPT', 'name' => 'Batur'],
            ['species_id' => $domba->id, 'code' => 'DAW', 'name' => 'Awassi'],

            // Kambing (Goat) Breeds
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

            // Sapi (Cattle/Cow) Breeds - Dairy & Dual Purpose
            ['species_id' => $sapi->id, 'code' => 'SFH', 'name' => 'Friesian Holstein (FH)'],
            ['species_id' => $sapi->id, 'code' => 'SJER', 'name' => 'Jersey'],
            ['species_id' => $sapi->id, 'code' => 'SSIM', 'name' => 'Simmental'],
            ['species_id' => $sapi->id, 'code' => 'SLIM', 'name' => 'Limousin'],
            ['species_id' => $sapi->id, 'code' => 'SPO', 'name' => 'Peranakan Ongole (PO)'],
            ['species_id' => $sapi->id, 'code' => 'SBAL', 'name' => 'Bali'],
            ['species_id' => $sapi->id, 'code' => 'SMAD', 'name' => 'Madura'],
            ['species_id' => $sapi->id, 'code' => 'SACO', 'name' => 'Aceh'],
            ['species_id' => $sapi->id, 'code' => 'SANG', 'name' => 'Angus'],
            ['species_id' => $sapi->id, 'code' => 'SBRA', 'name' => 'Brahman'],
            ['species_id' => $sapi->id, 'code' => 'SHER', 'name' => 'Hereford'],
            ['species_id' => $sapi->id, 'code' => 'SBRG', 'name' => 'Brangus'],
            ['species_id' => $sapi->id, 'code' => 'SAYS', 'name' => 'Ayrshire'],
            ['species_id' => $sapi->id, 'code' => 'SBRW', 'name' => 'Brown Swiss'],
            ['species_id' => $sapi->id, 'code' => 'SGUE', 'name' => 'Guernsey'],
            ['species_id' => $sapi->id, 'code' => 'SSAL', 'name' => 'Sahiwal'],

            // Kerbau (Water Buffalo) Breeds - Dairy
            ['species_id' => $kerbau->id, 'code' => 'KMUR', 'name' => 'Murrah'],
            ['species_id' => $kerbau->id, 'code' => 'KJAF', 'name' => 'Jafarabadi'],
            ['species_id' => $kerbau->id, 'code' => 'KSWA', 'name' => 'Swamp Buffalo (Lokal)'],
            ['species_id' => $kerbau->id, 'code' => 'KNILI', 'name' => 'Nili-Ravi'],
            ['species_id' => $kerbau->id, 'code' => 'KTOR', 'name' => 'Toraja'],
        ];

        foreach ($breeds as $breed) {
            Breed::updateOrCreate(
                ['code' => $breed['code']],
                $breed
            );
        }
    }
}
