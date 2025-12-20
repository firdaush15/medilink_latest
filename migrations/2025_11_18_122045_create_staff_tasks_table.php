<?php
// ============================================
// STEP 1: Create staff_tasks table
// database/migrations/2024_11_18_create_staff_tasks_table.php
// ============================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('staff_tasks', function (Blueprint $table) {
            $table->id('task_id');
            
            // Assignment (polymorphic - any staff to any staff)
            $table->foreignId('assigned_by_id')->constrained('users')->onDelete('cascade');
            $table->enum('assigned_by_type', ['doctor', 'nurse', 'pharmacist', 'receptionist', 'admin']);
            $table->foreignId('assigned_to_id')->constrained('users')->onDelete('cascade');
            $table->enum('assigned_to_type', ['nurse', 'pharmacist', 'receptionist', 'doctor', 'admin']);
            
            // Related entities (optional)
            $table->foreignId('patient_id')->nullable()->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('set null');
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions', 'prescription_id')->onDelete('set null');
            $table->foreignId('medicine_id')->nullable()->constrained('medicine_inventory', 'medicine_id')->onDelete('set null');
            
            // Task details
            $table->string('task_type'); // 'Vital Signs Check', 'Prepare Patient', 'Verify Prescription', etc.
            $table->enum('priority', ['Low', 'Normal', 'High', 'Urgent', 'Critical'])->default('Normal');
            $table->string('task_title');
            $table->text('task_description')->nullable();
            $table->string('action_url')->nullable();
            
            // Scheduling
            $table->timestamp('due_at')->nullable();
            
            // Status tracking
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            // Results & notes
            $table->text('completion_notes')->nullable();
            $table->json('task_data')->nullable(); // Flexible storage: vitals, measurements, etc.
            
            $table->timestamps();
            $table->softDeletes();
            
            // Performance indexes
            $table->index(['assigned_to_id', 'assigned_to_type', 'status'], 'idx_recipient_status');
            $table->index(['assigned_by_id', 'status'], 'idx_sender_status');
            $table->index(['patient_id', 'status'], 'idx_patient_status');
            $table->index(['appointment_id', 'status'], 'idx_appointment_status');
            $table->index('due_at');
            $table->index(['status', 'priority'], 'idx_status_priority');
        });
    }

    public function down()
    {
        Schema::dropIfExists('staff_tasks');
    }
};