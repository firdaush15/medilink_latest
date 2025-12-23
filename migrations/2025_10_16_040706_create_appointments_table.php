<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade');
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->enum('status', ['confirmed', 'completed', 'cancelled'])->default('confirmed');
            $table->text('reason')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
