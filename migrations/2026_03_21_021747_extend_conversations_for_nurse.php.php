<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add missing FK columns for nurse conversations
        Schema::table('conversations', function (Blueprint $table) {
            // receptionist_id — for nurse↔receptionist threads
            $table->foreignId('receptionist_id')
                ->nullable()
                ->after('nurse_id')
                ->constrained('receptionists', 'receptionist_id')
                ->onDelete('cascade');

            // pharmacist_id — for nurse↔pharmacist threads
            $table->foreignId('pharmacist_id')
                ->nullable()
                ->after('receptionist_id')
                ->constrained('pharmacists', 'pharmacist_id')
                ->onDelete('cascade');
        });

        // Step 2: Extend conversation_type ENUM with all nurse-initiated types
        // Also add admin_nurse so admin can initiate to nurse (and vice versa)
        DB::statement("
            ALTER TABLE conversations
            MODIFY COLUMN conversation_type
            ENUM(
                'doctor_admin',
                'doctor_patient',
                'doctor_nurse',
                'nurse_admin',
                'nurse_doctor',
                'nurse_receptionist',
                'nurse_pharmacist'
            )
            NOT NULL DEFAULT 'doctor_admin'
        ");
    }

    public function down(): void
    {
        // Revert ENUM
        DB::statement("
            ALTER TABLE conversations
            MODIFY COLUMN conversation_type
            ENUM('doctor_admin', 'doctor_patient', 'doctor_nurse')
            NOT NULL DEFAULT 'doctor_admin'
        ");

        // Drop added columns
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['receptionist_id']);
            $table->dropForeign(['pharmacist_id']);
            $table->dropColumn(['receptionist_id', 'pharmacist_id']);
        });
    }
};