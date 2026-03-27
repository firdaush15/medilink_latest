<?php

// Migration 1: conversations table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id('conversation_id');
            
            // Participants (polymorphic - can be doctor-patient or doctor-admin)
            $table->enum('conversation_type', ['doctor_admin', 'doctor_patient'])->default('doctor_admin');
            
            // For doctor-admin conversations
            $table->foreignId('doctor_id')->nullable()->constrained('doctors', 'doctor_id')->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users', 'id')->onDelete('cascade');
            
            // For doctor-patient conversations
            $table->foreignId('patient_id')->nullable()->constrained('patients', 'patient_id')->onDelete('cascade');
            
            // Related to specific appointment (optional)
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('set null');
            
            // Conversation metadata
            $table->string('subject')->nullable(); // e.g., "Appointment Request", "Lab Results Query"
            $table->enum('status', ['active', 'archived', 'closed'])->default('active');
            $table->timestamp('last_message_at')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};