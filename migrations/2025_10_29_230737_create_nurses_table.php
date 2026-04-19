<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nurses', function (Blueprint $table) {
            $table->id('nurse_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone_number');
            $table->string('profile_photo')->nullable();
            $table->string('department')->nullable(); // e.g., Emergency, ICU, General Ward
            $table->string('shift')->nullable(); // e.g., Morning, Evening, Night
            $table->enum('availability_status', ['Available', 'On Duty', 'On Leave', 'Unavailable'])->default('Available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nurses');
    }
};