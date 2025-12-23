<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientAllergy extends Model
{
    use HasFactory;

    protected $primaryKey = 'allergy_id';

    protected $fillable = [
        'patient_id',
        'allergy_type',
        'allergen_name',
        'severity',
        'reaction_description',
        'onset_date',
        'is_active',
        'notes',
        'recorded_by',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'onset_date' => 'date',
        'is_active' => 'boolean',
        'verified_at' => 'datetime',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ========================================
    // HELPER METHODS
    // ========================================
    
    public function getSeverityBadgeClass()
    {
        return match ($this->severity) {
            'Life-threatening' => 'badge-danger',
            'Severe' => 'badge-warning',
            'Moderate' => 'badge-info',
            'Mild' => 'badge-secondary',
            default => 'badge-light',
        };
    }

    public function isCritical()
    {
        return in_array($this->severity, ['Severe', 'Life-threatening']);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDrugAllergies($query)
    {
        return $query->where('allergy_type', 'Drug/Medication');
    }

    public function scopeCritical($query)
    {
        return $query->whereIn('severity', ['Severe', 'Life-threatening']);
    }
}
