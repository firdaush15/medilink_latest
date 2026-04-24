<?php
// ============================================
// MIGRATION: database/migrations/xxxx_create_staff_alerts_table.php
// ============================================

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_alerts', function (Blueprint $table) {
            $table->id('alert_id');
            
            // Polymorphic sender (any staff member can send)
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->enum('sender_type', ['doctor', 'nurse', 'pharmacist', 'receptionist', 'admin', 'system']);
            
            // Polymorphic recipient (any staff member can receive)
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->enum('recipient_type', ['doctor', 'nurse', 'pharmacist', 'receptionist', 'admin']);
            
            // Optional references
            $table->foreignId('patient_id')->nullable()->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('medicine_id')->nullable()->constrained('medicine_inventory', 'medicine_id')->onDelete('cascade');
            $table->foreignId('prescription_id')->nullable()->constrained('prescriptions', 'prescription_id')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('cascade');
            
            // Alert content
            $table->string('alert_type');
            $table->enum('priority', ['Normal', 'High', 'Urgent', 'Critical'])->default('Normal');
            $table->string('alert_title');
            $table->text('alert_message');
            $table->string('action_url')->nullable();
            
            // Status tracking
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_acknowledged')->default(false);
            $table->timestamp('acknowledged_at')->nullable();
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['recipient_id', 'recipient_type', 'is_read'], 'idx_recipient_read');
            $table->index(['sender_id', 'sender_type'], 'idx_sender');
            $table->index(['priority', 'is_read'], 'idx_priority_read');
            $table->index(['created_at', 'priority'], 'idx_created_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_alerts');
    }
};