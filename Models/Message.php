<?php

// Message.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $primaryKey = 'message_id';

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'sender_type',
        'message_content',
        'attachment_path',
        'is_read',
        'read_at',
        'priority',
        'is_system_message',
        'requires_response',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'is_system_message' => 'boolean',
        'requires_response' => 'boolean',
        'read_at' => 'datetime',
    ];

    // Relationships
    public function conversation()
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Helper to mark as read
    public function markAsRead()
    {
        if (!$this->is_read) {
            $this->update([
                'is_read' => true,
                'read_at' => now()
            ]);
        }
    }
}