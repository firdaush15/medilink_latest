<?php
// app/Models/PrescriptionDispensing.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriptionDispensing extends Model
{
    use HasFactory;

    protected $primaryKey = 'dispensing_id';

    protected $fillable = [
        'prescription_id',
        'pharmacist_id',
        'patient_id',
        'verification_status',
        'verification_notes',
        'verified_at',
        'dispensed_at',
        'total_amount',
        'payment_status',
        'payment_method',
        'patient_counseled',
        'counseling_notes',
        'special_instructions',
        'allergy_checked',
        'interaction_checked',
        'interaction_warnings',
        'pharmacist_signature',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'dispensed_at' => 'datetime',
        'patient_counseled' => 'boolean',
        'allergy_checked' => 'boolean',
        'interaction_checked' => 'boolean',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class, 'pharmacist_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function dispensedItems()
    {
        return $this->hasMany(DispensedItem::class, 'dispensing_id');
    }

    // Status checks
    public function canDispense()
    {
        return $this->verification_status === 'Verified' && 
               $this->allergy_checked && 
               $this->interaction_checked;
    }

    public function isComplete()
    {
        return $this->verification_status === 'Dispensed' && 
               $this->dispensed_at !== null;
    }

    // Actions
    public function verify($pharmacistId, $notes = null)
    {
        $this->update([
            'verification_status' => 'Verified',
            'verification_notes' => $notes,
            'verified_at' => now(),
        ]);
    }

    public function reject($reason)
    {
        $this->update([
            'verification_status' => 'Rejected',
            'verification_notes' => $reason,
        ]);
    }

    public function markDispensed()
    {
        $this->update([
            'verification_status' => 'Dispensed',
            'dispensed_at' => now(),
        ]);
    }
}