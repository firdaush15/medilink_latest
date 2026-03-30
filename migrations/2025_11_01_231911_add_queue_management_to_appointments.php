<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Complete Queue Management System
     * Handles appointment time priority + arrival time fairness
     */
    public function up(): void
    {
        // ========================================
        // 1. Add Queue Fields to Appointments
        // ========================================
        Schema::table('appointments', function (Blueprint $table) {
            // Queue number assigned when patient arrives
            $table->integer('queue_number')
                ->nullable()
                ->after('arrival_status')
                ->comment('Dynamic queue position (recalculated on each check-in)');
            
            // Priority score for queue ordering
            $table->integer('queue_priority_score')
                ->nullable()
                ->after('queue_number')
                ->comment('Calculated score: appointment_time + arrival_penalties');
            
            // Track if patient is late
            $table->boolean('is_late')
                ->default(false)
                ->after('queue_priority_score')
                ->comment('True if arrived after appointment_time + grace_period');
            
            // Late arrival penalty minutes
            $table->integer('late_penalty_minutes')
                ->default(0)
                ->after('is_late')
                ->comment('Minutes late = pushes queue position back');
            
            // Expected call time (estimated)
            $table->timestamp('estimated_call_time')
                ->nullable()
                ->after('late_penalty_minutes')
                ->comment('Estimated time nurse will call patient');
            
            // Actual call time
            $table->timestamp('called_at')
                ->nullable()
                ->after('estimated_call_time')
                ->comment('When nurse called patient from queue');
            
            // Add index for queue queries
            $table->index(['appointment_date', 'queue_number'], 'idx_queue_order');
            $table->index(['appointment_date', 'queue_priority_score'], 'idx_queue_priority');
        });

        // ========================================
        // 2. Create Queue Management Settings Table
        // ========================================
        Schema::create('queue_settings', function (Blueprint $table) {
            $table->id('setting_id');
            
            // Grace period settings
            $table->integer('grace_period_minutes')
                ->default(15)
                ->comment('Minutes after appointment_time before marked late');
            
            $table->integer('late_penalty_per_minute')
                ->default(2)
                ->comment('Queue penalty multiplier for each minute late');
            
            // Queue calculation weights
            $table->integer('appointment_time_weight')
                ->default(100)
                ->comment('Priority weight for scheduled appointment time');
            
            $table->integer('arrival_time_weight')
                ->default(10)
                ->comment('Secondary weight for check-in time');
            
            // Walk-in handling
            $table->boolean('allow_walk_ins')
                ->default(false)
                ->comment('Allow patients without appointments');
            
            $table->integer('walk_in_penalty_minutes')
                ->default(120)
                ->comment('Walk-ins treated as X minutes late');
            
            // Average consultation time (for estimates)
            $table->integer('avg_consultation_minutes')
                ->default(20)
                ->comment('Average time doctor spends per patient');
            
            $table->timestamps();
        });

        // Insert default settings
        DB::table('queue_settings')->insert([
            'grace_period_minutes' => 15,
            'late_penalty_per_minute' => 2,
            'appointment_time_weight' => 100,
            'arrival_time_weight' => 10,
            'allow_walk_ins' => false,
            'walk_in_penalty_minutes' => 120,
            'avg_consultation_minutes' => 20,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ========================================
        // 3. Create Queue History Log
        // ========================================
        Schema::create('queue_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('appointment_id')->constrained('appointments', 'appointment_id')->onDelete('cascade');
            $table->integer('old_queue_number')->nullable();
            $table->integer('new_queue_number');
            $table->integer('priority_score');
            $table->string('reason')->comment('Why queue changed: checked_in, recalculated, late_arrival, etc');
            $table->timestamp('changed_at');
            
            $table->index(['appointment_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_history');
        Schema::dropIfExists('queue_settings');
        
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_queue_order');
            $table->dropIndex('idx_queue_priority');
            
            $table->dropColumn([
                'queue_number',
                'queue_priority_score',
                'is_late',
                'late_penalty_minutes',
                'estimated_call_time',
                'called_at',
            ]);
        });
    }
};