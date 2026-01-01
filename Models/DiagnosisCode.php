<?php
// ========================================
// app/Models/DiagnosisCode.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosisCode extends Model
{
    protected $primaryKey = 'diagnosis_code_id';
    
    protected $fillable = [
        'icd10_code',
        'diagnosis_name',
        'category',
        'description',
        'severity',
        'is_chronic',
        'is_infectious',
        'requires_followup',
        'typical_recovery_days',
        'is_active',
    ];
    
    protected $casts = [
        'is_chronic' => 'boolean',
        'is_infectious' => 'boolean',
        'requires_followup' => 'boolean',
        'is_active' => 'boolean',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function patientDiagnoses()
    {
        return $this->hasMany(PatientDiagnosis::class, 'diagnosis_code_id');
    }
    
    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
    
    public function scopeInfectious($query)
    {
        return $query->where('is_infectious', true);
    }
    
    public function scopeChronic($query)
    {
        return $query->where('is_chronic', true);
    }
    
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('diagnosis_name', 'LIKE', "%{$search}%")
              ->orWhere('icd10_code', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }
}