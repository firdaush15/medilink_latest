<?php
// database/migrations/xxxx_create_leave_requests_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id('leave_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('staff_role', ['doctor', 'nurse', 'pharmacist', 'receptionist']);
            
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('leave_type', ['Annual Leave', 'Sick Leave', 'Emergency Leave', 'Unpaid Leave']);
            $table->text('reason')->nullable();
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};