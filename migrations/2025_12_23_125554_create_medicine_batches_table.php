<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create medicine_batches table for tracking individual batches
        Schema::create('medicine_batches', function (Blueprint $table) {
            $table->id('batch_id');
            $table->unsignedBigInteger('medicine_id');
            $table->string('batch_number')->unique();
            $table->integer('quantity')->default(0);
            $table->string('supplier')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            $table->date('received_date');
            $table->decimal('unit_price', 10, 2);
            $table->enum('status', ['active', 'depleted', 'expired', 'recalled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign key
            $table->foreign('medicine_id')
                  ->references('medicine_id')
                  ->on('medicine_inventory')
                  ->onDelete('cascade');

            // Indexes
            $table->index('medicine_id');
            $table->index('expiry_date');
            $table->index('status');
        });

        // Modify medicine_inventory table
        Schema::table('medicine_inventory', function (Blueprint $table) {
            // Remove batch-specific columns (they now belong to medicine_batches)
            $table->dropColumn(['batch_number', 'supplier', 'manufacture_date', 'expiry_date']);
            
            // Add unique constraint to prevent duplicate medicines
            $table->unique(['medicine_name', 'strength', 'form'], 'unique_medicine');
        });

        // Update stock_movements to reference batches
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedBigInteger('batch_id')->nullable()->after('medicine_id');
            
            $table->foreign('batch_id')
                  ->references('batch_id')
                  ->on('medicine_batches')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::table('medicine_inventory', function (Blueprint $table) {
            $table->dropUnique('unique_medicine');
            $table->string('batch_number')->nullable();
            $table->string('supplier')->nullable();
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date')->nullable();
        });

        Schema::dropIfExists('medicine_batches');
    }
};