<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Drop redundant columns
            $table->dropColumn(['workflow_stage', 'arrival_status']);
        });

        // Modify ENUM type for status column
        DB::statement("ALTER TABLE appointments MODIFY status ENUM(
            'scheduled',
            'confirmed', 
            'checked_in',
            'vitals_pending',
            'vitals_recorded',
            'ready_for_doctor',
            'in_consultation',
            'completed',
            'cancelled',
            'no_show'
        ) DEFAULT 'scheduled'");
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Re-add dropped columns
            $table->enum('workflow_stage', [
                'scheduled',
                'arrived',
                'vitals_in_progress',
                'vitals_done',
                'consultation',
                'completed'
            ])->default('scheduled');

            $table->enum('arrival_status', [
                'not_arrived',
                'arrived',
                'vitals_done',
                'ready_for_doctor'
            ])->default('not_arrived');

            // Revert status ENUM to original
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('confirmed', 'completed', 'cancelled') DEFAULT 'confirmed'");
        });
    }
};
