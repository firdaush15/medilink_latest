<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id('patient_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // ✅ Added IC Number (Unique & Indexed)
            $table->string('ic_number', 20)->unique()->index(); 
            $table->string('phone_number')->nullable(); // Can be nullable if we rely on IC
            $table->enum('gender', ['Male', 'Female', 'Other']);
            $table->date('date_of_birth');
            $table->string('emergency_contact')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};