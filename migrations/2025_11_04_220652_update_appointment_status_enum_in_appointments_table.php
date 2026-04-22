<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Remove 'scheduled' status - use only 'confirmed' for new appointments
     */
    public function up(): void
    {
        // Update the ENUM to remove 'scheduled'
        DB::statement("ALTER TABLE appointments MODIFY status ENUM(
            'confirmed', 
            'checked_in',
            'vitals_pending',
            'vitals_recorded',
            'ready_for_doctor',
            'in_consultation',
            'completed',
            'cancelled',
            'no_show'
        ) DEFAULT 'confirmed'");
    }

    /**
     * Rollback: add 'scheduled' back
     */
    public function down(): void
    {
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
        ) DEFAULT 'confirmed'");
    }
};