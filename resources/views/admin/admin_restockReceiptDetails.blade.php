<!-- resources/views/admin/admin_restockReceiptDetails.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt Details - {{ $receipt->receipt_number }}</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .container { 
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        
        .breadcrumb a { color: #3182ce; text-decoration: none; }
        .breadcrumb a:hover { text-decoration: underline; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .header-left h1 { font-size: 28px; color: #1a202c; margin: 0 0 8px 0; }
        
        .header-meta {
            display: flex;
            gap: 12px;
            margin-top: 10px;
        }
        
        .badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge-accepted { background: #d1fae5; color: #065f46; }
        .badge-on-hold { background: #fed7aa; color: #92400e; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        
        .header-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-secondary { background: #e2e8f0; color: #2d3748; }
        .btn-secondary:hover { background: #cbd5e0; }
        
        .alert-box {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }
        
        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            color: #991b1b;
        }
        
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .detail-card h3 {
            font-size: 16px;
            color: #2d3748;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f7fafc;
        }
        
        .detail-row:last-child { border-bottom: none; }
        
        .detail-label {
            color: #718096;
            font-size: 14px;
        }
        
        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }
        
        .value-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 24px;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-radius: 12px;
            margin-bottom: 20px;
        }
        
        .value-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(16, 185, 129, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
            color: #065f46;
        }
        
        .value-info h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
            color: #065f46;
        }
        
        .value-info p {
            margin: 0;
            color: #047857;
            font-size: 14px;
        }
        
        .notes-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .notes-section h3 {
            font-size: 18px;
            color: #2d3748;
            margin: 0 0 16px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .notes-content {
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            color: #2d3748;
            line-height: 1.6;
        }
        
        .timeline-section {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .timeline-section h3 {
            font-size: 18px;
            color: #2d3748;
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .timeline-item {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .timeline-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .timeline-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #d1fae5;
            color: #065f46;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        
        .timeline-content {
            flex: 1;
        }
        
        .timeline-title {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 4px;
        }
        
        .timeline-meta {
            font-size: 13px;
            color: #718096;
        }
        
        @media (max-width: 1024px) {
            .container { margin-left: 220px; }
        }
        
        @media (max-width: 768px) {
            .container { 
                margin-left: 0; 
                padding: 15px;
                margin-top: 60px;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')
    
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
            <span>/</span>
            <a href="{{ route('admin.pharmacy-inventory.index') }}">Pharmacy</a>
            <span>/</span>
            <a href="{{ route('admin.restock.receipts') }}">Stock Receipts</a>
            <span>/</span>
            <span>{{ $receipt->receipt_number }}</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>📥 Stock Receipt: {{ $receipt->receipt_number }}</h1>
                <div class="header-meta">
                    <span class="badge badge-{{ strtolower(str_replace(' ', '-', $receipt->quality_status)) }}">
                        {{ $receipt->quality_status }}
                    </span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
                <a href="{{ route('admin.restock.receipts') }}" class="btn btn-secondary">← Back to Receipts</a>
            </div>
        </div>

        <!-- Quality Status Alerts -->
        @if($receipt->quality_status === 'On Hold')
        <div class="alert-box alert-warning">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong>Quality Hold:</strong> This stock receipt is currently on hold pending quality inspection.
                @if($receipt->quality_notes)
                    <br>Notes: {{ $receipt->quality_notes }}
                @endif
            </div>
        </div>
        @elseif($receipt->quality_status === 'Rejected')
        <div class="alert-box alert-danger">
            <span style="font-size: 24px;">❌</span>
            <div>
                <strong>Quality Rejected:</strong> This stock was rejected and not added to inventory.
                @if($receipt->quality_notes)
                    <br>Reason: {{ $receipt->quality_notes }}
                @endif
            </div>
        </div>
        @else
        <div class="alert-box alert-success">
            <span style="font-size: 24px;">✅</span>
            <div>
                <strong>Quality Accepted:</strong> This stock passed quality inspection and was added to inventory.
            </div>
        </div>
        @endif

        <!-- Expiry Warning -->
        @php
            $daysUntilExpiry = now()->diffInDays($receipt->expiry_date, false);
        @endphp
        @if($daysUntilExpiry < 180)
        <div class="alert-box alert-warning">
            <span style="font-size: 24px;">⏰</span>
            <div>
                <strong>Short Expiry Notice:</strong> This batch will expire in approximately {{ round($daysUntilExpiry / 30) }} months ({{ $receipt->expiry_date->format('M d, Y') }}).
            </div>
        </div>
        @endif

        <!-- Receipt Value Indicator -->
        <div class="value-indicator">
            <div class="value-circle">
                RM {{ number_format($receipt->total_cost, 0) }}
            </div>
            <div class="value-info">
                <h4>Total Receipt Value</h4>
                <p>{{ number_format($receipt->quantity_received) }} units × RM {{ number_format($receipt->unit_cost, 2) }} per unit</p>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="details-grid">
            <!-- Medicine Information -->
            <div class="detail-card">
                <h3>💊 Medicine Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Medicine Name</span>
                    <span class="detail-value">{{ $receipt->medicine->medicine_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span class="detail-value">{{ $receipt->medicine->category }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Form & Strength</span>
                    <span class="detail-value">{{ $receipt->medicine->form }} {{ $receipt->medicine->strength }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Current Stock Level</span>
                    <span class="detail-value">{{ number_format($receipt->medicine->quantity_in_stock) }} units</span>
                </div>
            </div>

            <!-- Receipt Details -->
            <div class="detail-card">
                <h3>📦 Receipt Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Quantity Received</span>
                    <span class="detail-value" style="color: #10b981;">{{ number_format($receipt->quantity_received) }} units</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Batch Number</span>
                    <span class="detail-value">{{ $receipt->batch_number }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Unit Cost</span>
                    <span class="detail-value">RM {{ number_format($receipt->unit_cost, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Cost</span>
                    <span class="detail-value" style="color: #10b981; font-size: 16px;">
                        RM {{ number_format($receipt->total_cost, 2) }}
                    </span>
                </div>
            </div>

            <!-- Supplier & Dates -->
            <div class="detail-card">
                <h3>🏢 Supplier & Dates</h3>
                <div class="detail-row">
                    <span class="detail-label">Supplier</span>
                    <span class="detail-value">{{ $receipt->supplier }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Manufacture Date</span>
                    <span class="detail-value">{{ $receipt->manufacture_date ? $receipt->manufacture_date->format('M d, Y') : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Expiry Date</span>
                    <span class="detail-value" style="color: {{ $daysUntilExpiry < 180 ? '#f59e0b' : '#10b981' }};">
                        {{ $receipt->expiry_date->format('M d, Y') }}
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Shelf Life</span>
                    <span class="detail-value">{{ round($daysUntilExpiry / 30) }} months</span>
                </div>
            </div>

            <!-- Personnel & Processing -->
            <div class="detail-card">
                <h3>👥 Personnel & Status</h3>
                <div class="detail-row">
                    <span class="detail-label">Received By</span>
                    <span class="detail-value">{{ $receipt->receivedBy->user->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Received Date</span>
                    <span class="detail-value">{{ $receipt->received_at->format('M d, Y H:i') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Quality Status</span>
                    <span class="detail-value">{{ $receipt->quality_status }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Time Ago</span>
                    <span class="detail-value">{{ $receipt->received_at->diffForHumans() }}</span>
                </div>
            </div>
        </div>

        <!-- Related Restock Request -->
        @if($receipt->restockRequest)
        <div class="notes-section">
            <h3>🔗 Related Restock Request</h3>
            <div class="notes-content">
                <strong>Request Number:</strong> 
                <a href="{{ route('admin.restock.show', $receipt->restockRequest->request_id) }}" style="color: #3b82f6;">
                    {{ $receipt->restockRequest->request_number }}
                </a>
                <br>
                <strong>Requested Quantity:</strong> {{ number_format($receipt->restockRequest->quantity_requested) }} units
                <br>
                <strong>Priority:</strong> {{ $receipt->restockRequest->priority }}
            </div>
        </div>
        @endif

        <!-- Quality Notes -->
        @if($receipt->quality_notes)
        <div class="notes-section">
            <h3>📝 Quality Inspection Notes</h3>
            <div class="notes-content">
                {{ $receipt->quality_notes }}
            </div>
        </div>
        @endif

        <!-- Receipt Timeline -->
        <div class="timeline-section">
            <h3>📋 Receipt Processing Timeline</h3>
            
            <div class="timeline-item">
                <div class="timeline-icon">📦</div>
                <div class="timeline-content">
                    <div class="timeline-title">Stock Received</div>
                    <div class="timeline-meta">
                        {{ $receipt->received_at->format('F d, Y \a\t H:i') }} by {{ $receipt->receivedBy->user->name }}
                    </div>
                </div>
            </div>

            <div class="timeline-item">
                <div class="timeline-icon">🔍</div>
                <div class="timeline-content">
                    <div class="timeline-title">Quality Inspection</div>
                    <div class="timeline-meta">
                        Status: {{ $receipt->quality_status }}
                        @if($receipt->quality_notes)
                            - {{ Str::limit($receipt->quality_notes, 100) }}
                        @endif
                    </div>
                </div>
            </div>

            @if($receipt->quality_status === 'Accepted')
            <div class="timeline-item">
                <div class="timeline-icon">✅</div>
                <div class="timeline-content">
                    <div class="timeline-title">Added to Inventory</div>
                    <div class="timeline-meta">
                        {{ number_format($receipt->quantity_received) }} units successfully added to stock
                    </div>
                </div>
            </div>
            @endif

            <div class="timeline-item">
                <div class="timeline-icon">📊</div>
                <div class="timeline-content">
                    <div class="timeline-title">Financial Record</div>
                    <div class="timeline-meta">
                        Total value of RM {{ number_format($receipt->total_cost, 2) }} recorded in inventory valuation
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>