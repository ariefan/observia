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
        // Use updateOrCreate to avoid duplicating existing data
        Species::updateOrCreate(
            ['code' => 'OVIS'],
            ['name' => 'Domba', 'binomial_nomenclature' => 'Ovis aries']
        );
        Species::updateOrCreate(
            ['code' => 'CAPRA'],
            ['name' => 'Kambing', 'binomial_nomenclature' => 'Capra hircus']
        );
        Species::updateOrCreate(
            ['code' => 'BOS'],
            ['name' => 'Sapi', 'binomial_nomenclature' => 'Bos taurus']
        );
        Species::updateOrCreate(
            ['code' => 'BUBALUS'],
            ['name' => 'Kerbau', 'binomial_nomenclature' => 'Bubalus bubalis']
        );
        // Species::updateOrCreate(['code' => 'ORYCTO'], ['name' => 'Kelinci', 'binomial_nomenclature' => 'Oryctolagus cuniculus']);
        // Species::updateOrCreate(['code' => 'EQUUS'], ['name' => 'Kuda', 'binomial_nomenclature' => 'Equus ferus']);
        // Species::updateOrCreate(['code' => 'MUS'], ['name' => 'Tikus', 'binomial_nomenclature' => 'Mus musculus']);
        // Species::updateOrCreate(['code' => 'OTHER'], ['name' => 'Lainnya', 'binomial_nomenclature' => null]);
    }
}
