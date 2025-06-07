<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProvinceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $provinces = [
            ["id" => 1,"code" => "07","name" => "ACEH"],
            ["id" => 2,"code" => "08","name" => "SUMATERA UTARA"],
            ["id" => 3,"code" => "09","name" => "SUMATERA BARAT"],
            ["id" => 4,"code" => "10","name" => "RIAU"],
            ["id" => 5,"code" => "12","name" => "JAMBI"],
            ["id" => 6,"code" => "14","name" => "SUMATERA SELATAN"],
            ["id" => 7,"code" => "13","name" => "BENGKULU"],
            ["id" => 8,"code" => "16","name" => "LAMPUNG"],
            ["id" => 9,"code" => "15","name" => "KEPULAUAN BANGKA BELITUNG"],
            ["id" => 10,"code" => "11","name" => "KEPULAUAN RIAU"],
            ["id" => 11,"code" => "06","name" => "DKI JAKARTA"],
            ["id" => 12,"code" => "04","name" => "JAWA BARAT"],
            ["id" => 13,"code" => "02","name" => "JAWA TENGAH"],
            ["id" => 14,"code" => "01","name" => "DI YOGYAKARTA"],
            ["id" => 15,"code" => "03","name" => "JAWA TIMUR"],
            ["id" => 16,"code" => "05","name" => "BANTEN"],
            ["id" => 17,"code" => "17","name" => "BALI"],
            ["id" => 18,"code" => "18","name" => "NUSA TENGGARA BARAT"],
            ["id" => 19,"code" => "19","name" => "NUSA TENGGARA TIMUR"],
            ["id" => 20,"code" => "20","name" => "KALIMANTAN BARAT"],
            ["id" => 21,"code" => "21","name" => "KALIMANTAN TENGAH"],
            ["id" => 22,"code" => "22","name" => "KALIMANTAN SELATAN"],
            ["id" => 23,"code" => "23","name" => "KALIMANTAN TIMUR"],
            ["id" => 24,"code" => "24","name" => "KALIMANTAN UTARA"],
            ["id" => 25,"code" => "30","name" => "SULAWESI UTARA"],
            ["id" => 26,"code" => "27","name" => "SULAWESI TENGAH"],
            ["id" => 27,"code" => "25","name" => "SULAWESI SELATAN"],
            ["id" => 28,"code" => "28","name" => "SULAWESI TENGGARA"],
            ["id" => 29,"code" => "29","name" => "GORONTALO"],
            ["id" => 30,"code" => "26","name" => "SULAWESI BARAT"],
            ["id" => 31,"code" => "32","name" => "MALUKU"],
            ["id" => 32,"code" => "31","name" => "MALUKU UTARA"],
            ["id" => 33,"code" => "34","name" => "PAPUA"],
            ["id" => 34,"code" => "33","name" => "PAPUA BARAT"],
        ];

        \DB::table('provinces')->truncate();
        foreach ($provinces as $province) {
            \DB::table('provinces')->insert([
                'id' => $province['id'],
                'code' => $province['code'],
                'name' => $province['name'],
            ]);
        }
    }
}
