<?php
// database/migrations/xxxx_create_admins_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id('admin_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
            // Contact Information
            $table->string('phone_number');
            $table->string('profile_photo')->nullable();
            
            // Admin Details
            $table->string('employee_id')->unique()->nullable();
            $table->enum('admin_level', ['Super Admin', 'Admin', 'System Admin'])->default('Admin');
            $table->string('department')->default('Administration');
            $table->date('hire_date')->nullable();
            
            // Permissions & Access
            $table->json('permissions')->nullable()->comment('Specific admin permissions');
            $table->boolean('can_manage_staff')->default(true);
            $table->boolean('can_manage_inventory')->default(true);
            $table->boolean('can_manage_billing')->default(true);
            $table->boolean('can_view_reports')->default(true);
            $table->boolean('can_manage_system_settings')->default(false); // Only for Super Admin
            
            // Status
            $table->enum('status', ['Active', 'On Leave', 'Inactive'])->default('Active');
            
            // Activity Tracking
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->integer('total_logins')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index('admin_level');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};