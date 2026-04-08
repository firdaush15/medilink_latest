<?php
// database/migrations/2025_01_15_create_diagnoses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========================================
        // 1. DIAGNOSIS MASTER LIST (ICD-10 Based)
        // ========================================
        Schema::create('diagnosis_codes', function (Blueprint $table) {
            $table->id('diagnosis_code_id');
            $table->string('icd10_code', 10)->unique(); // e.g., "J11.1"
            $table->string('diagnosis_name'); // e.g., "Influenza A"
            $table->string('category'); // e.g., "Respiratory", "Cardiovascular"
            $table->text('description')->nullable();
            $table->enum('severity', ['Minor', 'Moderate', 'Severe', 'Critical'])->default('Moderate');
            $table->boolean('is_chronic')->default(false);
            $table->boolean('is_infectious')->default(false);
            $table->boolean('requires_followup')->default(false);
            $table->integer('typical_recovery_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
            $table->index('diagnosis_name');
        });

        // ========================================
        // 2. PATIENT DIAGNOSES (Link to Appointments)
        // ========================================
        Schema::create('patient_diagnoses', function (Blueprint $table) {
            $table->id('patient_diagnosis_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade');
            $table->foreignId('diagnosis_code_id')->constrained('diagnosis_codes', 'diagnosis_code_id')->onDelete('cascade');
            
            // Diagnosis details
            $table->enum('diagnosis_type', ['Primary', 'Secondary', 'Differential', 'Ruled Out'])->default('Primary');
            $table->enum('certainty', ['Confirmed', 'Probable', 'Suspected'])->default('Confirmed');
            $table->date('diagnosis_date');
            $table->text('clinical_notes')->nullable();
            $table->text('treatment_plan')->nullable();
            
            // Outcome tracking
            $table->enum('status', ['Active', 'Resolved', 'Chronic', 'Under Treatment'])->default('Active');
            $table->date('resolved_date')->nullable();
            $table->boolean('requires_referral')->default(false);
            $table->string('referral_to')->nullable();
            
            $table->timestamps();
            
            $table->index(['patient_id', 'diagnosis_date']);
            $table->index(['doctor_id', 'diagnosis_date']);
            $table->index(['diagnosis_code_id', 'diagnosis_date']);
            $table->index('status');
        });

        // ========================================
        // 3. DIAGNOSIS SYMPTOMS (For better tracking)
        // ========================================
        Schema::create('diagnosis_symptoms', function (Blueprint $table) {
            $table->id('symptom_id');
            $table->foreignId('patient_diagnosis_id')->constrained('patient_diagnoses', 'patient_diagnosis_id')->onDelete('cascade');
            $table->string('symptom_name'); // e.g., "Fever", "Cough", "Headache"
            $table->enum('severity', ['Mild', 'Moderate', 'Severe'])->default('Moderate');
            $table->integer('duration_days')->nullable();
            $table->timestamps();
            
            $table->index('patient_diagnosis_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnosis_symptoms');
        Schema::dropIfExists('patient_diagnoses');
        Schema::dropIfExists('diagnosis_codes');
    }
};