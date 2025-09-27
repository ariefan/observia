<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TransaksiController extends Controller
{
    public function paketLayanan()
    {
        // Package data structure based on the screenshot
        $packages = [
            [
                'id' => 'starter',
                'name' => 'Starter',
                'price' => 'Free',
                'price_numeric' => 0,
                'period' => '/Tahun/Peternakan',
                'description' => 'Mulai coba',
                'features' => [
                    ['name' => 'Kelola kesehatan dan produksi ternak', 'included' => true],
                    ['name' => 'Lacak data ternak untuk pengambilan keputusan', 'included' => true],
                    ['name' => 'Stud book dan sertifikat per ternak', 'included' => true],
                    ['name' => 'Visualisasi data produktivitas susu dan bobot ternak', 'included' => false],
                    ['name' => 'Rangking produktivitas susu dan bobot ternak', 'included' => false],
                    ['name' => 'Dukungan tim ahli Aifarm', 'included' => false],
                    ['name' => 'Solusi IoT', 'included' => false],
                ],
                'button_text' => 'Coba gratis 7 hari',
                'button_variant' => 'outline',
                'is_current' => false,
            ],
            [
                'id' => 'pro',
                'name' => 'Pro',
                'price' => 'Rp600,000',
                'price_numeric' => 600000,
                'period' => '/Tahun/Peternakan',
                'description' => null,
                'features' => [
                    ['name' => 'Kelola kesehatan dan produksi ternak', 'included' => true],
                    ['name' => 'Lacak data ternak untuk pengambilan keputusan', 'included' => true],
                    ['name' => 'Stud book dan sertifikat per ternak', 'included' => true],
                    ['name' => 'Visualisasi data produktivitas susu dan bobot ternak', 'included' => true],
                    ['name' => 'Rangking produktivitas susu dan bobot ternak', 'included' => true],
                    ['name' => 'Dukungan tim ahli Aifarm', 'included' => true],
                    ['name' => 'Solusi IoT', 'included' => false],
                ],
                'button_text' => 'Coba gratis 7 hari',
                'button_variant' => 'default',
                'is_current' => true,
            ],
            [
                'id' => 'enterprise',
                'name' => 'Enterprise',
                'price' => 'Rp1,000,000',
                'price_numeric' => 1000000,
                'period' => '/Tahun/Peternakan',
                'description' => null,
                'features' => [
                    ['name' => 'Kelola kesehatan dan produksi ternak', 'included' => true],
                    ['name' => 'Lacak data ternak untuk pengambilan keputusan', 'included' => true],
                    ['name' => 'Stud book dan sertifikat per ternak', 'included' => true],
                    ['name' => 'Visualisasi data produktivitas susu dan bobot ternak', 'included' => true],
                    ['name' => 'Rangking produktivitas susu dan bobot ternak', 'included' => true],
                    ['name' => 'Dukungan tim ahli Aifarm', 'included' => true],
                    ['name' => 'Solusi IoT', 'included' => true],
                ],
                'button_text' => 'Upgrade',
                'button_variant' => 'outline',
                'is_current' => false,
            ],
        ];

        return Inertia::render('Transaksi/PaketLayanan', [
            'packages' => $packages,
        ]);
    }

    public function tagihan()
    {
        // Placeholder for billing page
        return Inertia::render('Transaksi/Tagihan', [
            'bills' => [], // Add billing data here
        ]);
    }

    public function riwayatPembayaran()
    {
        // Placeholder for payment history page
        return Inertia::render('Transaksi/RiwayatPembayaran', [
            'payments' => [], // Add payment history data here
        ]);
    }
}