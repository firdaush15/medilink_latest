<?php
// database/migrations/2025_12_21_create_restock_system_tables.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========================================
        // 1. RESTOCK REQUESTS
        // ========================================
        Schema::create('restock_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->string('request_number')->unique()->comment('Auto: REQ-2025-0001');
            
            // Medicine & Requester
            $table->foreignId('medicine_id')->constrained('medicine_inventory', 'medicine_id')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('pharmacists', 'pharmacist_id')->onDelete('cascade');
            
            // Request Details
            $table->integer('current_stock')->comment('Stock at time of request');
            $table->integer('quantity_requested');
            $table->text('justification')->nullable();
            $table->enum('priority', ['Normal', 'Urgent', 'Critical'])->default('Normal');
            
            // Supplier & Cost
            $table->string('preferred_supplier')->nullable();
            $table->decimal('estimated_unit_price', 10, 2)->nullable();
            $table->decimal('estimated_total_cost', 10, 2)->nullable();
            
            // Approval Workflow
            $table->enum('status', [
                'Pending',           // Waiting admin approval
                'Approved',          // Admin approved, ready to order
                'Ordered',           // PO sent to supplier
                'Partially Received', // Some items received
                'Received',          // All items received
                'Rejected',          // Admin rejected
                'Cancelled'          // Request cancelled
            ])->default('Pending');
            
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            
            $table->text('rejection_reason')->nullable();
            $table->timestamp('rejected_at')->nullable();
            
            // Order tracking
            $table->string('purchase_order_number')->nullable();
            $table->timestamp('ordered_at')->nullable();
            $table->date('expected_delivery_date')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'priority', 'created_at']);
            $table->index(['medicine_id', 'status']);
        });

        // ========================================
        // 2. STOCK RECEIPTS (Receiving)
        // ========================================
        Schema::create('stock_receipts', function (Blueprint $table) {
            $table->id('receipt_id');
            $table->string('receipt_number')->unique()->comment('Auto: RCV-2025-0001');
            
            // Link to request (optional - can receive without request)
            $table->foreignId('restock_request_id')->nullable()
                ->constrained('restock_requests', 'request_id')
                ->onDelete('set null');
            
            $table->foreignId('medicine_id')->constrained('medicine_inventory', 'medicine_id')->onDelete('cascade');
            $table->foreignId('received_by')->constrained('pharmacists', 'pharmacist_id')->onDelete('cascade');
            
            // Shipment Details
            $table->integer('quantity_ordered')->nullable();
            $table->integer('quantity_received');
            $table->string('batch_number');
            $table->date('manufacture_date')->nullable();
            $table->date('expiry_date');
            
            // Supplier & Cost
            $table->string('supplier');
            $table->string('supplier_invoice_number')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total_cost', 10, 2);
            
            // Quality Control
            $table->enum('quality_status', ['Accepted', 'Rejected', 'On Hold'])->default('Accepted');
            $table->text('quality_check_notes')->nullable();
            $table->boolean('packaging_intact')->default(true);
            $table->boolean('temperature_maintained')->default(true);
            $table->boolean('expiry_acceptable')->default(true)->comment('Min 1 year from receipt');
            
            // Timestamps
            $table->timestamp('received_at');
            $table->timestamps();
            
            $table->index(['medicine_id', 'received_at']);
            $table->index('batch_number');
        });

        // ========================================
        // 3. MEDICINE DISPOSALS
        // ========================================
        Schema::create('medicine_disposals', function (Blueprint $table) {
            $table->id('disposal_id');
            $table->string('disposal_number')->unique()->comment('Auto: DSP-2025-0001');
            
            $table->foreignId('medicine_id')->constrained('medicine_inventory', 'medicine_id')->onDelete('cascade');
            $table->integer('quantity_disposed');
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            
            // Disposal Reason
            $table->enum('reason', [
                'Expired',
                'Near Expiry', // Dispose before actual expiry
                'Damaged',
                'Contaminated',
                'Recalled by Manufacturer',
                'Quality Issue',
                'Other'
            ]);
            $table->text('reason_details')->nullable();
            
            // Disposal Method (as per regulations)
            $table->enum('disposal_method', [
                'Incineration',
                'Chemical Treatment',
                'Encapsulation',
                'Landfill (Non-hazardous)',
                'Return to Supplier',
                'Other'
            ]);
            $table->text('disposal_details')->nullable();
            
            // Authorization
            $table->foreignId('disposed_by')->constrained('pharmacists', 'pharmacist_id')->onDelete('cascade');
            $table->foreignId('witnessed_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('Admin or senior pharmacist witness');
            
            // Documentation
            $table->string('disposal_certificate_path')->nullable()->comment('PDF/photo of disposal cert');
            $table->string('authorization_document')->nullable();
            $table->text('documentation_notes')->nullable();
            
            // Cost
            $table->decimal('estimated_loss', 10, 2)->nullable()->comment('Value of disposed medicine');
            
            $table->timestamp('disposed_at');
            $table->timestamps();
            
            $table->index(['medicine_id', 'disposed_at']);
            $table->index(['reason', 'disposed_at']);
        });

        // ========================================
        // 4. RESTOCK REQUEST HISTORY (Audit Trail)
        // ========================================
        Schema::create('restock_request_logs', function (Blueprint $table) {
            $table->id('log_id');
            $table->foreignId('request_id')->constrained('restock_requests', 'request_id')->onDelete('cascade');
            
            $table->string('action'); // 'created', 'approved', 'rejected', 'ordered', 'received'
            $table->string('from_status')->nullable();
            $table->string('to_status');
            
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->enum('performed_by_role', ['pharmacist', 'admin', 'system']);
            
            $table->text('notes')->nullable();
            $table->json('changes')->nullable(); // What changed
            $table->string('ip_address', 45)->nullable();
            
            $table->timestamp('performed_at');
            
            $table->index(['request_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restock_request_logs');
        Schema::dropIfExists('medicine_disposals');
        Schema::dropIfExists('stock_receipts');
        Schema::dropIfExists('restock_requests');
    }
};