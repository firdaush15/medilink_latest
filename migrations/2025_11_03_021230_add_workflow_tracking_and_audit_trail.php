<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enhanced workflow tracking with audit trail
     */
    public function up(): void
    {
        // ========================================
        // 1. ADD NEW FIELDS TO APPOINTMENTS TABLE
        // ========================================
        Schema::table('appointments', function (Blueprint $table) {
            // ✅ Explicit workflow stage (replaces ambiguous arrival_status)
            $table->enum('workflow_stage', [
                'scheduled',
                'arrived',
                'vitals_in_progress',
                'vitals_recorded',
                'ready_for_doctor',
                'with_doctor',
                'completed',
                'cancelled'
            ])->default('scheduled')->after('status');

            // ✅ Vitals verification timestamp
            $table->timestamp('vitals_verified_at')
                ->nullable()
                ->after('vitals_recorded_by')
                ->comment('When nurse verified vitals before marking ready');

            // ✅ Consultation end time
            $table->timestamp('consultation_ended_at')
                ->nullable()
                ->after('consultation_started_at')
                ->comment('When doctor finished consultation');

            // ✅ Critical vitals alert tracking
            $table->boolean('critical_vitals_alert_sent')
                ->default(false)
                ->after('vitals_verified_at')
                ->comment('Whether critical vitals alert was sent to doctor');

            // Add index for workflow queries
            $table->index(['appointment_date', 'workflow_stage'], 'idx_workflow_status');
        });

        // ========================================
        // 2. CREATE WORKFLOW AUDIT LOG TABLE
        // ========================================
        Schema::create('appointment_workflow_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('appointment_id')
                ->constrained('appointments', 'appointment_id')
                ->onDelete('cascade');

            // Stage transition
            $table->string('from_stage', 50);
            $table->string('to_stage', 50);

            // Who made the change
            $table->foreignId('changed_by_id')
                ->constrained('users', 'id')
                ->onDelete('cascade');
            
            $table->enum('changed_by_type', ['user', 'nurse', 'doctor', 'system'])
                ->comment('Type of user who made the change');

            // Change details
            $table->text('notes')->nullable();
            $table->timestamp('timestamp');
            $table->string('ip_address', 45)->nullable();

            // Indexes
            $table->index(['appointment_id', 'timestamp']);
            $table->index('changed_by_id');
        });

        // ========================================
        // 4. CREATE NURSE ALERTS TABLE (if not exists)
        // ========================================
        if (!Schema::hasTable('nurse_alerts')) {
            Schema::create('nurse_alerts', function (Blueprint $table) {
                $table->id('alert_id');
                $table->foreignId('nurse_id')->nullable()
                    ->constrained('nurses', 'nurse_id')
                    ->onDelete('cascade');
                
                $table->foreignId('patient_id')->nullable()
                    ->constrained('patients', 'patient_id')
                    ->onDelete('cascade');

                $table->foreignId('appointment_id')->nullable()
                    ->constrained('appointments', 'appointment_id')
                    ->onDelete('cascade');

                $table->enum('alert_type', [
                    'Critical Vitals',
                    'Medication Due',
                    'Appointment Reminder',
                    'Lab Results Ready',
                    'Doctor Request',
                    'Patient Call',
                    'Emergency',
                    'System'
                ]);

                $table->string('alert_title');
                $table->text('alert_message');
                $table->enum('priority', ['Normal', 'High', 'Urgent', 'Critical'])->default('Normal');

                $table->boolean('is_read')->default(false);
                $table->timestamp('read_at')->nullable();
                $table->boolean('is_acknowledged')->default(false);
                $table->timestamp('acknowledged_at')->nullable();

                $table->string('action_url')->nullable();
                $table->timestamps();

                $table->index(['nurse_id', 'is_read', 'created_at']);
                $table->index(['priority', 'is_read']);
            });
        }

        // ========================================
        // 5. CREATE SYSTEM NOTIFICATIONS TABLE
        // ========================================
        Schema::create('system_notifications', function (Blueprint $table) {
            $table->id('notification_id');
            
            // Recipients
            $table->foreignId('user_id')
                ->constrained('users', 'id')
                ->onDelete('cascade');
            
            $table->enum('user_role', ['admin', 'doctor', 'nurse', 'receptionist']);

            // Notification content
            $table->string('notification_type', 50);
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Additional data

            // Status
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_actionable')->default(false);
            $table->string('action_url')->nullable();
            $table->boolean('action_completed')->default(false);

            // Priority
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'is_read', 'created_at']);
            $table->index(['notification_type', 'user_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_notifications');
        Schema::dropIfExists('appointment_handoffs');
        Schema::dropIfExists('appointment_workflow_logs');
        
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_workflow_status');
            $table->dropColumn([
                'workflow_stage',
                'vitals_verified_at',
                'consultation_ended_at',
                'critical_vitals_alert_sent',
            ]);
        });
    }
};