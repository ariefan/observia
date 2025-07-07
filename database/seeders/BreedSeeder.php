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
        $species = Species::where('name', 'Kambing')->first();
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Etawa',
            'origin' => 'India',
            'description' => 'Kambing berbadan besar dan tinggi, bulu putih atau krem, produktivitas susu tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Jamnapari',
            'origin' => 'India',
            'description' => 'Kambing berbadan besar dan panjang, bulu hitam atau putih, produktivitas susu tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Kacang',
            'origin' => 'Indonesia',
            'description' => 'Kambing berbadan kecil dan ramping, bulu hitam, putih, atau coklat, produktivitas daging tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Pekan',
            'origin' => 'Indonesia',
            'description' => 'Kambing berbadan besar dan panjang, bulu putih atau hitam, produktivitas daging tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Sumbawa',
            'origin' => 'Indonesia',
            'description' => 'Kambing berbadan besar dan panjang, bulu putih atau hitam, produktivitas daging tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Boer',
            'origin' => 'Afrika Selatan',
            'description' => 'Kambing berbadan besar dan gemuk, bulu putih atau hitam, produktivitas daging sangat tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Pygmy',
            'origin' => 'Afrika Barat',
            'description' => 'Kambing terkecil di dunia, bulu beragam, jinak dan cocok untuk dipelihara.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Saanen',
            'origin' => 'Swiss',
            'description' => 'Kambing berbadan besar dan tinggi, bulu putih, produktivitas susu sangat tinggi.',
        ]);
        Breed::create([
            'species_id' => $species->id,
            'name' => 'Alpine',
            'origin' => 'Prancis, Swiss',
            'description' => 'Kambing berbadan sedang dan ramping, bulu berwarna putih, produktivitas susu tinggi, tahan terhadap cuaca dingin.',
        ]);
    }
}
