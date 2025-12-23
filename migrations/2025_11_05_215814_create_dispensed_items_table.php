<?php
// database/migrations/xxxx_create_dispensed_items_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensed_items', function (Blueprint $table) {
            $table->id('dispensed_item_id');
            $table->foreignId('dispensing_id')->constrained('prescription_dispensings', 'dispensing_id')->onDelete('cascade');
            $table->foreignId('medicine_id')->constrained('medicine_inventory', 'medicine_id')->onDelete('cascade');
            $table->foreignId('prescription_item_id')->constrained('prescription_items', 'item_id')->onDelete('cascade');
            
            $table->integer('quantity_dispensed');
            $table->string('batch_number');
            $table->date('expiry_date');
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_price', 10, 2);
            
            // Substitution tracking (if generic used instead of brand)
            $table->boolean('is_substituted')->default(false);
            $table->string('substituted_with')->nullable();
            $table->text('substitution_reason')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensed_items');
    }
};