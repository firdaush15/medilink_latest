<?php
// database/migrations/xxxx_create_stock_movements_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id('movement_id');
            $table->foreignId('medicine_id')->constrained('medicine_inventory', 'medicine_id')->onDelete('cascade');
            $table->foreignId('pharmacist_id')->nullable()->constrained('pharmacists', 'pharmacist_id')->onDelete('set null');
            
            $table->enum('movement_type', ['Stock In', 'Dispensed', 'Returned', 'Expired', 'Damaged', 'Adjustment']);
            $table->integer('quantity');
            $table->integer('balance_after')->comment('Stock level after this movement');
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->string('reference_number')->nullable()->comment('PO number, dispensing ID, etc');
            
            $table->timestamps();
            
            $table->index(['medicine_id', 'created_at']);
            $table->index('movement_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};