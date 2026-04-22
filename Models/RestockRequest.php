<?php
// ========================================
// app/Models/RestockRequest.php
// ========================================

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestockRequest extends Model
{
    protected $primaryKey = 'request_id';
    
    protected $fillable = [
        'request_number',
        'medicine_id',
        'requested_by',
        'current_stock',
        'quantity_requested',
        'justification',
        'priority',
        'preferred_supplier',
        'estimated_unit_price',
        'estimated_total_cost',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejection_reason',
        'rejected_at',
        'purchase_order_number',
        'ordered_at',
        'expected_delivery_date',
    ];
    
    protected $casts = [
        'current_stock' => 'integer',
        'quantity_requested' => 'integer',
        'estimated_unit_price' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'ordered_at' => 'datetime',
        'expected_delivery_date' => 'date',
    ];
    
    // ========================================
    // RELATIONSHIPS
    // ========================================
    
    public function medicine()
    {
        return $this->belongsTo(MedicineInventory::class, 'medicine_id', 'medicine_id');
    }
    
    public function requestedBy()
    {
        return $this->belongsTo(Pharmacist::class, 'requested_by', 'pharmacist_id');
    }
    
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
    public function receipts()
    {
        return $this->hasMany(StockReceipt::class, 'restock_request_id', 'request_id');
    }
    
    public function logs()
    {
        return $this->hasMany(RestockRequestLog::class, 'request_id', 'request_id');
    }
    
    // ========================================
    // AUTO-GENERATE REQUEST NUMBER
    // ========================================
    
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($request) {
            if (!$request->request_number) {
                $year = now()->year;
                $lastRequest = self::whereYear('created_at', $year)
                    ->latest('request_id')
                    ->first();
                
                $number = $lastRequest ? (int)substr($lastRequest->request_number, -4) + 1 : 1;
                $request->request_number = "REQ-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
            }
            
            // Auto-calculate total cost
            if ($request->quantity_requested && $request->estimated_unit_price) {
                $request->estimated_total_cost = $request->quantity_requested * $request->estimated_unit_price;
            }
        });
    }
    
    // ========================================
    // STATUS HELPERS
    // ========================================
    
    public function isPending()
    {
        return $this->status === 'Pending';
    }
    
    public function isApproved()
    {
        return $this->status === 'Approved';
    }
    
    public function isRejected()
    {
        return $this->status === 'Rejected';
    }
    
    public function isCompleted()
    {
        return $this->status === 'Received';
    }
    
    public function canApprove()
    {
        return $this->status === 'Pending';
    }
    
    public function canOrder()
    {
        return $this->status === 'Approved';
    }
    
    public function canReceive()
    {
        return in_array($this->status, ['Approved', 'Ordered', 'Partially Received']);
    }
    
    // ========================================
    // ACTIONS
    // ========================================
    
    public function approve($adminId, $notes = null)
    {
        $this->update([
            'status' => 'Approved',
            'approved_by' => $adminId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
        
        $this->logAction('approved', 'Pending', 'Approved', $adminId, 'admin', $notes);
    }
    
    public function reject($adminId, $reason)
    {
        $this->update([
            'status' => 'Rejected',
            'approved_by' => $adminId,
            'rejected_at' => now(),
            'rejection_reason' => $reason,
        ]);
        
        $this->logAction('rejected', 'Pending', 'Rejected', $adminId, 'admin', $reason);
    }
    
    public function markAsOrdered($poNumber, $expectedDate = null)
    {
        $this->update([
            'status' => 'Ordered',
            'purchase_order_number' => $poNumber,
            'ordered_at' => now(),
            'expected_delivery_date' => $expectedDate,
        ]);
        
        $this->logAction('ordered', 'Approved', 'Ordered', auth()->id(), 'pharmacist', "PO: {$poNumber}");
    }
    
    public function markAsReceived()
    {
        $this->update([
            'status' => 'Received',
        ]);
        
        $this->logAction('received', $this->status, 'Received', auth()->id(), 'pharmacist');
    }
    
    // ========================================
    // LOGGING
    // ========================================
    
    protected function logAction($action, $fromStatus, $toStatus, $userId, $role, $notes = null)
    {
        RestockRequestLog::create([
            'request_id' => $this->request_id,
            'action' => $action,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'performed_by' => $userId,
            'performed_by_role' => $role,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'performed_at' => now(),
        ]);
    }
    
    // ========================================
    // DISPLAY HELPERS
    // ========================================
    
    public function getPriorityBadgeClass()
    {
        return match($this->priority) {
            'Critical' => 'badge-danger',
            'Urgent' => 'badge-warning',
            'Normal' => 'badge-info',
            default => 'badge-secondary',
        };
    }
    
    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'Pending' => 'badge-warning',
            'Approved' => 'badge-success',
            'Ordered' => 'badge-info',
            'Partially Received' => 'badge-primary',
            'Received' => 'badge-success',
            'Rejected' => 'badge-danger',
            'Cancelled' => 'badge-secondary',
            default => 'badge-light',
        };
    }
    
    // ========================================
    // SCOPES
    // ========================================
    
    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'Approved');
    }
    
    public function scopeCritical($query)
    {
        return $query->where('priority', 'Critical');
    }
}