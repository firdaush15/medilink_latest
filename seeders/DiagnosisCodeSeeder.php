<?php
// database/seeders/DiagnosisCodeSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiagnosisCode;

class DiagnosisCodeSeeder extends Seeder
{
    public function run()
    {
        $diagnoses = [
            // RESPIRATORY INFECTIONS
            [
                'icd10_code' => 'J11.1',
                'diagnosis_name' => 'Influenza A',
                'category' => 'Respiratory Infection',
                'description' => 'Acute respiratory illness caused by influenza type A virus',
                'severity' => 'Moderate',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => true,
                'typical_recovery_days' => 7,
            ],
            [
                'icd10_code' => 'J11.2',
                'diagnosis_name' => 'Influenza B',
                'category' => 'Respiratory Infection',
                'description' => 'Acute respiratory illness caused by influenza type B virus',
                'severity' => 'Moderate',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => true,
                'typical_recovery_days' => 7,
            ],
            [
                'icd10_code' => 'J00',
                'diagnosis_name' => 'Acute Nasopharyngitis (Common Cold)',
                'category' => 'Respiratory Infection',
                'description' => 'Viral infection of the upper respiratory tract',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => false,
                'typical_recovery_days' => 5,
            ],
            [
                'icd10_code' => 'J06.9',
                'diagnosis_name' => 'Upper Respiratory Infection (URI)',
                'category' => 'Respiratory Infection',
                'description' => 'Acute infection of the upper respiratory tract',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => false,
                'typical_recovery_days' => 7,
            ],
            [
                'icd10_code' => 'J18.9',
                'diagnosis_name' => 'Pneumonia',
                'category' => 'Respiratory Infection',
                'description' => 'Inflammation of the lungs, usually caused by infection',
                'severity' => 'Severe',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => true,
                'typical_recovery_days' => 14,
            ],
            [
                'icd10_code' => 'J20.9',
                'diagnosis_name' => 'Acute Bronchitis',
                'category' => 'Respiratory Infection',
                'description' => 'Inflammation of the bronchial tubes',
                'severity' => 'Moderate',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => false,
                'typical_recovery_days' => 10,
            ],
            
            // FEVER & INFECTIONS
            [
                'icd10_code' => 'R50.9',
                'diagnosis_name' => 'Fever of Unknown Origin',
                'category' => 'Fever & Infection',
                'description' => 'Elevated body temperature without clear cause',
                'severity' => 'Moderate',
                'is_chronic' => false,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => 3,
            ],
            [
                'icd10_code' => 'A09',
                'diagnosis_name' => 'Gastroenteritis (Food Poisoning)',
                'category' => 'Gastrointestinal',
                'description' => 'Inflammation of stomach and intestines',
                'severity' => 'Moderate',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => false,
                'typical_recovery_days' => 3,
            ],
            [
                'icd10_code' => 'A01.0',
                'diagnosis_name' => 'Typhoid Fever',
                'category' => 'Fever & Infection',
                'description' => 'Bacterial infection causing prolonged fever',
                'severity' => 'Severe',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => true,
                'typical_recovery_days' => 21,
            ],
            [
                'icd10_code' => 'A90',
                'diagnosis_name' => 'Dengue Fever',
                'category' => 'Fever & Infection',
                'description' => 'Mosquito-borne viral infection',
                'severity' => 'Severe',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => true,
                'typical_recovery_days' => 7,
            ],
            
            // CHRONIC CONDITIONS
            [
                'icd10_code' => 'E11.9',
                'diagnosis_name' => 'Type 2 Diabetes Mellitus',
                'category' => 'Endocrine',
                'description' => 'Chronic metabolic disorder affecting blood sugar regulation',
                'severity' => 'Moderate',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => null,
            ],
            [
                'icd10_code' => 'I10',
                'diagnosis_name' => 'Essential Hypertension',
                'category' => 'Cardiovascular',
                'description' => 'High blood pressure',
                'severity' => 'Moderate',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => null,
            ],
            [
                'icd10_code' => 'J45.9',
                'diagnosis_name' => 'Asthma',
                'category' => 'Respiratory',
                'description' => 'Chronic inflammatory airway disease',
                'severity' => 'Moderate',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => null,
            ],
            
            // GASTROINTESTINAL
            [
                'icd10_code' => 'K21.9',
                'diagnosis_name' => 'GERD (Acid Reflux)',
                'category' => 'Gastrointestinal',
                'description' => 'Gastroesophageal reflux disease',
                'severity' => 'Minor',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => false,
                'typical_recovery_days' => null,
            ],
            [
                'icd10_code' => 'K29.7',
                'diagnosis_name' => 'Gastritis',
                'category' => 'Gastrointestinal',
                'description' => 'Inflammation of the stomach lining',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => false,
                'requires_followup' => false,
                'typical_recovery_days' => 7,
            ],
            
            // MUSCULOSKELETAL
            [
                'icd10_code' => 'M54.5',
                'diagnosis_name' => 'Lower Back Pain',
                'category' => 'Musculoskeletal',
                'description' => 'Pain in the lower back region',
                'severity' => 'Moderate',
                'is_chronic' => false,
                'is_infectious' => false,
                'requires_followup' => false,
                'typical_recovery_days' => 14,
            ],
            [
                'icd10_code' => 'M79.3',
                'diagnosis_name' => 'Muscle Strain',
                'category' => 'Musculoskeletal',
                'description' => 'Injury to muscle fibers',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => false,
                'requires_followup' => false,
                'typical_recovery_days' => 7,
            ],
            
            // DERMATOLOGICAL
            [
                'icd10_code' => 'L30.9',
                'diagnosis_name' => 'Dermatitis',
                'category' => 'Dermatological',
                'description' => 'Skin inflammation',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => false,
                'requires_followup' => false,
                'typical_recovery_days' => 7,
            ],
            [
                'icd10_code' => 'B86',
                'diagnosis_name' => 'Fungal Skin Infection',
                'category' => 'Dermatological',
                'description' => 'Fungal infection of the skin',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => true,
                'requires_followup' => false,
                'typical_recovery_days' => 14,
            ],
            
            // MENTAL HEALTH
            [
                'icd10_code' => 'F41.9',
                'diagnosis_name' => 'Anxiety Disorder',
                'category' => 'Mental Health',
                'description' => 'Excessive worry and fear',
                'severity' => 'Moderate',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => null,
            ],
            [
                'icd10_code' => 'F32.9',
                'diagnosis_name' => 'Major Depressive Disorder',
                'category' => 'Mental Health',
                'description' => 'Persistent low mood and loss of interest',
                'severity' => 'Moderate',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => null,
            ],
            
            // HEADACHES
            [
                'icd10_code' => 'G43.9',
                'diagnosis_name' => 'Migraine',
                'category' => 'Neurological',
                'description' => 'Severe recurring headache',
                'severity' => 'Moderate',
                'is_chronic' => true,
                'is_infectious' => false,
                'requires_followup' => true,
                'typical_recovery_days' => null,
            ],
            [
                'icd10_code' => 'R51',
                'diagnosis_name' => 'Tension Headache',
                'category' => 'Neurological',
                'description' => 'Common headache caused by muscle tension',
                'severity' => 'Minor',
                'is_chronic' => false,
                'is_infectious' => false,
                'requires_followup' => false,
                'typical_recovery_days' => 1,
            ],
        ];

        foreach ($diagnoses as $diagnosis) {
            DiagnosisCode::create($diagnosis);
        }
    }
}