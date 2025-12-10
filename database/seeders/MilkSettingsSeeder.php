<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MilkSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'milk_quality_standards',
                'label' => 'Standar Kualitas Susu',
                'value' => json_encode([
                    'grade_a' => [
                        'pH_min' => 6.6,
                        'pH_max' => 6.8,
                        'fat_min' => 3.5,
                        'bacteria_max' => 100000,
                    ],
                    'grade_b' => [
                        'pH_min' => 6.5,
                        'pH_max' => 6.9,
                        'fat_min' => 3.0,
                        'bacteria_max' => 500000,
                    ],
                    'grade_c' => [
                        'pH_min' => 6.4,
                        'pH_max' => 7.0,
                        'fat_min' => 2.5,
                        'bacteria_max' => 1000000,
                    ],
                ]),
                'type' => 'json',
                'description' => 'Standar kualitas untuk grading susu (pH, fat, bacteria count)',
                'category' => 'milk_supply_chain',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'key' => 'milk_temperature_range',
                'label' => 'Rentang Suhu Susu',
                'value' => json_encode([
                    'pickup_min' => 4,
                    'pickup_max' => 7,
                    'warning_threshold' => 10,
                ]),
                'type' => 'json',
                'description' => 'Rentang suhu ideal untuk transportasi susu (°C)',
                'category' => 'milk_supply_chain',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'key' => 'milk_expiry_hours',
                'label' => 'Waktu Kadaluarsa Susu (Jam)',
                'value' => '24',
                'type' => 'number',
                'description' => 'Batas waktu maksimal susu harus diproses (dalam jam)',
                'category' => 'milk_supply_chain',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'key' => 'payment_schedule',
                'label' => 'Jadwal Pembayaran',
                'value' => 'monthly',
                'type' => 'select',
                'options' => json_encode(['options' => ['weekly' => 'Mingguan', 'biweekly' => 'Dua Mingguan', 'monthly' => 'Bulanan']]),
                'description' => 'Jadwal pembayaran ke peternak',
                'category' => 'milk_supply_chain',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
