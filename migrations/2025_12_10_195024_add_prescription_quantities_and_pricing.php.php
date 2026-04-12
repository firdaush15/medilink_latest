<?php
// database/migrations/2025_12_11_add_prescription_quantities_and_pricing.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            // Link to actual medicine in inventory
            $table->foreignId('medicine_id')
                ->nullable()
                ->after('prescription_id')
                ->constrained('medicine_inventory', 'medicine_id')
                ->onDelete('set null')
                ->comment('Link to actual medicine in inventory');
            
            // Quantity fields
            $table->integer('quantity_prescribed')
                ->after('frequency')
                ->comment('Number of units prescribed (e.g., 30 tablets)');
            
            $table->integer('days_supply')
                ->nullable()
                ->after('quantity_prescribed')
                ->comment('Number of days this prescription should last');
            
            // Update existing pricing fields if they don't exist
            if (!Schema::hasColumn('prescription_items', 'unit_price')) {
                $table->decimal('unit_price', 10, 2)->default(0)->after('days_supply');
            }
            
            if (!Schema::hasColumn('prescription_items', 'total_price')) {
                $table->decimal('total_price', 10, 2)->default(0)->after('unit_price');
            }
            
            // Dispensing tracking
            $table->integer('quantity_dispensed')
                ->default(0)
                ->after('total_price')
                ->comment('Actual quantity given to patient');
            
            $table->string('batch_number')
                ->nullable()
                ->after('quantity_dispensed')
                ->comment('Batch number of dispensed medicine');
            
            $table->date('expiry_date')
                ->nullable()
                ->after('batch_number')
                ->comment('Expiry date of dispensed medicine');
            
            // Add index for performance
            $table->index('medicine_id');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropForeign(['medicine_id']);
            $table->dropColumn([
                'medicine_id',
                'quantity_prescribed',
                'days_supply',
                'quantity_dispensed',
                'batch_number',
                'expiry_date'
            ]);
        });
    }
};