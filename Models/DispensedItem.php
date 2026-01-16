<?php
// app/Models/DispensedItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DispensedItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'dispensed_item_id';

    protected $fillable = [
        'dispensing_id',
        'medicine_id',
        'prescription_item_id',
        'quantity_dispensed',
        'batch_number',
        'expiry_date',
        'unit_price',
        'total_price',
        'is_substituted',
        'substituted_with',
        'substitution_reason',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_substituted' => 'boolean',
    ];

    public function dispensing()
    {
        return $this->belongsTo(PrescriptionDispensing::class, 'dispensing_id');
    }

    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id');
    }

    public function prescriptionItem()
    {
        return $this->belongsTo(PrescriptionItem::class, 'prescription_item_id');
    }
}