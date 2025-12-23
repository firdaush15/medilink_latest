<?php

// MedicalRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'record_id';

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'record_date',
        'record_type',
        'record_title',  // ✅ ADDED THIS - was missing!
        'description',
        'file_path',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }
}