<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========================================
        // 2. NURSE REPORTS TABLE
        // ========================================
        Schema::create('nurse_reports', function (Blueprint $table) {
            $table->id('report_id');
            $table->foreignId('nurse_id')->constrained('nurses', 'nurse_id')->onDelete('cascade');
            $table->foreignId('patient_id')->nullable()->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->string('report_type')->comment('incident, progress, discharge, medication, wound, pain');
            $table->string('report_number')->unique()->comment('Auto-generated: INC-2024-0001');
            $table->timestamp('event_datetime');
            $table->string('location');
            $table->enum('severity', ['minor', 'moderate', 'major', 'critical'])->nullable();
            $table->text('description');
            $table->text('actions_taken')->nullable();
            $table->text('patient_response')->nullable();
            $table->boolean('followup_required')->default(false);
            $table->boolean('physician_notified')->default(false);
            $table->text('additional_notes')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->timestamps();
            
            $table->index(['nurse_id', 'report_type']);
            $table->index('event_datetime');
            $table->index('report_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurse_reports');
        Schema::dropIfExists('handover_reports');
    }
};