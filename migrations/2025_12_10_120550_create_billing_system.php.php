<?php
// database/migrations/2024_12_11_create_billing_system.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========================================
        // 1. ADD BILLING FIELDS TO APPOINTMENTS TABLE
        // ========================================
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('consultation_fee', 10, 2)->default(0)->after('checkout_notes');
            $table->decimal('procedures_fee', 10, 2)->default(0)->after('consultation_fee');
            $table->decimal('pharmacy_fee', 10, 2)->default(0)->after('procedures_fee');
            $table->decimal('subtotal', 10, 2)->default(0)->after('pharmacy_fee');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('subtotal');
            $table->decimal('discount_amount', 10, 2)->default(0)->after('tax_amount');
            $table->decimal('total_amount', 10, 2)->default(0)->after('discount_amount');
            $table->string('payment_method')->nullable()->after('total_amount');
            $table->text('billing_notes')->nullable()->after('payment_method');
        });
        
        // ========================================
        // 2. CREATE BILLING_ITEMS TABLE (Itemized Charges)
        // ========================================
        Schema::create('billing_items', function (Blueprint $table) {
            $table->id('billing_item_id');
            $table->unsignedBigInteger('appointment_id');
            $table->enum('item_type', [
                'consultation',
                'procedure',
                'lab_test',
                'imaging',
                'medication',
                'medical_supply',
                'other'
            ]);
            $table->string('item_code')->nullable(); // e.g., "LAB-CBC", "IMG-XRAY"
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('amount', 10, 2); // quantity * unit_price
            $table->unsignedBigInteger('medical_record_id')->nullable(); // If linked to medical record
            $table->unsignedBigInteger('prescription_id')->nullable(); // If linked to prescription
            $table->unsignedBigInteger('added_by'); // User who added this charge
            $table->timestamps();
            
            $table->foreign('appointment_id')
                ->references('appointment_id')
                ->on('appointments')
                ->onDelete('cascade');
                
            $table->foreign('medical_record_id')
                ->references('record_id')
                ->on('medical_records')
                ->onDelete('set null');
                
            $table->foreign('prescription_id')
                ->references('prescription_id')
                ->on('prescriptions')
                ->onDelete('set null');
                
            $table->foreign('added_by')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
                
            $table->index(['appointment_id', 'item_type']);
        });
        
        // ========================================
        // 3. CREATE PROCEDURE_PRICES TABLE (Standard Pricing Catalog)
        // ========================================
        Schema::create('procedure_prices', function (Blueprint $table) {
            $table->id('procedure_id');
            $table->string('procedure_code')->unique(); // e.g., "LAB-CBC", "IMG-XRAY"
            $table->string('procedure_name');
            $table->enum('category', [
                'consultation',
                'blood_test',
                'imaging',
                'minor_procedure',
                'major_procedure',
                'diagnostic_test',
                'other'
            ]);
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['category', 'is_active']);
        });

        // ========================================
        // 4. ADD unit_price TO prescription_items TABLE
        // ========================================
        Schema::table('prescription_items', function (Blueprint $table) {
            $table->decimal('unit_price', 10, 2)->default(0)->after('frequency');
            $table->integer('quantity')->default(1)->after('unit_price')
                ->comment('Number of units (e.g., 30 tablets)');
            $table->decimal('total_price', 10, 2)->default(0)->after('quantity');
        });

        // ========================================
        // 5. ADD payment_date TO prescription_dispensings TABLE
        // ========================================
        Schema::table('prescription_dispensings', function (Blueprint $table) {
            $table->timestamp('payment_date')->nullable()->after('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_dispensings', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });

        Schema::table('prescription_items', function (Blueprint $table) {
            $table->dropColumn(['unit_price', 'quantity', 'total_price']);
        });

        Schema::dropIfExists('procedure_prices');
        Schema::dropIfExists('billing_items');
        
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn([
                'consultation_fee',
                'procedures_fee',
                'pharmacy_fee',
                'subtotal',
                'tax_amount',
                'discount_amount',
                'total_amount',
                'payment_method',
                'billing_notes'
            ]);
        });
    }
};