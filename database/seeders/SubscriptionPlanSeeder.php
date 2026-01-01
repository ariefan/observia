<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Paket gratis untuk memulai manajemen peternakan digital',
                'monthly_price' => 0,
                'annual_price' => 0,
                'max_livestock' => 50,
                'max_users' => 3,
                'has_analytics' => false,
                'has_iot' => false,
                'has_expert_support' => false,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 1,
                'features' => [
                    ['name' => 'Manajemen Ternak', 'description' => 'Pencatatan data ternak dasar', 'included' => true],
                    ['name' => 'Pencatatan Susu', 'description' => 'Catat produksi susu harian', 'included' => true],
                    ['name' => 'Manajemen Kandang', 'description' => 'Kelola kandang dan pengelompokan', 'included' => true],
                    ['name' => 'Visualisasi Produktivitas', 'description' => 'Grafik dan analisis produktivitas', 'included' => false],
                    ['name' => 'Manajemen Inventaris', 'description' => 'Kelola stok pakan dan obat', 'included' => true],
                    ['name' => 'Dukungan Ahli', 'description' => 'Konsultasi dengan pakar peternakan', 'included' => false],
                    ['name' => 'Integrasi IoT', 'description' => 'Sensor dan monitoring otomatis', 'included' => false],
                ],
            ],
            [
                'name' => 'Pro',
                'slug' => 'pro',
                'description' => 'Paket lengkap untuk peternakan profesional',
                'monthly_price' => 62500, // Rp 62,500/month (annual: 600,000/12 = 50,000/month with 20% saving)
                'annual_price' => 600000, // Rp 600,000/year (20% discount from monthly)
                'max_livestock' => 500,
                'max_users' => 10,
                'has_analytics' => true,
                'has_iot' => false,
                'has_expert_support' => true,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 2,
                'features' => [
                    ['name' => 'Manajemen Ternak', 'description' => 'Pencatatan data ternak lengkap', 'included' => true],
                    ['name' => 'Pencatatan Susu', 'description' => 'Catat produksi susu dengan grading', 'included' => true],
                    ['name' => 'Manajemen Kandang', 'description' => 'Kelola kandang dan pengelompokan', 'included' => true],
                    ['name' => 'Visualisasi Produktivitas', 'description' => 'Grafik dan analisis produktivitas', 'included' => true],
                    ['name' => 'Manajemen Inventaris', 'description' => 'Kelola stok pakan dan obat', 'included' => true],
                    ['name' => 'Dukungan Ahli', 'description' => 'Konsultasi dengan pakar peternakan', 'included' => true],
                    ['name' => 'Integrasi IoT', 'description' => 'Sensor dan monitoring otomatis', 'included' => false],
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Solusi lengkap untuk peternakan skala besar',
                'monthly_price' => 104167, // Rp 104,167/month (annual: 1,000,000/12 = 83,333/month with 20% saving)
                'annual_price' => 1000000, // Rp 1,000,000/year
                'max_livestock' => null, // Unlimited
                'max_users' => null, // Unlimited
                'has_analytics' => true,
                'has_iot' => true,
                'has_expert_support' => true,
                'is_active' => true,
                'is_visible' => true,
                'sort_order' => 3,
                'features' => [
                    ['name' => 'Manajemen Ternak', 'description' => 'Pencatatan data ternak premium', 'included' => true],
                    ['name' => 'Pencatatan Susu', 'description' => 'Catat produksi susu dengan grading', 'included' => true],
                    ['name' => 'Manajemen Kandang', 'description' => 'Kelola kandang dan pengelompokan', 'included' => true],
                    ['name' => 'Visualisasi Produktivitas', 'description' => 'Grafik dan analisis produktivitas lanjutan', 'included' => true],
                    ['name' => 'Manajemen Inventaris', 'description' => 'Kelola stok pakan dan obat', 'included' => true],
                    ['name' => 'Dukungan Ahli', 'description' => 'Konsultasi prioritas dengan pakar', 'included' => true],
                    ['name' => 'Integrasi IoT', 'description' => 'Sensor dan monitoring otomatis', 'included' => true],
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}
