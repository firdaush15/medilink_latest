<?php
// database/migrations/xxxx_create_prescription_dispensings_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_dispensings', function (Blueprint $table) {
            $table->id('dispensing_id');
            $table->foreignId('prescription_id')->constrained('prescriptions', 'prescription_id')->onDelete('cascade');
            $table->foreignId('pharmacist_id')->nullable()->constrained('pharmacists', 'pharmacist_id')->onDelete('cascade');
            $table->foreignId('patient_id')->constrained('patients', 'patient_id')->onDelete('cascade');
            
            // Verification
            $table->enum('verification_status', ['Pending', 'Verified', 'Rejected', 'Dispensed'])->default('Pending');
            $table->text('verification_notes')->nullable()->comment('Issues found during verification');
            $table->timestamp('verified_at')->nullable();
            
            // Dispensing details
            $table->timestamp('dispensed_at')->nullable();
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->enum('payment_status', ['Pending', 'Paid', 'Insurance'])->default('Pending');
            $table->string('payment_method')->nullable();
            
            // Patient counseling
            $table->boolean('patient_counseled')->default(false);
            $table->text('counseling_notes')->nullable();
            $table->text('special_instructions')->nullable();
            
            // Safety checks
            $table->boolean('allergy_checked')->default(false);
            $table->boolean('interaction_checked')->default(false);
            $table->text('interaction_warnings')->nullable();
            
            // Digital signature (for audit trail)
            $table->string('pharmacist_signature')->nullable();
            
            $table->timestamps();
            
            $table->index(['verification_status', 'created_at']);
            $table->index(['pharmacist_id', 'dispensed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_dispensings');
    }
};