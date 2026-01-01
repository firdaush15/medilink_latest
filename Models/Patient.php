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
        'no_show_count',           // ✅ ADDED
        'late_arrival_count',      // ✅ ADDED
        'is_flagged',              // ✅ ADDED - THIS WAS MISSING
        'flag_reason',             // ✅ ADDED - THIS WAS MISSING
        'last_visit_date',         // ✅ ADDED
    ];

    protected $appends = ['age'];

    // ✅ ADDED: Cast boolean fields
    protected $casts = [
        'date_of_birth' => 'date',
        'smoking' => 'boolean',
        'alcohol' => 'boolean',
        'is_flagged' => 'boolean',        // ✅ ADDED
        'last_visit_date' => 'datetime',  // ✅ ADDED
    ];

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

    public function vitalRecords()
    {
        return $this->hasMany(VitalRecord::class, 'patient_id');
    }

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

    public function ratings()
    {
        return $this->hasMany(DoctorRating::class, 'patient_id');
    }

    /**
     * All diagnoses for this patient
     */
    public function diagnoses()
    {
        return $this->hasMany(PatientDiagnosis::class, 'patient_id', 'patient_id');
    }

    /**
     * Active diagnoses (currently being treated)
     */
    public function activeDiagnoses()
    {
        return $this->hasMany(PatientDiagnosis::class, 'patient_id', 'patient_id')
            ->where('status', 'Active')
            ->with('diagnosisCode')
            ->orderBy('diagnosis_date', 'desc');
    }

    /**
     * Chronic conditions
     */
    public function chronicConditions()
    {
        return $this->hasMany(PatientDiagnosis::class, 'patient_id', 'patient_id')
            ->where('status', 'Chronic')
            ->with('diagnosisCode')
            ->orderBy('diagnosis_date', 'desc');
    }

    /**
     * Check if patient has specific diagnosis
     */
    public function hasDiagnosis($icd10Code)
    {
        return $this->diagnoses()
            ->whereHas('diagnosisCode', function ($q) use ($icd10Code) {
                $q->where('icd10_code', $icd10Code);
            })
            ->where('status', 'Active')
            ->exists();
    }

    /**
     * Get most recent diagnosis
     */
    public function latestDiagnosis()
    {
        return $this->hasOne(PatientDiagnosis::class, 'patient_id', 'patient_id')
            ->with('diagnosisCode')
            ->latest('diagnosis_date');
    }

    // ========================================
    // COMPUTED ATTRIBUTES
    // ========================================

    public function getAgeAttribute()
    {
        return $this->date_of_birth
            ? Carbon::parse($this->date_of_birth)->age
            : null;
    }

    // ========================================
    // ✅ NEW HELPER METHODS FOR FLAGS
    // ========================================

    /**
     * Check if patient has drug allergy
     */
    public function hasDrugAllergy($medicationName)
    {
        return $this->drugAllergies()
            ->where('allergen_name', 'LIKE', "%{$medicationName}%")
            ->exists();
    }

    /**
     * Flag this patient
     */
    public function flagPatient($reason)
    {
        $this->update([
            'is_flagged' => true,
            'flag_reason' => $reason,
        ]);
    }

    /**
     * Unflag this patient
     */
    public function unflagPatient()
    {
        $this->update([
            'is_flagged' => false,
            'flag_reason' => null,
        ]);
    }

    /**
     * Check if patient is flagged
     */
    public function isFlagged()
    {
        return $this->is_flagged === true;
    }

    /**
     * Increment no-show count
     */
    public function incrementNoShowCount()
    {
        $this->increment('no_show_count');
    }

    /**
     * Increment late arrival count
     */
    public function incrementLateArrivalCount()
    {
        $this->increment('late_arrival_count');
    }

    /**
     * Update last visit date
     */
    public function updateLastVisitDate()
    {
        $this->update(['last_visit_date' => now()]);
    }

    // ========================================
    // ✅ QUERY SCOPES
    // ========================================

    /**
     * Scope: Get only flagged patients
     */
    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    /**
     * Scope: Get only non-flagged patients
     */
    public function scopeNotFlagged($query)
    {
        return $query->where('is_flagged', false);
    }

    /**
     * Scope: Patients with high no-show counts
     */
    public function scopeHighNoShows($query, $threshold = 3)
    {
        return $query->where('no_show_count', '>=', $threshold);
    }

    /**
     * Scope: Recently visited patients
     */
    public function scopeRecentlyVisited($query, $days = 30)
    {
        return $query->where('last_visit_date', '>=', now()->subDays($days));
    }

    /**
     * Get patient's mental health assessments
     */
    public function mentalHealthAssessments()
    {
        return $this->hasMany(MentalHealthAssessment::class, 'patient_id', 'patient_id')
            ->orderBy('assessment_date', 'desc');
    }

    /**
     * Get latest mental health assessment
     */
    public function latestMentalHealthAssessment()
    {
        return $this->hasOne(MentalHealthAssessment::class, 'patient_id', 'patient_id')
            ->latest('assessment_date');
    }

    /**
     * Check if patient has critical mental health assessment
     */
    public function hasCriticalMentalHealth()
    {
        return $this->mentalHealthAssessments()
            ->whereIn('risk_level', ['severe', 'moderate'])
            ->where('assessment_date', '>=', now()->subMonths(3))
            ->exists();
    }

    /**
     * Get mental health trend (improving/stable/declining)
     */
    public function getMentalHealthTrend()
    {
        $assessments = $this->mentalHealthAssessments()
            ->where('assessment_date', '>=', now()->subMonths(6))
            ->orderBy('assessment_date', 'asc')
            ->get();

        if ($assessments->count() < 2) {
            return 'insufficient_data';
        }

        $firstScore = $assessments->first()->total_score;
        $lastScore = $assessments->last()->total_score;
        $difference = $lastScore - $firstScore;

        if ($difference > 5) {
            return 'declining'; // Score increasing = worsening
        } elseif ($difference < -5) {
            return 'improving'; // Score decreasing = improving
        } else {
            return 'stable';
        }
    }
}
