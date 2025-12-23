<?php

// MessageTemplate.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageTemplate extends Model
{
    use HasFactory;

    protected $primaryKey = 'template_id';

    protected $fillable = [
        'template_name',
        'template_type',
        'template_content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Replace placeholders with actual data
    public function render($data = [])
    {
        $content = $this->template_content;
        
        foreach ($data as $key => $value) {
            $content = str_replace('{'.$key.'}', $value, $content);
        }
        
        return $content;
    }
}