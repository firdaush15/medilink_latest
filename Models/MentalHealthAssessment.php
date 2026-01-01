<?php
// app/Models/MentalHealthAssessment.php - COMPLETE VERSION

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MentalHealthAssessment extends Model
{
    protected $primaryKey = 'assessment_id';
    
    protected $fillable = [
        'patient_id',
        'assessment_type',
        'total_score',
        'risk_level',
        'recommendations',
        'assessment_date',
        'reviewed_by_doctor_id',
        'doctor_notes',
        'reviewed_at',
        'is_shared_with_doctor',
    ];
    
    // ✅ IMPORTANT: This automatically converts JSON ↔ Array
    protected $casts = [
        'assessment_date' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_shared_with_doctor' => 'boolean',
        'recommendations' => 'array', // ✅ Auto JSON conversion
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }
    
    public function reviewedByDoctor()
    {
        return $this->belongsTo(Doctor::class, 'reviewed_by_doctor_id', 'doctor_id');
    }
    
    public function answers()
    {
        return $this->hasMany(AssessmentAnswer::class, 'assessment_id', 'assessment_id');
    }
    
    // ========================================
    // HELPER METHODS
    // ========================================
    
    /**
     * Get human-readable risk level
     */
    public function getRiskLevelDisplayAttribute()
    {
        return match($this->risk_level) {
            'good' => 'Good Mental State 😊',
            'mild' => 'Mild Stress/Anxiety 😐',
            'moderate' => 'Moderate Stress/Depression 😟',
            'severe' => 'Severe Emotional Distress 😔',
            default => 'Unknown',
        };
    }
    
    /**
     * Get color for risk level
     */
    public function getRiskColorAttribute()
    {
        return match($this->risk_level) {
            'good' => '#4CAF50',      // Green
            'mild' => '#FF9800',      // Orange
            'moderate' => '#FF5722',  // Deep Orange
            'severe' => '#F44336',    // Red
            default => '#9E9E9E',     // Grey
        };
    }
    
    /**
     * Check if reviewed by doctor
     */
    public function isReviewed()
    {
        return !is_null($this->reviewed_at);
    }
}