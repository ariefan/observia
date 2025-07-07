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
        Species::create(['name' => 'Domba', 'code' => 'OVIS', 'binomial_nomenclature' => 'Ovis aries']);
        Species::create(['name' => 'Kambing', 'code' => 'CAPRA', 'binomial_nomenclature' => 'Capra hircus']);
        // Species::create(['name' => 'Kelinci', 'code' => '', 'binomial_nomenclature' => 'Oryctolagus cuniculus']);
        // Species::create(['name' => 'Kerbau', 'code' => '', 'binomial_nomenclature' => 'Bubalus bubalis']);
        // Species::create(['name' => 'Kuda', 'code' => '', 'binomial_nomenclature' => 'Equus ferus']);
        // Species::create(['name' => 'Sapi', 'code' => '', 'binomial_nomenclature' => 'Bos taurus']);
        // Species::create(['name' => 'Tikus', 'code' => '', 'binomial_nomenclature' => 'Mus musculus']);
        // Species::create(['name' => 'Lainnya', 'code' => '', 'binomial_nomenclature' => null]);
    }
}
