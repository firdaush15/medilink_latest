<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE conversations
            MODIFY COLUMN conversation_type
            ENUM(
                'doctor_admin',
                'doctor_patient',
                'doctor_nurse',
                'doctor_receptionist',
                'doctor_pharmacist',
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
};