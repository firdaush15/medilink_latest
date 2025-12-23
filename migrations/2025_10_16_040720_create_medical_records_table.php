<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade');
            $table->date('record_date');
            $table->string('record_type'); // e.g., blood_type, xray_result
            $table->string('record_title'); // optional short title
            $table->text('description');
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
