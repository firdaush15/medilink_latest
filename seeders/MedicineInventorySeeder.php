<?php
// database/seeders/MedicineInventorySeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicineInventory;
use App\Models\StaffAlert;
use App\Models\User;
use Carbon\Carbon;

class MedicineInventorySeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('💊 Seeding Medicine Inventory...');

        $medicines = [
            // ========================================
            // NORMAL STOCK MEDICINES
            // ========================================
            [
                'medicine_name' => 'Amoxicillin',
                'generic_name' => 'Amoxicillin',
                'brand_name' => 'Amoxil',
                'category' => 'Antibiotic',
                'form' => 'Capsule',
                'strength' => '500mg',
                'quantity_in_stock' => 200,
                'reorder_level' => 50,
                'unit_price' => 1.20,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'AMX2024-001',
                'manufacture_date' => Carbon::now()->subMonths(6),
                'expiry_date' => Carbon::now()->addYears(2),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Paracetamol',
                'generic_name' => 'Acetaminophen',
                'brand_name' => 'Panadol',
                'category' => 'Analgesic',
                'form' => 'Tablet',
                'strength' => '500mg',
                'quantity_in_stock' => 500,
                'reorder_level' => 100,
                'unit_price' => 0.50,
                'supplier' => 'MediSupply Inc',
                'batch_number' => 'PAR2024-102',
                'manufacture_date' => Carbon::now()->subMonths(4),
                'expiry_date' => Carbon::now()->addMonths(18),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Metformin',
                'generic_name' => 'Metformin HCl',
                'brand_name' => 'Glucophage',
                'category' => 'Antidiabetic',
                'form' => 'Tablet',
                'strength' => '500mg',
                'quantity_in_stock' => 150,
                'reorder_level' => 80,
                'unit_price' => 2.50,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'MET2024-045',
                'manufacture_date' => Carbon::now()->subMonths(3),
                'expiry_date' => Carbon::now()->addYears(2),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Ibuprofen',
                'generic_name' => 'Ibuprofen',
                'brand_name' => 'Advil',
                'category' => 'Analgesic',
                'form' => 'Tablet',
                'strength' => '400mg',
                'quantity_in_stock' => 300,
                'reorder_level' => 75,
                'unit_price' => 0.80,
                'supplier' => 'MediSupply Inc',
                'batch_number' => 'IBU2024-087',
                'manufacture_date' => Carbon::now()->subMonths(5),
                'expiry_date' => Carbon::now()->addMonths(24),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Losartan',
                'generic_name' => 'Losartan Potassium',
                'brand_name' => 'Cozaar',
                'category' => 'Antihypertensive',
                'form' => 'Tablet',
                'strength' => '50mg',
                'quantity_in_stock' => 120,
                'reorder_level' => 60,
                'unit_price' => 3.00,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'LOS2024-033',
                'manufacture_date' => Carbon::now()->subMonths(2),
                'expiry_date' => Carbon::now()->addYears(3),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Omeprazole',
                'generic_name' => 'Omeprazole',
                'brand_name' => 'Prilosec',
                'category' => 'Gastrointestinal',
                'form' => 'Capsule',
                'strength' => '20mg',
                'quantity_in_stock' => 180,
                'reorder_level' => 70,
                'unit_price' => 1.80,
                'supplier' => 'MediSupply Inc',
                'batch_number' => 'OME2024-056',
                'manufacture_date' => Carbon::now()->subMonths(4),
                'expiry_date' => Carbon::now()->addMonths(30),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Cetirizine',
                'generic_name' => 'Cetirizine HCl',
                'brand_name' => 'Zyrtec',
                'category' => 'Antihistamine',
                'form' => 'Tablet',
                'strength' => '10mg',
                'quantity_in_stock' => 250,
                'reorder_level' => 100,
                'unit_price' => 0.70,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'CET2024-098',
                'manufacture_date' => Carbon::now()->subMonths(3),
                'expiry_date' => Carbon::now()->addYears(2),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Aspirin',
                'generic_name' => 'Acetylsalicylic Acid',
                'brand_name' => 'Aspirin',
                'category' => 'Cardiovascular',
                'form' => 'Tablet',
                'strength' => '100mg',
                'quantity_in_stock' => 400,
                'reorder_level' => 150,
                'unit_price' => 0.30,
                'supplier' => 'MediSupply Inc',
                'batch_number' => 'ASP2024-112',
                'manufacture_date' => Carbon::now()->subMonths(6),
                'expiry_date' => Carbon::now()->addYears(3),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Salbutamol Inhaler',
                'generic_name' => 'Salbutamol',
                'brand_name' => 'Ventolin',
                'category' => 'Respiratory',
                'form' => 'Inhaler',
                'strength' => '100mcg/dose',
                'quantity_in_stock' => 80,
                'reorder_level' => 40,
                'unit_price' => 15.00,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'SAL2024-067',
                'manufacture_date' => Carbon::now()->subMonths(2),
                'expiry_date' => Carbon::now()->addYears(2),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Vitamin C',
                'generic_name' => 'Ascorbic Acid',
                'brand_name' => 'Redoxon',
                'category' => 'Vitamins & Supplements',
                'form' => 'Tablet',
                'strength' => '1000mg',
                'quantity_in_stock' => 350,
                'reorder_level' => 120,
                'unit_price' => 0.40,
                'supplier' => 'MediSupply Inc',
                'batch_number' => 'VIT2024-145',
                'manufacture_date' => Carbon::now()->subMonths(5),
                'expiry_date' => Carbon::now()->addYears(2),
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],

            // ========================================
            // LOW STOCK MEDICINES (Will trigger alerts)
            // ========================================
            [
                'medicine_name' => 'Ciprofloxacin',
                'generic_name' => 'Ciprofloxacin',
                'brand_name' => 'Cipro',
                'category' => 'Antibiotic',
                'form' => 'Tablet',
                'strength' => '500mg',
                'quantity_in_stock' => 30, // ⚠️ LOW STOCK
                'reorder_level' => 50,
                'unit_price' => 2.80,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'CIP2024-078',
                'manufacture_date' => Carbon::now()->subMonths(4),
                'expiry_date' => Carbon::now()->addYears(2),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Low Stock',
            ],
            [
                'medicine_name' => 'Insulin Glargine',
                'generic_name' => 'Insulin Glargine',
                'brand_name' => 'Lantus',
                'category' => 'Antidiabetic',
                'form' => 'Injection',
                'strength' => '100units/ml',
                'quantity_in_stock' => 15, // ⚠️ LOW STOCK
                'reorder_level' => 30,
                'unit_price' => 45.00,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'INS2024-023',
                'manufacture_date' => Carbon::now()->subMonths(2),
                'expiry_date' => Carbon::now()->addMonths(18),
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Low Stock',
            ],

            // ========================================
            // EXPIRING SOON MEDICINES (Will trigger alerts)
            // ========================================
            [
                'medicine_name' => 'Cough Syrup',
                'generic_name' => 'Dextromethorphan',
                'brand_name' => 'Robitussin',
                'category' => 'Respiratory',
                'form' => 'Syrup',
                'strength' => '15mg/5ml',
                'quantity_in_stock' => 45,
                'reorder_level' => 25,
                'unit_price' => 8.50,
                'supplier' => 'MediSupply Inc',
                'batch_number' => 'CSY2023-189',
                'manufacture_date' => Carbon::now()->subYears(2),
                'expiry_date' => Carbon::now()->addDays(25), // ⚠️ EXPIRING SOON
                'requires_prescription' => false,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],
            [
                'medicine_name' => 'Eye Drops',
                'generic_name' => 'Chloramphenicol',
                'brand_name' => 'Chlorsig',
                'category' => 'Ophthalmic',
                'form' => 'Drops',
                'strength' => '0.5%',
                'quantity_in_stock' => 20,
                'reorder_level' => 15,
                'unit_price' => 12.00,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'EYE2023-156',
                'manufacture_date' => Carbon::now()->subYears(1),
                'expiry_date' => Carbon::now()->addDays(20), // ⚠️ EXPIRING SOON
                'requires_prescription' => true,
                'is_controlled_substance' => false,
                'status' => 'Active',
            ],

            // ========================================
            // OUT OF STOCK MEDICINE (Critical alert)
            // ========================================
            [
                'medicine_name' => 'Morphine',
                'generic_name' => 'Morphine Sulfate',
                'brand_name' => 'MST Continus',
                'category' => 'Opioid Analgesic',
                'form' => 'Tablet',
                'strength' => '10mg',
                'quantity_in_stock' => 0, // ⚠️ OUT OF STOCK
                'reorder_level' => 20,
                'unit_price' => 8.00,
                'supplier' => 'Pharmaco Ltd',
                'batch_number' => 'MOR2024-089',
                'manufacture_date' => Carbon::now()->subMonths(3),
                'expiry_date' => Carbon::now()->addYears(1),
                'requires_prescription' => true,
                'is_controlled_substance' => true,
                'status' => 'Out of Stock',
            ],
        ];

        $createdCount = 0;
        $lowStockCount = 0;
        $expiringCount = 0;
        $outOfStockCount = 0;

        foreach ($medicines as $medicine) {
            $med = MedicineInventory::create($medicine);
            $createdCount++;

            // Track alert-worthy medicines
            if ($med->status === 'Low Stock') {
                $lowStockCount++;
            }
            if ($med->status === 'Out of Stock') {
                $outOfStockCount++;
            }
            if ($med->expiry_date->diffInDays(now()) <= 30 && $med->expiry_date->isFuture()) {
                $expiringCount++;
            }
        }

        $this->command->info("✅ Created {$createdCount} medicines");
        $this->command->info("   - {$lowStockCount} Low Stock");
        $this->command->info("   - {$expiringCount} Expiring Soon");
        $this->command->info("   - {$outOfStockCount} Out of Stock");

        // ========================================
        // AUTO-GENERATE ALERTS FOR PHARMACISTS
        // ========================================
        $this->command->info('🔔 Generating inventory alerts for pharmacists...');

        // Get all pharmacist users
        $pharmacists = User::where('role', 'pharmacist')->get();

        if ($pharmacists->isEmpty()) {
            $this->command->warn('⚠️  No pharmacists found. Skipping alert generation.');
            return;
        }

        $alertCount = 0;

        foreach ($pharmacists as $pharmacist) {
            // Low Stock Alerts
            $lowStockMedicines = MedicineInventory::where('status', 'Low Stock')->get();
            foreach ($lowStockMedicines as $medicine) {
                StaffAlert::create([
                    'sender_id' => 1, // System (admin)
                    'sender_type' => 'system',
                    'recipient_id' => $pharmacist->id,
                    'recipient_type' => 'pharmacist',
                    'medicine_id' => $medicine->medicine_id,
                    'alert_type' => 'Low Stock',
                    'priority' => 'High',
                    'alert_title' => "Low Stock Alert: {$medicine->medicine_name}",
                    'alert_message' => "Current stock: {$medicine->quantity_in_stock} units. Reorder level: {$medicine->reorder_level} units. Please restock immediately.",
                    'action_url' => null, // ✅ Will be updated when route exists
                ]);
                $alertCount++;
            }

            // Out of Stock Alerts (Critical)
            $outOfStockMedicines = MedicineInventory::where('status', 'Out of Stock')->get();
            foreach ($outOfStockMedicines as $medicine) {
                StaffAlert::create([
                    'sender_id' => 1,
                    'sender_type' => 'system',
                    'recipient_id' => $pharmacist->id,
                    'recipient_type' => 'pharmacist',
                    'medicine_id' => $medicine->medicine_id,
                    'alert_type' => 'Restock Needed',
                    'priority' => 'Critical',
                    'alert_title' => "🚨 OUT OF STOCK: {$medicine->medicine_name}",
                    'alert_message' => "Medicine is completely out of stock. URGENT RESTOCK REQUIRED. This may affect patient care.",
                    'action_url' => null, // ✅ Will be updated when route exists
                ]);
                $alertCount++;
            }

            // Expiring Soon Alerts
            $expiringMedicines = MedicineInventory::where('expiry_date', '<=', now()->addDays(30))
                ->where('expiry_date', '>', now())
                ->where('status', '!=', 'Expired')
                ->get();

            foreach ($expiringMedicines as $medicine) {
                // ✅ USE THE MODEL METHOD instead of Carbon's diffInDays
                $daysUntilExpiry = $medicine->getDaysUntilExpiry(); // This now returns integer!

                StaffAlert::create([
                    'sender_id' => 1,
                    'sender_type' => 'system',
                    'recipient_id' => $pharmacist->id,
                    'recipient_type' => 'pharmacist',
                    'medicine_id' => $medicine->medicine_id,
                    'alert_type' => 'Expiring Soon',
                    'priority' => $daysUntilExpiry <= 14 ? 'Urgent' : 'High',
                    'alert_title' => "Medicine Expiring Soon: {$medicine->medicine_name}",
                    'alert_message' => "Medicine will expire on {$medicine->expiry_date->format('M d, Y')}. Please verify stock and mark for disposal if needed.", // ✅ Removed the "days" part entirely (cleaner message)
                    'action_url' => null,
                ]);
                $alertCount++;
            }
        }

        $this->command->info("✅ Generated {$alertCount} inventory alerts for pharmacists");
        $this->command->info('💊 Medicine inventory seeding completed successfully!');
    }
}
