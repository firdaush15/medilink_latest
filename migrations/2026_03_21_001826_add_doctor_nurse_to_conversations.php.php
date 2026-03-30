<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Add nurse_id column
        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('nurse_id')
                ->nullable()
                ->after('patient_id')
                ->constrained('nurses', 'nurse_id')
                ->onDelete('cascade');
        });

        // Step 2: Extend the conversation_type ENUM to include doctor_nurse
        // MariaDB/MySQL requires re-declaring the full ENUM to add a value
        DB::statement("
            ALTER TABLE conversations
            MODIFY COLUMN conversation_type
            ENUM('doctor_admin', 'doctor_patient', 'doctor_nurse')
            NOT NULL DEFAULT 'doctor_admin'
        ");
    }

    public function down(): void
    {
        // Revert ENUM first (remove doctor_nurse)
        DB::statement("
            ALTER TABLE conversations
            MODIFY COLUMN conversation_type
            ENUM('doctor_admin', 'doctor_patient')
            NOT NULL DEFAULT 'doctor_admin'
        ");

        // Drop nurse_id column
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['nurse_id']);
            $table->dropColumn('nurse_id');
        });
    }
};