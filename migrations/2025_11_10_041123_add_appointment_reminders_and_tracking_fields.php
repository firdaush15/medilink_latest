<?php

// Migration 1: Add appointment reminders table
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointment_reminders', function (Blueprint $table) {
            $table->id('reminder_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            
            // Reminder details
            $table->enum('reminder_type', ['sms', 'email', 'whatsapp'])->default('sms');
            $table->timestamp('scheduled_for'); // When to send
            $table->timestamp('sent_at')->nullable(); // When actually sent
            
            // Status tracking
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('failure_reason')->nullable();
            
            // Content
            $table->text('message_content');
            $table->string('recipient'); // Phone or email
            
            $table->timestamps();
            
            $table->index(['appointment_id', 'status']);
            $table->index('scheduled_for');
        });

        // Migration 2: Add visit history tracking fields
        Schema::table('appointments', function (Blueprint $table) {
            $table->timestamp('checked_out_at')->nullable()->after('consultation_ended_at');
            $table->foreignId('checked_out_by')->nullable()->after('checked_out_at')->constrained('users')->onDelete('set null');
            $table->boolean('payment_collected')->default(false)->after('checked_out_by');
            $table->decimal('payment_amount', 10, 2)->nullable()->after('payment_collected');
            $table->text('checkout_notes')->nullable()->after('payment_amount');
        });

        // Migration 3: Add no-show tracking
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('no_show_count')->default(0)->after('emergency_contact');
            $table->integer('late_arrival_count')->default(0)->after('no_show_count');
            $table->boolean('is_flagged')->default(false)->after('late_arrival_count');
            $table->text('flag_reason')->nullable()->after('is_flagged');
            $table->timestamp('last_visit_date')->nullable()->after('flag_reason');
        });

        // Migration 4: Add walk-in tracking
        Schema::table('appointments', function (Blueprint $table) {
            $table->boolean('is_walk_in')->default(false)->after('status');
            $table->enum('urgency_level', ['routine', 'urgent', 'emergency'])->default('routine')->after('is_walk_in');
            $table->text('walk_in_notes')->nullable()->after('urgency_level');
        });

        // Migration 5: Create patient search history
        Schema::create('patient_search_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Receptionist
            $table->string('search_query');
            $table->json('filters')->nullable();
            $table->integer('results_count');
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_search_history');
        Schema::dropIfExists('appointment_reminders');
        
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['checked_out_by']);
            $table->dropColumn([
                'checked_out_at',
                'checked_out_by',
                'payment_collected',
                'payment_amount',
                'checkout_notes',
                'is_walk_in',
                'urgency_level',
                'walk_in_notes'
            ]);
        });
        
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'no_show_count',
                'late_arrival_count',
                'is_flagged',
                'flag_reason',
                'last_visit_date'
            ]);
        });
    }
};