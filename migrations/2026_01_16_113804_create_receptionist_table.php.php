<?php
// database/migrations/xxxx_create_receptionists_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receptionists', function (Blueprint $table) {
            $table->id('receptionist_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Contact Information
            $table->string('phone_number');
            $table->string('profile_photo')->nullable();
            
            // Work Details
            $table->string('employee_id')->unique()->nullable();
            $table->string('department')->default('Front Desk');
            $table->enum('shift', ['Morning', 'Afternoon', 'Evening', 'Night', 'Rotating'])->default('Morning');
            $table->date('hire_date')->nullable();
            
            // Status
            $table->enum('availability_status', ['Available', 'On Break', 'On Leave', 'Unavailable'])->default('Available');
            
            // Performance tracking (optional)
            $table->integer('patients_checked_in_today')->default(0);
            $table->integer('total_patients_checked_in')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('availability_status');
            $table->index(['shift', 'availability_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receptionists');
    }
};