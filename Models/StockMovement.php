<?php
// app/Models/StockMovement.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $primaryKey = 'movement_id';

    protected $fillable = [
        'medicine_id',
        'pharmacist_id',
        'batch_id', // <--- Added missing fillable field
        'movement_type',
        'quantity',
        'balance_after',
        'batch_number',
        'notes',
        'reference_number',
    ];

    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id');
    }

    public function pharmacist()
    {
        return $this->belongsTo(Pharmacist::class, 'pharmacist_id');
    }

    // 👇 Added missing relationship method
    public function batch()
    {
        return $this->belongsTo(MedicineBatch::class, 'batch_id');
    }
}