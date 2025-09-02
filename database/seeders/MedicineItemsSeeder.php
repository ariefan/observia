<?php

namespace Database\Seeders;

use App\Models\Farm;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\InventoryUnit;
use Illuminate\Database\Seeder;

class MedicineItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the "Obat-obatan" category
        $medicineCategory = InventoryCategory::where('name', 'Obat-obatan')->first();
        
        // Get units
        $tabletUnit = InventoryUnit::where('symbol', 'tablet')->first();
        $vialUnit = InventoryUnit::where('symbol', 'vial')->first();
        $bottleUnit = InventoryUnit::where('symbol', 'btl')->first();
        $mlUnit = InventoryUnit::where('symbol', 'ml')->first();
        $sachetsUnit = InventoryUnit::where('symbol', 'sachet')->first();
        $tubeUnit = InventoryUnit::where('symbol', 'tube')->first();
        $kapsulUnit = InventoryUnit::where('symbol', 'kapsul')->first();
        $ampulUnit = InventoryUnit::where('symbol', 'ampul')->first();

        // Get all farms to seed medicines for each farm
        $farms = Farm::all();

        // Common veterinary medicines with ATCvet codes
        $medicines = [
            // Antibiotics (J01 - Antibacterials for systemic use)
            [
                'name' => 'Amoxicillin',
                'brand' => 'Vetamoxyl',
                'description' => 'Broad-spectrum antibiotic for bacterial infections',
                'sku' => 'QJ01CA04',
                'unit_id' => $tabletUnit->id,
                'unit_cost' => 2500.00,
                'selling_price' => 3000.00,
                'minimum_stock' => 50.00,
                'current_stock' => 200.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Ampicillin',
                'brand' => 'Vetampi',
                'description' => 'Penicillin antibiotic for gram-positive and some gram-negative bacteria',
                'sku' => 'QJ01CA01',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 15000.00,
                'selling_price' => 18000.00,
                'minimum_stock' => 10.00,
                'current_stock' => 50.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Doxycycline',
                'brand' => 'Vetdoxy',
                'description' => 'Tetracycline antibiotic for respiratory and systemic infections',
                'sku' => 'QJ01AA02',
                'unit_id' => $tabletUnit->id,
                'unit_cost' => 3000.00,
                'selling_price' => 3500.00,
                'minimum_stock' => 30.00,
                'current_stock' => 150.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Enrofloxacin',
                'brand' => 'Baytril',
                'description' => 'Fluoroquinolone antibiotic for serious bacterial infections',
                'sku' => 'QJ01MA90',
                'unit_id' => $tabletUnit->id,
                'unit_cost' => 5000.00,
                'selling_price' => 6000.00,
                'minimum_stock' => 20.00,
                'current_stock' => 80.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Oxytetracycline',
                'brand' => 'Terramycin',
                'description' => 'Broad-spectrum antibiotic for various bacterial infections',
                'sku' => 'QJ01AA06',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 12000.00,
                'selling_price' => 15000.00,
                'minimum_stock' => 15.00,
                'current_stock' => 60.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Anti-parasitic drugs (P02 - Anthelmintics, P03 - Ectoparasiticides)
            [
                'name' => 'Albendazole',
                'brand' => 'Valbazen',
                'description' => 'Broad-spectrum anthelmintic for internal parasites',
                'sku' => 'QP52AC11',
                'unit_id' => $tabletUnit->id,
                'unit_cost' => 1500.00,
                'selling_price' => 2000.00,
                'minimum_stock' => 100.00,
                'current_stock' => 300.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Ivermectin',
                'brand' => 'Ivomec',
                'description' => 'Antiparasitic for internal and external parasites',
                'sku' => 'QP54AA11',
                'unit_id' => $mlUnit->id,
                'unit_cost' => 25000.00,
                'selling_price' => 30000.00,
                'minimum_stock' => 50.00,
                'current_stock' => 100.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Fenbendazole',
                'brand' => 'Panacur',
                'description' => 'Broad-spectrum benzimidazole anthelmintic',
                'sku' => 'QP52AC13',
                'unit_id' => $sachetsUnit->id,
                'unit_cost' => 8000.00,
                'selling_price' => 10000.00,
                'minimum_stock' => 25.00,
                'current_stock' => 75.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Anti-inflammatory drugs (M01 - Anti-inflammatory and antirheumatic products)
            [
                'name' => 'Meloxicam',
                'brand' => 'Metacam',
                'description' => 'NSAID for pain and inflammation relief',
                'sku' => 'QM01AC06',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 45000.00,
                'selling_price' => 55000.00,
                'minimum_stock' => 5.00,
                'current_stock' => 20.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Flunixin Meglumine',
                'brand' => 'Banamine',
                'description' => 'NSAID for pain relief and anti-inflammatory treatment',
                'sku' => 'QM01AG90',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 35000.00,
                'selling_price' => 42000.00,
                'minimum_stock' => 8.00,
                'current_stock' => 25.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Vaccines and immunologicals (J07 - Vaccines)
            [
                'name' => 'Newcastle Disease Vaccine',
                'brand' => 'Nobivac ND',
                'description' => 'Live vaccine against Newcastle Disease in poultry',
                'sku' => 'QJ07BG01',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 85000.00,
                'selling_price' => 100000.00,
                'minimum_stock' => 10.00,
                'current_stock' => 30.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'FMD Vaccine',
                'brand' => 'Aftovaxpur',
                'description' => 'Inactivated vaccine against Foot and Mouth Disease',
                'sku' => 'QJ07BH01',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 125000.00,
                'selling_price' => 150000.00,
                'minimum_stock' => 5.00,
                'current_stock' => 15.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Vitamins and minerals (A11 - Vitamins)
            [
                'name' => 'Vitamin B Complex',
                'brand' => 'B-Complex Forte',
                'description' => 'Injectable vitamin B complex for metabolic support',
                'sku' => 'QA11EA',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 18000.00,
                'selling_price' => 22000.00,
                'minimum_stock' => 20.00,
                'current_stock' => 80.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Vitamin AD3E',
                'brand' => 'Vitasel',
                'description' => 'Fat-soluble vitamins A, D3, and E injection',
                'sku' => 'QA11CC',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 22000.00,
                'selling_price' => 27000.00,
                'minimum_stock' => 15.00,
                'current_stock' => 60.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Hormones (G03 - Sex hormones and modulators of the genital system)
            [
                'name' => 'Oxytocin',
                'brand' => 'Syntocin',
                'description' => 'Hormone for inducing labor and milk let-down',
                'sku' => 'QG02AB01',
                'unit_id' => $ampulUnit->id,
                'unit_cost' => 8000.00,
                'selling_price' => 10000.00,
                'minimum_stock' => 50.00,
                'current_stock' => 150.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'PGF2α (Dinoprost)',
                'brand' => 'Lutalyse',
                'description' => 'Prostaglandin for estrus synchronization',
                'sku' => 'QG02AD01',
                'unit_id' => $vialUnit->id,
                'unit_cost' => 65000.00,
                'selling_price' => 80000.00,
                'minimum_stock' => 5.00,
                'current_stock' => 20.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Antiseptics and disinfectants (D08 - Antiseptics and disinfectants)
            [
                'name' => 'Povidone Iodine',
                'brand' => 'Betadine',
                'description' => 'Antiseptic solution for wound cleaning',
                'sku' => 'QD08AG02',
                'unit_id' => $bottleUnit->id,
                'unit_cost' => 25000.00,
                'selling_price' => 30000.00,
                'minimum_stock' => 10.00,
                'current_stock' => 40.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Chlorhexidine',
                'brand' => 'Hibiscrub',
                'description' => 'Antiseptic for skin disinfection',
                'sku' => 'QD08AC02',
                'unit_id' => $bottleUnit->id,
                'unit_cost' => 35000.00,
                'selling_price' => 42000.00,
                'minimum_stock' => 8.00,
                'current_stock' => 30.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            
            // Digestive system drugs (A03 - Drugs for functional gastrointestinal disorders)
            [
                'name' => 'Activated Charcoal',
                'brand' => 'Carbomix Vet',
                'description' => 'Adsorbent for toxin binding in digestive tract',
                'sku' => 'QA07BA01',
                'unit_id' => $sachetsUnit->id,
                'unit_cost' => 12000.00,
                'selling_price' => 15000.00,
                'minimum_stock' => 30.00,
                'current_stock' => 100.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
            [
                'name' => 'Probiotics',
                'brand' => 'BioVet Plus',
                'description' => 'Beneficial bacteria for digestive health',
                'sku' => 'QA07FA',
                'unit_id' => $sachetsUnit->id,
                'unit_cost' => 15000.00,
                'selling_price' => 18000.00,
                'minimum_stock' => 25.00,
                'current_stock' => 80.00,
                'track_expiry' => true,
                'expiry_date' => now()->addMonths(rand(6, 24))->format('Y-m-d'),
            ],
        ];

        // Create medicines for each farm
        foreach ($farms as $farm) {
            foreach ($medicines as $medicine) {
                $medicineData = array_merge($medicine, [
                    'farm_id' => $farm->id,
                    'category_id' => $medicineCategory->id,
                    'is_active' => true,
                ]);

                InventoryItem::create($medicineData);
            }
        }

        $this->command->info('Medicine items seeded successfully for ' . $farms->count() . ' farms!');
    }
}