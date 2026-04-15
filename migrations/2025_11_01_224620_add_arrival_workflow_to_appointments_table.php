<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add proper workflow tracking to appointments table
     * This enables: Scheduled → Arrived → Vitals Done → Ready for Doctor → Completed
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Step 1: Patient arrival (marked by Receptionist)
            $table->enum('arrival_status', ['not_arrived', 'arrived', 'vitals_done', 'ready_for_doctor'])
                ->default('not_arrived')
                ->after('status')
                ->comment('Tracks patient flow: not_arrived → arrived (receptionist) → vitals_done (nurse) → ready_for_doctor (nurse)');
            
            // Timestamp when receptionist marks patient as arrived
            $table->timestamp('arrived_at')
                ->nullable()
                ->after('arrival_status')
                ->comment('When receptionist checked patient in');
            
            // Track which receptionist checked in the patient
            $table->foreignId('checked_in_by')
                ->nullable()
                ->after('arrived_at')
                ->constrained('users')
                ->onDelete('set null')
                ->comment('User ID of receptionist who marked arrival');
            
            // Timestamp when nurse completes vitals
            $table->timestamp('vitals_completed_at')
                ->nullable()
                ->after('checked_in_by')
                ->comment('When nurse finished recording vitals');
            
            // Track which nurse recorded vitals
            $table->foreignId('vitals_recorded_by')
                ->nullable()
                ->after('vitals_completed_at')
                ->constrained('nurses', 'nurse_id')
                ->onDelete('set null')
                ->comment('Nurse who recorded vitals');
            
            // Timestamp when doctor started consultation
            $table->timestamp('consultation_started_at')
                ->nullable()
                ->after('vitals_recorded_by')
                ->comment('When doctor began seeing patient');
            
            // Add index for performance
            $table->index(['appointment_date', 'arrival_status'], 'idx_appointment_workflow');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop index
            $table->dropIndex('idx_appointment_workflow');
            
            // Drop foreign key constraints
            $table->dropForeign(['checked_in_by']);
            $table->dropForeign(['vitals_recorded_by']);
            
            // Drop columns
            $table->dropColumn([
                'arrival_status',
                'arrived_at',
                'checked_in_by',
                'vitals_completed_at',
                'vitals_recorded_by',
                'consultation_started_at'
            ]);
        });
    }
};