<?php
// database/migrations/xxxx_create_shift_templates_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shift_templates', function (Blueprint $table) {
            $table->id('template_id');
            $table->string('template_name'); // "Morning Shift", "Afternoon Shift"
            $table->time('start_time'); // 08:00:00
            $table->time('end_time');   // 14:00:00
            $table->integer('duration_hours'); // 6
            $table->string('color_code')->default('#4CAF50'); // For calendar display
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shift_templates');
    }
};