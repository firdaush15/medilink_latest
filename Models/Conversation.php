<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasFactory;

    protected $primaryKey = 'conversation_id';

    protected $fillable = [
        'conversation_type',
        'doctor_id',
        'admin_id',
        'patient_id',
        'nurse_id',
        'receptionist_id',
        'pharmacist_id',
        'appointment_id',
        'subject',
        'status',
        'last_message_at',
        'is_starred',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_starred'      => 'boolean',
    ];

    // ========================================
    // RELATIONSHIPS
    // ========================================

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_id', 'nurse_id');
    }

    public function receptionist()
    {
        return $this->belongsTo(Receptionist::class, 'receptionist_id', 'receptionist_id');
    }

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class, 'pharmacist_id', 'pharmacist_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class, 'appointment_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest('created_at');
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    public function getUnreadCount($userId)
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->count();
    }

    public function markAllAsRead($userId)
    {
        $this->messages()
            ->where('sender_id', '!=', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Get the display name of the "other party" from the nurse's perspective.
     */
    public function getOtherPartyNameForNurse(): string
    {
        return match ($this->conversation_type) {
            'doctor_nurse'         => 'Dr. ' . ($this->doctor?->user?->name ?? 'Doctor'),
            'nurse_admin'          => 'Admin Support',
            'nurse_doctor'         => 'Dr. ' . ($this->doctor?->user?->name ?? 'Doctor'),
            'nurse_receptionist'   => $this->receptionist?->user?->name ?? 'Receptionist',
            'nurse_pharmacist'     => $this->pharmacist?->user?->name ?? 'Pharmacist',
            default                => 'Unknown',
        };
    }

    /**
     * Get the display name of the "other party" from the doctor's perspective.
     */
    public function getOtherPartyNameForDoctor(): string
    {
        return match ($this->conversation_type) {
            'doctor_admin'   => 'Admin Support',
            'doctor_patient' => $this->patient?->user?->name ?? 'Patient',
            'doctor_nurse',
            'nurse_doctor'   => $this->nurse?->user?->name ?? 'Nurse',
            default          => 'Unknown',
        };
    }
}