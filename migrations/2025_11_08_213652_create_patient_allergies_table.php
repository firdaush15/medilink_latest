<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_allergies', function (Blueprint $table) {
            $table->id('allergy_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            
            // Allergy details
            $table->enum('allergy_type', [
                'Drug/Medication',
                'Food',
                'Environmental',
                'Other'
            ])->default('Drug/Medication');
            
            $table->string('allergen_name'); // e.g., "Penicillin", "Aspirin", "Peanuts"
            $table->enum('severity', ['Mild', 'Moderate', 'Severe', 'Life-threatening'])->default('Moderate');
            
            // Reaction details
            $table->text('reaction_description')->nullable(); // e.g., "Rash", "Difficulty breathing", "Anaphylaxis"
            $table->date('onset_date')->nullable(); // When was this allergy first discovered
            
            // Status
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            
            // Audit trail
            $table->foreignId('recorded_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['patient_id', 'is_active']);
            $table->index(['allergen_name', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_allergies');
    }
};