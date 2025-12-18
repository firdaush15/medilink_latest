<?php
// Patient.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory;

    protected $primaryKey = 'patient_id';

    protected $fillable = [
        'user_id',
        'phone_number',
        'gender',
        'date_of_birth',
        'emergency_contact',
        'chronic_conditions',
        'current_medications',
        'past_surgeries',
        'blood_type',
        'smoking',
        'alcohol',
        'family_medical_history',
    ];

    protected $appends = ['age'];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class, 'patient_id');
    }

    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'patient_id');
    }

    // ✅ ADD THIS RELATIONSHIP - THIS WAS MISSING!
    public function vitalRecords()
    {
        return $this->hasMany(VitalRecord::class, 'patient_id');
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth
            ? Carbon::parse($this->date_of_birth)->age
            : null;
    }

    public function ratings()
    {
        return $this->hasMany(DoctorRating::class, 'patient_id');
    }

    // Add this relationship to Patient.php:
    public function allergies()
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id');
    }

    public function activeAllergies()
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id')
            ->where('is_active', true)
            ->orderByRaw("FIELD(severity, 'Life-threatening', 'Severe', 'Moderate', 'Mild')");
    }

    public function drugAllergies()
    {
        return $this->hasMany(PatientAllergy::class, 'patient_id')
            ->where('allergy_type', 'Drug/Medication')
            ->where('is_active', true);
    }

    public function hasDrugAllergy($medicationName)
    {
        return $this->drugAllergies()
            ->where('allergen_name', 'LIKE', "%{$medicationName}%")
            ->exists();
    }
}
