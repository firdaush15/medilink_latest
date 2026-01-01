<?php
// NurseReport.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NurseReport extends Model
{
    use HasFactory;

    protected $primaryKey = 'report_id';

    protected $fillable = [
        'nurse_id',
        'patient_id',
        'report_type',
        'report_number',
        'event_datetime',
        'location',
        'severity',
        'description',
        'actions_taken',
        'patient_response',
        'followup_required',
        'physician_notified',
        'additional_notes',
        'is_confidential',
    ];

    protected $casts = [
        'event_datetime' => 'datetime',
        'followup_required' => 'boolean',
        'physician_notified' => 'boolean',
        'is_confidential' => 'boolean',
    ];

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    // Auto-generate report number
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (!$report->report_number) {
                $prefix = strtoupper(substr($report->report_type, 0, 3));
                $year = now()->year;
                $lastReport = self::whereYear('created_at', $year)
                    ->where('report_type', $report->report_type)
                    ->latest('report_id')
                    ->first();
                
                $number = $lastReport ? (int)substr($lastReport->report_number, -4) + 1 : 1;
                $report->report_number = "{$prefix}-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
        });
    }
}