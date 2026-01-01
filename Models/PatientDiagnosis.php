<?php

// ========================================
// app/Models/PatientDiagnosis.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientDiagnosis extends Model
{
    protected $primaryKey = 'patient_diagnosis_id';
    protected $table = 'patient_diagnoses';
    
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'doctor_id',
        'diagnosis_code_id',
        'diagnosis_type',
        'certainty',
        'diagnosis_date',
        'clinical_notes',
        'treatment_plan',
        'status',
        'resolved_date',
        'requires_referral',
        'referral_to',
    ];
    
    protected $casts = [
        'diagnosis_date' => 'date',
        'resolved_date' => 'date',
        'requires_referral' => 'boolean',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }
    
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }
    
    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
    
    public function diagnosisCode()
    {
        return $this->belongsTo(DiagnosisCode::class, 'diagnosis_code_id');
    }
    
    public function symptoms()
    {
        return $this->hasMany(DiagnosisSymptom::class, 'patient_diagnosis_id');
    }
    
    // ========================================
    // SCOPES
    // ========================================
    
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }
    
    public function scopeResolved($query)
    {
        return $query->where('status', 'Resolved');
    }
    
    public function scopePrimary($query)
    {
        return $query->where('diagnosis_type', 'Primary');
    }
    
    public function scopeForDoctor($query, $doctorId)
    {
        return $query->where('doctor_id', $doctorId);
    }
    
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('diagnosis_date', [$startDate, $endDate]);
    }
}