<?php
// database/migrations/xxxx_create_medicine_inventory_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medicine_inventory', function (Blueprint $table) {
            $table->id('medicine_id');
            $table->string('medicine_name');
            $table->string('generic_name')->nullable();
            $table->string('brand_name')->nullable();
            $table->string('category')->comment('e.g., Antibiotic, Analgesic, Antihypertensive');
            $table->enum('form', ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Cream', 'Drops', 'Inhaler', 'Other']);
            $table->string('strength')->comment('e.g., 500mg, 10mg/ml');
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('reorder_level')->default(50)->comment('Alert when stock below this');
            $table->decimal('unit_price', 10, 2);
            $table->string('supplier')->nullable();
            $table->string('batch_number')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            $table->text('storage_instructions')->nullable();
            $table->text('side_effects')->nullable();
            $table->text('contraindications')->nullable();
            $table->boolean('requires_prescription')->default(true);
            $table->boolean('is_controlled_substance')->default(false);
            $table->enum('status', ['Active', 'Low Stock', 'Out of Stock', 'Expired', 'Discontinued'])->default('Active');
            $table->timestamps();
            
            $table->index(['medicine_name', 'status']);
            $table->index('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medicine_inventory');
    }
};