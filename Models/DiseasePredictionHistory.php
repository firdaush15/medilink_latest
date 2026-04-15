<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class DiseasePredictionHistory extends Model
{
    protected $fillable = [
        'patient_id', 'symptoms', 'prediction', 'confidence',
        'top_predictions', 'risk_level', 'recommendation',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id', 'patient_id');
    }
}