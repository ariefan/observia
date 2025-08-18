<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Farm;
use App\Models\City;
use App\Models\User;

class DummyFarmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = City::inRandomOrder()->limit(10)->pluck('id')->toArray();
        $firstUser = User::first();
        
        if (!$firstUser) {
            $this->command->error('No users found. Please create a user first.');
            return;
        }

        $farms = [
            [
                'name' => 'Peternakan Sumber Rejeki',
                'address' => 'Jl. Raya Bogor No. 123, Bogor, Jawa Barat',
                'owner' => 'Budi Santoso',
                'email' => 'sumberrejeki@gmail.com',
                'phone' => '081234567890',
            ],
            [
                'name' => 'Farm Berkah Jaya',
                'address' => 'Jl. Merdeka No. 45, Bandung, Jawa Barat',
                'owner' => 'Siti Aminah',
                'email' => 'berkahjaya@gmail.com',
                'phone' => '082345678901',
            ],
            [
                'name' => 'Peternakan Maju Mandiri',
                'address' => 'Jl. Sudirman No. 67, Yogyakarta',
                'owner' => 'Ahmad Wijaya',
                'email' => 'majumandiri@gmail.com',
                'phone' => '083456789012',
            ],
            [
                'name' => 'Ternak Sejahtera Abadi',
                'address' => 'Jl. Gajah Mada No. 89, Surabaya, Jawa Timur',
                'owner' => 'Rina Sari',
                'email' => 'sejahteraabadi@gmail.com',
                'phone' => '084567890123',
            ],
            [
                'name' => 'Peternakan Harapan Baru',
                'address' => 'Jl. Diponegoro No. 12, Semarang, Jawa Tengah',
                'owner' => 'Dedy Kurniawan',
                'email' => 'harapanbaru@gmail.com',
                'phone' => '085678901234',
            ],
            [
                'name' => 'Farm Gemilang Sentosa',
                'address' => 'Jl. Ahmad Yani No. 34, Medan, Sumatera Utara',
                'owner' => 'Lisa Permata',
                'email' => 'gemilangsentosa@gmail.com',
                'phone' => '086789012345',
            ],
            [
                'name' => 'Peternakan Barokah Ilahi',
                'address' => 'Jl. Veteran No. 56, Makassar, Sulawesi Selatan',
                'owner' => 'Hendra Pratama',
                'email' => 'barokakilahi@gmail.com',
                'phone' => '087890123456',
            ],
            [
                'name' => 'Ternak Jaya Makmur',
                'address' => 'Jl. Pahlawan No. 78, Denpasar, Bali',
                'owner' => 'Made Sutrisno',
                'email' => 'jayamakmur@gmail.com',
                'phone' => '088901234567',
            ],
            [
                'name' => 'Peternakan Cahaya Terang',
                'address' => 'Jl. Pemuda No. 90, Palembang, Sumatera Selatan',
                'owner' => 'Fitri Handayani',
                'email' => 'cahayaterang@gmail.com',
                'phone' => '089012345678',
            ],
            [
                'name' => 'Farm Indah Permai',
                'address' => 'Jl. Kartini No. 21, Malang, Jawa Timur',
                'owner' => 'Agus Setiawan',
                'email' => 'indahpermai@gmail.com',
                'phone' => '090123456789',
            ],
        ];

        foreach ($farms as $index => $farmData) {
            $farm = Farm::create([
                'name' => $farmData['name'],
                'address' => $farmData['address'],
                'owner' => $farmData['owner'],
                'email' => $farmData['email'],
                'phone' => $farmData['phone'],
                'city_id' => $cities[$index] ?? $cities[0],
                'user_id' => $firstUser->id,
            ]);

            // Attach the first user to this farm
            $farm->users()->attach($firstUser->id, ['role' => 'owner']);
        }

        $this->command->info('10 dummy farms created successfully!');
    }
}
