<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========================================
        // 1. NURSE-DOCTOR TEAM ASSIGNMENTS
        // ========================================
        Schema::create('nurse_doctor_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('nurse_id')->constrained('nurses', 'nurse_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade');
            
            $table->enum('assignment_type', ['primary', 'backup', 'floater'])->default('primary');
            $table->integer('priority_order')->default(1);
            
            $table->json('working_days')->nullable();
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->date('assigned_from')->default(now());
            $table->date('assigned_until')->nullable();
            
            $table->timestamps();
            
            $table->index(['doctor_id', 'is_active']);
            $table->index(['nurse_id', 'is_active']);
            $table->unique(['nurse_id', 'doctor_id'], 'unique_nurse_doctor_assignment');
        });

        // ========================================
        // 2. PATIENT-NURSE ASSIGNMENTS
        // ========================================
        Schema::create('patient_nurse_assignments', function (Blueprint $table) {
            $table->id('assignment_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('nurse_id')->constrained('nurses', 'nurse_id')->onDelete('cascade');
            
            // ✅ UPDATED: Add more assignment methods
    $table->enum('assignment_method', [
        'auto',           // Standard auto-assignment (on shift)
        'manual',         // Manually assigned by staff
        'transferred',    // Transferred from another nurse
        'fallback',       // Fallback to available nurse (on shift)
        'fallback_no_shift', // Emergency fallback (nurse not on shift)
        'emergency'       // Emergency assignment (no nurses available)
    ])->default('auto');
            $table->timestamp('assigned_at');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->enum('status', ['accepted', 'in_progress', 'completed', 'transferred'])->default('accepted');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            
            $table->foreignId('transferred_to')->nullable()->constrained('nurses', 'nurse_id')->onDelete('set null');
            $table->text('transfer_reason')->nullable();
            $table->timestamp('transferred_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['nurse_id', 'status', 'assigned_at']);
            $table->index(['appointment_id', 'status']);
        });

        // ========================================
        // 3. NURSE WORKLOAD TRACKING
        // ========================================
        Schema::create('nurse_workload_tracking', function (Blueprint $table) {
            $table->id('tracking_id');
            $table->foreignId('nurse_id')->unique()->constrained('nurses', 'nurse_id')->onDelete('cascade');
            
            $table->integer('current_patients')->default(0);
            $table->integer('pending_vitals')->default(0);
            $table->integer('total_today')->default(0);
            
            $table->integer('max_capacity')->default(5);
            $table->boolean('is_available')->default(true);
            $table->enum('current_status', ['available', 'busy', 'on_break', 'off_duty'])->default('available');
            
            $table->integer('avg_completion_time_minutes')->default(0);
            $table->decimal('efficiency_score', 5, 2)->default(100.00);
            
            $table->timestamp('last_assignment_at')->nullable();
            $table->timestamp('last_completed_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['is_available', 'current_patients']);
            $table->index('current_status');
        });

        // ========================================
        // 4. ASSIGNMENT LOGS
        // ========================================
        Schema::create('nurse_assignment_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            $table->foreignId('nurse_id')->constrained('nurses', 'nurse_id')->onDelete('cascade');
            
            $table->enum('action', [
                'auto_assigned',
                'manually_assigned',
                'accepted',
                'declined',
                'started',
                'completed',
                'transferred',
                'timeout'
            ]);
            
            $table->text('details')->nullable();
            $table->integer('workload_at_time')->nullable();
            $table->timestamp('action_at');
            $table->string('ip_address', 45)->nullable();
            
            $table->index(['nurse_id', 'action_at']);
            $table->index(['appointment_id', 'action']);
        });

        // ========================================
        // 5. ADD FIELDS TO APPOINTMENTS TABLE
        // ========================================
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('assigned_nurse_id')
                ->nullable()
                ->after('vitals_recorded_by')
                ->constrained('nurses', 'nurse_id')
                ->onDelete('set null');
            
            $table->timestamp('nurse_assigned_at')
                ->nullable()
                ->after('assigned_nurse_id');
            
            $table->timestamp('nurse_accepted_at')
                ->nullable()
                ->after('nurse_assigned_at');
            
            $table->boolean('nurse_notified')
                ->default(false)
                ->after('nurse_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['assigned_nurse_id']);
            $table->dropColumn([
                'assigned_nurse_id',
                'nurse_assigned_at',
                'nurse_accepted_at',
                'nurse_notified'
            ]);
        });
        
        Schema::dropIfExists('nurse_assignment_logs');
        Schema::dropIfExists('nurse_workload_tracking');
        Schema::dropIfExists('patient_nurse_assignments');
        Schema::dropIfExists('nurse_doctor_assignments');
    }
};