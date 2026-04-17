<?php

// ========================================
// app/Models/DiagnosisSymptom.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiagnosisSymptom extends Model
{
    protected $primaryKey = 'symptom_id';
    
    protected $fillable = [
        'patient_diagnosis_id',
        'symptom_name',
        'severity',
        'duration_days',
    ];
    
    public function patientDiagnosis()
    {
        return $this->belongsTo(PatientDiagnosis::class, 'patient_diagnosis_id');
    }
}

