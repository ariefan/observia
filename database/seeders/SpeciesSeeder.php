<?php

namespace Database\Seeders;

use App\Models\Species;
use Illuminate\Database\Seeder;

class SpeciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Species::truncate();
        Species::create(['name' => 'Domba', 'binomial_nomenclature' => 'Ovis aries']);
        Species::create(['name' => 'Kambing', 'binomial_nomenclature' => 'Capra hircus']);
        Species::create(['name' => 'Kelinci', 'binomial_nomenclature' => 'Oryctolagus cuniculus']);
        Species::create(['name' => 'Kerbau', 'binomial_nomenclature' => 'Bubalus bubalis']);
        Species::create(['name' => 'Kuda', 'binomial_nomenclature' => 'Equus ferus']);
        Species::create(['name' => 'Sapi', 'binomial_nomenclature' => 'Bos taurus']);
        Species::create(['name' => 'Tikus', 'binomial_nomenclature' => 'Mus musculus']);
        Species::create(['name' => 'Lainnya', 'binomial_nomenclature' => null]);
    }
}
