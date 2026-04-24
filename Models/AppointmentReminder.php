<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppointmentReminder extends Model
{
    use HasFactory;

    protected $primaryKey = 'reminder_id';

    protected $fillable = [
        'appointment_id',
        'reminder_type',
        'scheduled_for',
        'sent_at',
        'status',
        'failure_reason',
        'message_content',
        'recipient',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
    ];

    // Relationships
    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id', 'appointment_id');
    }

    // Helper methods
    public function markAsSent()
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsFailed($reason)
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
        ]);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeDueForSending($query)
    {
        return $query->where('status', 'pending')
            ->where('scheduled_for', '<=', now());
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    // Static helper
    public static function createForAppointment($appointment, $type = 'sms')
    {
        // Schedule reminder 24 hours before appointment
        $appointmentDateTime = \Carbon\Carbon::parse(
            $appointment->appointment_date->format('Y-m-d') . ' ' . 
            $appointment->appointment_time->format('H:i:s')
        );
        
        $scheduledFor = $appointmentDateTime->copy()->subHours(24);

        // Generate message
        $message = "Reminder: Your appointment with Dr. {$appointment->doctor->user->name} is tomorrow at {$appointment->appointment_time->format('h:i A')}. Clinic: MediLink Hospital. Reply CONFIRM to confirm.";

        $recipient = $type === 'email' 
            ? $appointment->patient->user->email 
            : $appointment->patient->phone_number;

        return self::create([
            'appointment_id' => $appointment->appointment_id,
            'reminder_type' => $type,
            'scheduled_for' => $scheduledFor,
            'message_content' => $message,
            'recipient' => $recipient,
            'status' => 'pending',
        ]);
    }
}