<?php
// app/Models/AssessmentAnswer.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentAnswer extends Model
{
    protected $primaryKey = 'answer_id';
    
    protected $fillable = [
        'assessment_id',
        'question_number',
        'question_text',
        'answer_option',
        'score_value',
    ];
    
    public function assessment()
    {
        return $this->belongsTo(MentalHealthAssessment::class, 'assessment_id', 'assessment_id');
    }
}