<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_ratings', function (Blueprint $table) {
            $table->id('rating_id');
            
            // Relationships
            $table->foreignId('doctor_id')->constrained('doctors', 'doctor_id')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            $table->foreignId('appointment_id')->nullable()->constrained('appointments', 'appointment_id')->onDelete('set null');
            
            // Rating info
            $table->unsignedTinyInteger('rating')->comment('1 to 5'); // star rating
            $table->text('comment')->nullable(); // optional feedback message
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_ratings');
    }
};
