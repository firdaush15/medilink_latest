<?php
// app/Models/ShiftTemplate.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftTemplate extends Model
{
    protected $primaryKey = 'template_id';
    
    protected $fillable = [
        'template_name',
        'start_time',
        'end_time',
        'duration_hours',
        'color_code',
        'is_active',
    ];
    
    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];
    
    public function shifts()
    {
        return $this->hasMany(StaffShift::class, 'template_id');
    }
}