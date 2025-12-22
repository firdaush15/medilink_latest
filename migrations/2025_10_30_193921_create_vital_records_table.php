<?php
// Migration: create_vital_records_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_records', function (Blueprint $table) {
            $table->id('vital_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('nurse_id')->constrained('nurses', 'nurse_id')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('set null');
            
            // Vital signs
            $table->decimal('temperature', 4, 1)->nullable()->comment('Celsius');
            $table->string('blood_pressure')->nullable()->comment('e.g., 120/80');
            $table->integer('heart_rate')->nullable()->comment('BPM');
            $table->integer('respiratory_rate')->nullable()->comment('breaths per minute');
            $table->integer('oxygen_saturation')->nullable()->comment('SpO2 percentage');
            $table->decimal('weight', 5, 2)->nullable()->comment('kg');
            $table->decimal('height', 5, 2)->nullable()->comment('cm');
            
            // Metadata
            $table->timestamp('recorded_at');
            $table->text('notes')->nullable();
            $table->boolean('is_critical')->default(false)->comment('Flagged if vitals outside normal range');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['patient_id', 'recorded_at']);
            $table->index('is_critical');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_records');
    }
};