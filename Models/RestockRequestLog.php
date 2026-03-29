<?php

// ========================================
// app/Models/RestockRequestLog.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockRequestLog extends Model
{
    protected $primaryKey = 'log_id';
    public $timestamps = false;
    
    protected $fillable = [
        'request_id',
        'action',
        'from_status',
        'to_status',
        'performed_by',
        'performed_by_role',
        'notes',
        'changes',
        'ip_address',
        'performed_at',
    ];
    
    protected $casts = [
        'changes' => 'array',
        'performed_at' => 'datetime',
    ];
    
    public function request()
    {
        return $this->belongsTo(RestockRequest::class, 'request_id', 'request_id');
    }
    
    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
