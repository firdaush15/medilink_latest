<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // Medical history fields
            $table->text('chronic_conditions')->nullable()->after('emergency_contact')
                ->comment('Comma-separated list or JSON of chronic conditions');
            
            $table->text('current_medications')->nullable()
                ->comment('Current medications patient is taking');
            
            $table->text('past_surgeries')->nullable()
                ->comment('History of surgical procedures');
            
            $table->enum('blood_type', ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'Unknown'])
                ->default('Unknown');
            
            $table->boolean('smoking')->default(false);
            $table->boolean('alcohol')->default(false);
            
            $table->text('family_medical_history')->nullable()
                ->comment('Relevant family medical history');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn([
                'chronic_conditions',
                'current_medications',
                'past_surgeries',
                'blood_type',
                'smoking',
                'alcohol',
                'family_medical_history'
            ]);
        });
    }
};
