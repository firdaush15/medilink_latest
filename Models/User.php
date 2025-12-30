<?php
//User.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'address',
        'profile_photo',
        'last_seen_at',
        'account_completed',        // ✅ ADD THIS
        'registered_by_staff',      // ✅ ADD THIS
        'account_completion_token', // ✅ ADD THIS
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relationships
    public function patient()
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    // 🆕 Add nurse relationship
    public function nurse()
    {
        return $this->hasOne(Nurse::class, 'user_id');
    }

    public function pharmacist()
    {
        return $this->hasOne(Pharmacist::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_seen_at' => 'datetime', // ✅ THIS IS THE FIX
            'account_completed' => 'boolean',        // ✅ ADD THIS
            'registered_by_staff' => 'boolean',      // ✅ ADD THIS
        ];
    }

    // Check if user is online
    public function isOnline()
    {
        // User is considered online if they were active in the last 5 minutes
        return $this->last_seen_at &&
            $this->last_seen_at->greaterThan(now()->subMinutes(5));
    }

    // In app/Models/User.php

    public function getLastSeenTimeAttribute()
    {
        if (!$this->last_seen_at) {
            return null;
        }

        return $this->last_seen_at->format('H:i'); // Returns "14:00" format
    }

    public function getLastSeenFullAttribute()
    {
        if (!$this->last_seen_at) {
            return null;
        }

        // Returns "Last seen at 14:00" or "Last seen 2 hours ago"
        if ($this->last_seen_at->isToday()) {
            return 'Last seen at ' . $this->last_seen_at->format('H:i');
        }

        return 'Last seen ' . $this->last_seen_at->diffForHumans();
    }
}
