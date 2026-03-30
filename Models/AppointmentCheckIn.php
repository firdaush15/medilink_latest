<?php
// AppointmentCheckIn.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentCheckIn extends Model
{
    use HasFactory;

    protected $primaryKey = 'check_in_id';

    protected $fillable = [
        'appointment_id',
        'nurse_id',
        'checked_in_at',
        'arrival_status',
        'check_in_notes',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id');
    }
}