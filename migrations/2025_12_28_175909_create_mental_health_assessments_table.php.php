<?php
// database/migrations/xxxx_create_mental_health_assessments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Main assessments table
        Schema::create('mental_health_assessments', function (Blueprint $table) {
            $table->id('assessment_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->enum('assessment_type', ['mental_health', 'diabetes_risk', 'heart_health'])->default('mental_health');
            $table->integer('total_score');
            $table->enum('risk_level', ['good', 'mild', 'moderate', 'severe']);
            $table->text('recommendations')->nullable();
            $table->timestamp('assessment_date');
            
            // Doctor review (optional)
            $table->foreignId('reviewed_by_doctor_id')->nullable()->constrained('doctors', 'doctor_id')->onDelete('set null');
            $table->text('doctor_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            
            // Privacy controls
            $table->boolean('is_shared_with_doctor')->default(true);
            
            $table->timestamps();
            
            $table->index(['patient_id', 'assessment_date']);
            $table->index('risk_level');
        });

        // Individual answers table
        Schema::create('assessment_answers', function (Blueprint $table) {
            $table->id('answer_id');
            $table->foreignId('assessment_id')->constrained('mental_health_assessments', 'assessment_id')->onDelete('cascade');
            $table->integer('question_number');
            $table->text('question_text');
            $table->string('answer_option', 50); // "Never", "Sometimes", "Often", "Always"
            $table->integer('score_value');
            $table->timestamps();
            
            $table->index('assessment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answers');
        Schema::dropIfExists('mental_health_assessments');
    }
};