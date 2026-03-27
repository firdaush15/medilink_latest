<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicineInventory;
use App\Models\MedicineBatch;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class MedicineInventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('💊 Seeding medicines with batch tracking...');

        // Get first pharmacist for stock movements
        $pharmacist = \App\Models\Pharmacist::first();
        
        if (!$pharmacist) {
            $this->command->warn('⚠️ No pharmacist found. Skipping medicine seeding.');
            return;
        }

        // Sample medicines with their initial batches
        $medicines = [
            [
                'medicine' => [
                    'medicine_name' => 'Paracetamol',
                    'generic_name' => 'Acetaminophen',
                    'brand_name' => 'Panadol',
                    'category' => 'Analgesic',
                    'form' => 'Tablet',
                    'strength' => '500mg',
                    'reorder_level' => 100,
                    'unit_price' => 0.50,
                    'storage_instructions' => 'Store at room temperature (15-30°C)',
                    'side_effects' => 'Rare: nausea, skin rash',
                    'contraindications' => 'Severe liver disease',
                    'requires_prescription' => false,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'PAR-2024-001',
                        'quantity' => 500,
                        'supplier' => 'Pharmaco Ltd',
                        'manufacture_date' => Carbon::now()->subMonths(6),
                        'expiry_date' => Carbon::now()->addMonths(18),
                        'unit_price' => 0.50,
                    ],
                    [
                        'batch_number' => 'PAR-2024-002',
                        'quantity' => 300,
                        'supplier' => 'Pharmaco Ltd',
                        'manufacture_date' => Carbon::now()->subMonths(3),
                        'expiry_date' => Carbon::now()->addMonths(21),
                        'unit_price' => 0.50,
                    ],
                ]
            ],
            
            [
                'medicine' => [
                    'medicine_name' => 'Amoxicillin',
                    'generic_name' => 'Amoxicillin',
                    'brand_name' => 'Amoxil',
                    'category' => 'Antibiotic',
                    'form' => 'Capsule',
                    'strength' => '500mg',
                    'reorder_level' => 200,
                    'unit_price' => 1.50,
                    'storage_instructions' => 'Store in a cool, dry place',
                    'side_effects' => 'Diarrhea, nausea, skin rash',
                    'contraindications' => 'Penicillin allergy',
                    'requires_prescription' => true,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'AMX-2024-001',
                        'quantity' => 1000,
                        'supplier' => 'Global Pharma Inc',
                        'manufacture_date' => Carbon::now()->subMonths(4),
                        'expiry_date' => Carbon::now()->addMonths(20),
                        'unit_price' => 1.50,
                    ],
                ]
            ],
            
            [
                'medicine' => [
                    'medicine_name' => 'Ciprofloxacin',
                    'generic_name' => 'Ciprofloxacin',
                    'brand_name' => 'Cipro',
                    'category' => 'Antibiotic',
                    'form' => 'Tablet',
                    'strength' => '500mg',
                    'reorder_level' => 50,
                    'unit_price' => 2.80,
                    'storage_instructions' => 'Protect from light and moisture',
                    'side_effects' => 'Nausea, dizziness, headache',
                    'contraindications' => 'Pregnancy, children under 18',
                    'requires_prescription' => true,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'CIP-2024-001',
                        'quantity' => 80,
                        'supplier' => 'MediSupply Co',
                        'manufacture_date' => Carbon::now()->subMonths(8),
                        'expiry_date' => Carbon::now()->addMonths(4), // Expiring soon!
                        'unit_price' => 2.80,
                    ],
                    [
                        'batch_number' => 'CIP-2024-002',
                        'quantity' => 200,
                        'supplier' => 'MediSupply Co',
                        'manufacture_date' => Carbon::now()->subMonths(2),
                        'expiry_date' => Carbon::now()->addMonths(22),
                        'unit_price' => 2.80,
                    ],
                ]
            ],
            
            [
                'medicine' => [
                    'medicine_name' => 'Metformin',
                    'generic_name' => 'Metformin Hydrochloride',
                    'brand_name' => 'Glucophage',
                    'category' => 'Antidiabetic',
                    'form' => 'Tablet',
                    'strength' => '500mg',
                    'reorder_level' => 150,
                    'unit_price' => 0.80,
                    'storage_instructions' => 'Store at room temperature',
                    'side_effects' => 'Nausea, diarrhea, stomach upset',
                    'contraindications' => 'Severe kidney disease, metabolic acidosis',
                    'requires_prescription' => true,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'MET-2024-001',
                        'quantity' => 500,
                        'supplier' => 'Diabetes Care Pharma',
                        'manufacture_date' => Carbon::now()->subMonths(5),
                        'expiry_date' => Carbon::now()->addMonths(19),
                        'unit_price' => 0.80,
                    ],
                ]
            ],
            
            [
                'medicine' => [
                    'medicine_name' => 'Ibuprofen',
                    'generic_name' => 'Ibuprofen',
                    'brand_name' => 'Advil',
                    'category' => 'Analgesic',
                    'form' => 'Tablet',
                    'strength' => '400mg',
                    'reorder_level' => 100,
                    'unit_price' => 0.60,
                    'storage_instructions' => 'Store at room temperature',
                    'side_effects' => 'Stomach pain, heartburn, nausea',
                    'contraindications' => 'Active peptic ulcer, severe heart failure',
                    'requires_prescription' => false,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'IBU-2024-001',
                        'quantity' => 30, // Low stock example
                        'supplier' => 'Pain Relief Inc',
                        'manufacture_date' => Carbon::now()->subMonths(10),
                        'expiry_date' => Carbon::now()->addMonths(2), // Critical expiry!
                        'unit_price' => 0.60,
                    ],
                ]
            ],
            
            [
                'medicine' => [
                    'medicine_name' => 'Omeprazole',
                    'generic_name' => 'Omeprazole',
                    'brand_name' => 'Prilosec',
                    'category' => 'Antacid',
                    'form' => 'Capsule',
                    'strength' => '20mg',
                    'reorder_level' => 80,
                    'unit_price' => 1.20,
                    'storage_instructions' => 'Store in a dry place, protect from moisture',
                    'side_effects' => 'Headache, nausea, stomach pain',
                    'contraindications' => 'Allergy to proton pump inhibitors',
                    'requires_prescription' => true,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'OME-2024-001',
                        'quantity' => 250,
                        'supplier' => 'GI Health Pharma',
                        'manufacture_date' => Carbon::now()->subMonths(3),
                        'expiry_date' => Carbon::now()->addMonths(21),
                        'unit_price' => 1.20,
                    ],
                ]
            ],
            
            [
                'medicine' => [
                    'medicine_name' => 'Losartan',
                    'generic_name' => 'Losartan Potassium',
                    'brand_name' => 'Cozaar',
                    'category' => 'Antihypertensive',
                    'form' => 'Tablet',
                    'strength' => '50mg',
                    'reorder_level' => 100,
                    'unit_price' => 1.80,
                    'storage_instructions' => 'Store at room temperature',
                    'side_effects' => 'Dizziness, fatigue, low blood pressure',
                    'contraindications' => 'Pregnancy, severe liver disease',
                    'requires_prescription' => true,
                    'is_controlled_substance' => false,
                ],
                'batches' => [
                    [
                        'batch_number' => 'LOS-2024-001',
                        'quantity' => 400,
                        'supplier' => 'CardioMed Supplies',
                        'manufacture_date' => Carbon::now()->subMonths(4),
                        'expiry_date' => Carbon::now()->addMonths(20),
                        'unit_price' => 1.80,
                    ],
                ]
            ],
        ];

        // Create medicines and their batches
        foreach ($medicines as $data) {
            // Calculate total stock from batches
            $totalStock = collect($data['batches'])->sum('quantity');
            
            // Determine status based on total stock
            $status = 'Active';
            if ($totalStock == 0) {
                $status = 'Out of Stock';
            } elseif ($totalStock <= $data['medicine']['reorder_level']) {
                $status = 'Low Stock';
            }
            
            // Create the medicine record (WITHOUT batch-specific fields)
            $medicine = MedicineInventory::create([
                'medicine_name' => $data['medicine']['medicine_name'],
                'generic_name' => $data['medicine']['generic_name'],
                'brand_name' => $data['medicine']['brand_name'],
                'category' => $data['medicine']['category'],
                'form' => $data['medicine']['form'],
                'strength' => $data['medicine']['strength'],
                'quantity_in_stock' => $totalStock,
                'reorder_level' => $data['medicine']['reorder_level'],
                'unit_price' => $data['medicine']['unit_price'],
                'storage_instructions' => $data['medicine']['storage_instructions'],
                'side_effects' => $data['medicine']['side_effects'],
                'contraindications' => $data['medicine']['contraindications'],
                'requires_prescription' => $data['medicine']['requires_prescription'],
                'is_controlled_substance' => $data['medicine']['is_controlled_substance'],
                'status' => $status,
            ]);
            
            // Create batch records for this medicine
            foreach ($data['batches'] as $batchData) {
                // Determine batch status
                $batchStatus = 'active';
                if ($batchData['expiry_date']->isPast()) {
                    $batchStatus = 'expired';
                } elseif ($batchData['quantity'] <= 0) {
                    $batchStatus = 'depleted';
                }
                
                $batch = MedicineBatch::create([
                    'medicine_id' => $medicine->medicine_id,
                    'batch_number' => $batchData['batch_number'],
                    'quantity' => $batchData['quantity'],
                    'supplier' => $batchData['supplier'],
                    'manufacture_date' => $batchData['manufacture_date'],
                    'expiry_date' => $batchData['expiry_date'],
                    'received_date' => $batchData['manufacture_date'], // Use manufacture date as received
                    'unit_price' => $batchData['unit_price'],
                    'status' => $batchStatus,
                    'notes' => 'Initial seed data',
                ]);
                
                // Create stock movement record
                StockMovement::create([
                    'medicine_id' => $medicine->medicine_id,
                    'batch_id' => $batch->batch_id, // ✅ Link to batch
                    'pharmacist_id' => $pharmacist->pharmacist_id,
                    'movement_type' => 'Stock In',
                    'quantity' => $batchData['quantity'],
                    'balance_after' => $medicine->quantity_in_stock,
                    'notes' => "Initial stock registration - Batch: {$batch->batch_number}",
                ]);
            }
            
            $this->command->info("✓ Created: {$medicine->medicine_name} with " . count($data['batches']) . " batch(es)");
        }
        
        $this->command->info("\n🎉 Successfully seeded " . count($medicines) . " medicines with batch tracking!");
    }
}