<?php
// database/migrations/2026_04_12_create_disease_prediction_histories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disease_prediction_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')
                ->constrained('patients', 'patient_id')
                ->onDelete('cascade');

            // Inputs
            $table->json('symptoms')->comment('Array of symptom strings');

            // Output
            $table->string('prediction');               // top predicted disease
            $table->decimal('confidence', 5, 2);        // e.g. 87.50
            $table->json('top_predictions');            // [{disease, confidence}, ...]
            $table->enum('risk_level', ['low', 'medium', 'high']);
            $table->text('recommendation');

            $table->timestamps();

            $table->index(['patient_id', 'created_at']);
            $table->index('risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disease_prediction_histories');
    }
};