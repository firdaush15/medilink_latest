<?php
// database/migrations/xxxx_create_pharmacists_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pharmacists', function (Blueprint $table) {
            $table->id('pharmacist_id');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('phone_number');
            $table->string('profile_photo')->nullable();
            $table->string('license_number')->unique()->comment('Professional license number');
            $table->date('license_expiry')->nullable();
            $table->string('specialization')->nullable()->comment('e.g., Clinical, Community, Hospital');
            $table->enum('availability_status', ['Available', 'On Break', 'On Leave', 'Unavailable'])->default('Available');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pharmacists');
    }
};