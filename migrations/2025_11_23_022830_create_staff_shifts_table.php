<?php
// database/migrations/xxxx_create_staff_shifts_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_shifts', function (Blueprint $table) {
            $table->id('shift_id');
            
            // Polymorphic staff reference
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('staff_role', ['doctor', 'nurse', 'pharmacist', 'receptionist', 'admin']);
            
            // Shift details
            $table->foreignId('template_id')->nullable()->constrained('shift_templates', 'template_id')->onDelete('set null');
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            
            // Status
            $table->enum('status', ['scheduled', 'checked_in', 'checked_out', 'absent', 'cancelled'])->default('scheduled');
            $table->timestamp('actual_check_in')->nullable();
            $table->timestamp('actual_check_out')->nullable();
            
            // Metadata
            $table->text('notes')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->boolean('is_recurring')->default(false);
            $table->string('recurrence_pattern')->nullable(); // "weekly", "monthly"
            
            $table->timestamps();
            
            // Indexes
            $table->index(['shift_date', 'staff_role']);
            $table->index(['user_id', 'shift_date']);
            $table->unique(['user_id', 'shift_date', 'start_time'], 'unique_staff_shift');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_shifts');
    }
};