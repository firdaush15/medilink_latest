<?php
// database/seeders/ProcedurePricesSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcedurePricesSeeder extends Seeder
{
    public function run()
    {
        $procedures = [
            // ========================================
            // CONSULTATIONS (Based on specialization)
            // ========================================
            [
                'procedure_code' => 'CONS-GEN',
                'procedure_name' => 'General Medicine Consultation',
                'category' => 'consultation',
                'description' => 'Standard general practitioner consultation',
                'base_price' => 50.00,
            ],
            [
                'procedure_code' => 'CONS-PEDI',
                'procedure_name' => 'Pediatrics Consultation',
                'category' => 'consultation',
                'description' => 'Specialist pediatric consultation',
                'base_price' => 60.00,
            ],
            [
                'procedure_code' => 'CONS-CARD',
                'procedure_name' => 'Cardiology Consultation',
                'category' => 'consultation',
                'description' => 'Specialist cardiology consultation',
                'base_price' => 80.00,
            ],
            [
                'procedure_code' => 'CONS-ORTHO',
                'procedure_name' => 'Orthopedics Consultation',
                'category' => 'consultation',
                'description' => 'Specialist orthopedic consultation',
                'base_price' => 75.00,
            ],
            [
                'procedure_code' => 'CONS-DERM',
                'procedure_name' => 'Dermatology Consultation',
                'category' => 'consultation',
                'description' => 'Specialist dermatology consultation',
                'base_price' => 65.00,
            ],
            [
                'procedure_code' => 'CONS-PSYCH',
                'procedure_name' => 'Psychiatry Consultation',
                'category' => 'consultation',
                'description' => 'Specialist psychiatric consultation',
                'base_price' => 90.00,
            ],
            [
                'procedure_code' => 'CONS-NEURO',
                'procedure_name' => 'Neurology Consultation',
                'category' => 'consultation',
                'description' => 'Specialist neurology consultation',
                'base_price' => 85.00,
            ],
            [
                'procedure_code' => 'CONS-GASTRO',
                'procedure_name' => 'Gastroenterology Consultation',
                'category' => 'consultation',
                'description' => 'Specialist gastroenterology consultation',
                'base_price' => 75.00,
            ],
            [
                'procedure_code' => 'CONS-ENDO',
                'procedure_name' => 'Endocrinology Consultation',
                'category' => 'consultation',
                'description' => 'Specialist endocrinology consultation',
                'base_price' => 70.00,
            ],
            
            // ========================================
            // BLOOD TESTS
            // ========================================
            [
                'procedure_code' => 'LAB-CBC',
                'procedure_name' => 'Complete Blood Count (CBC)',
                'category' => 'blood_test',
                'description' => 'Full blood panel analysis',
                'base_price' => 45.00,
            ],
            [
                'procedure_code' => 'LAB-FBS',
                'procedure_name' => 'Fasting Blood Sugar (FBS)',
                'category' => 'blood_test',
                'description' => 'Glucose level test - fasting required',
                'base_price' => 25.00,
            ],
            [
                'procedure_code' => 'LAB-HBA1C',
                'procedure_name' => 'HbA1c Test',
                'category' => 'blood_test',
                'description' => 'Glycated hemoglobin test for diabetes monitoring',
                'base_price' => 55.00,
            ],
            [
                'procedure_code' => 'LAB-LIPID',
                'procedure_name' => 'Lipid Profile',
                'category' => 'blood_test',
                'description' => 'Cholesterol and triglycerides test',
                'base_price' => 65.00,
            ],
            [
                'procedure_code' => 'LAB-LFT',
                'procedure_name' => 'Liver Function Test (LFT)',
                'category' => 'blood_test',
                'description' => 'Comprehensive liver enzyme panel',
                'base_price' => 70.00,
            ],
            [
                'procedure_code' => 'LAB-RFT',
                'procedure_name' => 'Renal Function Test (RFT)',
                'category' => 'blood_test',
                'description' => 'Kidney function assessment',
                'base_price' => 70.00,
            ],
            [
                'procedure_code' => 'LAB-THYROID',
                'procedure_name' => 'Thyroid Function Test',
                'category' => 'blood_test',
                'description' => 'TSH, T3, T4 levels',
                'base_price' => 85.00,
            ],
            [
                'procedure_code' => 'LAB-URIC',
                'procedure_name' => 'Uric Acid Test',
                'category' => 'blood_test',
                'description' => 'Test for gout and kidney stones',
                'base_price' => 35.00,
            ],
            [
                'procedure_code' => 'LAB-ESR',
                'procedure_name' => 'ESR (Erythrocyte Sedimentation Rate)',
                'category' => 'blood_test',
                'description' => 'Inflammation marker test',
                'base_price' => 30.00,
            ],
            
            // ========================================
            // IMAGING
            // ========================================
            [
                'procedure_code' => 'IMG-XRAY-CHEST',
                'procedure_name' => 'Chest X-Ray',
                'category' => 'imaging',
                'description' => 'Chest radiograph (PA view)',
                'base_price' => 90.00,
            ],
            [
                'procedure_code' => 'IMG-XRAY',
                'procedure_name' => 'X-Ray (Single View)',
                'category' => 'imaging',
                'description' => 'Standard X-ray imaging per view',
                'base_price' => 80.00,
            ],
            [
                'procedure_code' => 'IMG-ULTRASOUND',
                'procedure_name' => 'Ultrasound Scan',
                'category' => 'imaging',
                'description' => 'Diagnostic ultrasound imaging',
                'base_price' => 150.00,
            ],
            [
                'procedure_code' => 'IMG-CT',
                'procedure_name' => 'CT Scan',
                'category' => 'imaging',
                'description' => 'Computed tomography scan',
                'base_price' => 450.00,
            ],
            [
                'procedure_code' => 'IMG-MRI',
                'procedure_name' => 'MRI Scan',
                'category' => 'imaging',
                'description' => 'Magnetic resonance imaging',
                'base_price' => 800.00,
            ],
            
            // ========================================
            // DIAGNOSTIC TESTS
            // ========================================
            [
                'procedure_code' => 'DIAG-ECG',
                'procedure_name' => 'ECG/EKG',
                'category' => 'diagnostic_test',
                'description' => 'Electrocardiogram - heart rhythm test',
                'base_price' => 60.00,
            ],
            [
                'procedure_code' => 'DIAG-ECHO',
                'procedure_name' => 'Echocardiogram',
                'category' => 'diagnostic_test',
                'description' => 'Cardiac ultrasound',
                'base_price' => 250.00,
            ],
            [
                'procedure_code' => 'DIAG-STRESS',
                'procedure_name' => 'Stress Test',
                'category' => 'diagnostic_test',
                'description' => 'Cardiac stress test',
                'base_price' => 200.00,
            ],
            [
                'procedure_code' => 'DIAG-URINE',
                'procedure_name' => 'Urinalysis',
                'category' => 'diagnostic_test',
                'description' => 'Complete urine analysis',
                'base_price' => 35.00,
            ],
            [
                'procedure_code' => 'DIAG-STOOL',
                'procedure_name' => 'Stool Test',
                'category' => 'diagnostic_test',
                'description' => 'Stool analysis for infections',
                'base_price' => 40.00,
            ],
            [
                'procedure_code' => 'DIAG-SPIROMETRY',
                'procedure_name' => 'Spirometry (Lung Function Test)',
                'category' => 'diagnostic_test',
                'description' => 'Pulmonary function test',
                'base_price' => 120.00,
            ],
            
            // ========================================
            // MINOR PROCEDURES
            // ========================================
            [
                'procedure_code' => 'PROC-WOUND',
                'procedure_name' => 'Wound Dressing',
                'category' => 'minor_procedure',
                'description' => 'Wound cleaning and dressing',
                'base_price' => 40.00,
            ],
            [
                'procedure_code' => 'PROC-SUTURE-SIMPLE',
                'procedure_name' => 'Simple Suturing (1-5 stitches)',
                'category' => 'minor_procedure',
                'description' => 'Simple wound closure',
                'base_price' => 120.00,
            ],
            [
                'procedure_code' => 'PROC-SUTURE-COMPLEX',
                'procedure_name' => 'Complex Suturing (6+ stitches)',
                'category' => 'minor_procedure',
                'description' => 'Complex wound closure',
                'base_price' => 200.00,
            ],
            [
                'procedure_code' => 'PROC-INJECTION',
                'procedure_name' => 'Injection Administration (IM/IV)',
                'category' => 'minor_procedure',
                'description' => 'Intramuscular or intravenous injection',
                'base_price' => 25.00,
            ],
            [
                'procedure_code' => 'PROC-NEBULIZER',
                'procedure_name' => 'Nebulizer Treatment',
                'category' => 'minor_procedure',
                'description' => 'Respiratory nebulization therapy',
                'base_price' => 35.00,
            ],
            [
                'procedure_code' => 'PROC-IV-DRIP',
                'procedure_name' => 'IV Drip Setup',
                'category' => 'minor_procedure',
                'description' => 'Intravenous fluid therapy setup',
                'base_price' => 50.00,
            ],
            [
                'procedure_code' => 'PROC-CATHETER',
                'procedure_name' => 'Catheterization',
                'category' => 'minor_procedure',
                'description' => 'Urinary catheter insertion',
                'base_price' => 80.00,
            ],
            [
                'procedure_code' => 'PROC-DRESSING-COMPLEX',
                'procedure_name' => 'Complex Wound Dressing',
                'category' => 'minor_procedure',
                'description' => 'Specialized wound care',
                'base_price' => 60.00,
            ],
        ];
        
        foreach ($procedures as $procedure) {
            DB::table('procedure_prices')->insert($procedure);
        }
    }
}