<?php
// VitalRecord.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VitalRecord extends Model
{
    use HasFactory;

    protected $primaryKey = 'vital_id';

    protected $fillable = [
        'patient_id',
        'nurse_id',
        'appointment_id',
        'temperature',
        'blood_pressure',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'recorded_at',
        'notes',
        'is_critical',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'is_critical' => 'boolean',
        'temperature' => 'decimal:1',
        'weight' => 'decimal:2',
        'height' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    // Check if vitals are within normal ranges
    public function checkCriticalStatus()
    {
        $isCritical = false;

        // Temperature: Normal range 36.1-37.2°C
        if ($this->temperature && ($this->temperature < 36.0 || $this->temperature > 38.0)) {
            $isCritical = true;
        }

        // Heart rate: Normal range 60-100 BPM
        if ($this->heart_rate && ($this->heart_rate < 50 || $this->heart_rate > 110)) {
            $isCritical = true;
        }

        // Oxygen saturation: Normal >95%
        if ($this->oxygen_saturation && $this->oxygen_saturation < 90) {
            $isCritical = true;
        }

        // Blood pressure parsing (simplified)
        if ($this->blood_pressure) {
            $parts = explode('/', $this->blood_pressure);
            if (count($parts) == 2) {
                $systolic = (int)$parts[0];
                $diastolic = (int)$parts[1];
                
                if ($systolic < 90 || $systolic > 140 || $diastolic < 60 || $diastolic > 90) {
                    $isCritical = true;
                }
            }
        }

        $this->is_critical = $isCritical;
        $this->save();

        return $isCritical;
    }
}