<?php

// Migration 3: message_templates (for common messages)
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id('template_id');
            $table->string('template_name'); // "Appointment Reminder", "Lab Results Ready"
            $table->enum('template_type', ['appointment_reminder', 'prescription_ready', 'lab_results', 'follow_up', 'custom']);
            $table->text('template_content'); // Message template with placeholders like {patient_name}
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};