<!--resources/views/admin/pharmacy-inventory-details.blade.php-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $medicine->medicine_name }} - Medicine Details</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_dashboard.css'])
    <style>
        .breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-bottom: 20px;
            font-size: 14px;
            color: #64748b;
        }
        
        .breadcrumb a {
            color: #3182ce;
            text-decoration: none;
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .header-left h1 {
            font-size: 28px;
            color: #1a202c;
            margin: 0 0 8px 0;
        }
        
        .header-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-top: 10px;
        }
        
        .meta-badge {
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        
        .badge-controlled {
            background: #fee;
            color: #c53030;
        }
        
        .badge-prescription {
            background: #ebf4ff;
            color: #2c5282;
        }
        
        .badge-category {
            background: #e6fffa;
            color: #047857;
        }
        
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
        
        .btn-primary {
            background: #3182ce;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2c5282;
        }
        
        .btn-secondary {
            background: #e2e8f0;
            color: #2d3748;
        }
        
        .btn-secondary:hover {
            background: #cbd5e0;
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c53030;
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
        
        .detail-row:last-child {
            border-bottom: none;
        }
        
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
        
        .stock-indicator {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            color: white;
            margin-bottom: 20px;
        }
        
        .stock-circle {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }
        
        .stock-info h4 {
            margin: 0 0 5px 0;
            font-size: 18px;
        }
        
        .stock-info p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .alert-box {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .alert-danger {
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            color: #721c24;
        }
        
        .alert-success {
            background: #d4edda;
            border-left: 4px solid #28a745;
            color: #155724;
        }
        
        .usage-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #3182ce;
            margin: 10px 0;
        }
        
        .stat-card .label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .history-table {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .history-table h3 {
            font-size: 18px;
            color: #2d3748;
            margin: 0 0 20px 0;
        }
        
        .movement-badge-in {
            background: #c6f6d5;
            color: #22543d;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .movement-badge-out {
            background: #bee3f8;
            color: #2c5282;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .movement-badge-dispensed {
            background: #feebc8;
            color: #7c2d12;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .movement-badge-expired {
            background: #fed7d7;
            color: #742a2a;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .quantity-positive {
            color: #22543d;
            font-weight: 700;
        }
        
        .quantity-negative {
            color: #c53030;
            font-weight: 700;
        }
    </style>
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
        <span>/</span>
        <a href="{{ route('admin.pharmacy-inventory.index') }}">Pharmacy Inventory</a>
        <span>/</span>
        <span>{{ $medicine->medicine_name }}</span>
    </div>

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>{{ $medicine->medicine_name }}</h1>
            <div class="header-meta">
                @if($medicine->generic_name)
                    <span style="color: #718096; font-size: 15px;">
                        Generic: <strong>{{ $medicine->generic_name }}</strong>
                    </span>
                @endif
                @if($medicine->brand_name)
                    <span style="color: #718096; font-size: 15px;">
                        Brand: <strong>{{ $medicine->brand_name }}</strong>
                    </span>
                @endif
            </div>
            <div class="header-meta">
                <span class="meta-badge badge-category">{{ $medicine->category }}</span>
                @if($medicine->requires_prescription)
                    <span class="meta-badge badge-prescription">📋 Prescription Required</span>
                @endif
                @if($medicine->is_controlled_substance)
                    <span class="meta-badge badge-controlled">🔒 Controlled Substance</span>
                @endif
            </div>
        </div>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="window.print()">🖨️ Print</button>
            <button class="btn btn-primary" onclick="alert('Edit functionality coming soon')">✏️ Edit</button>
            <a href="{{ route('admin.pharmacy-inventory.index') }}" class="btn btn-secondary">← Back to Inventory</a>
        </div>
    </div>

    <!-- Alerts -->
    @php
        $nextBatch = $medicine->getNextExpiringBatch();
    @endphp

    @if($medicine->isExpired())
        <div class="alert-box alert-danger">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong>EXPIRED MEDICINE</strong> - This medicine expired on 
                {{ $nextBatch ? $nextBatch->expiry_date->format('M d, Y') : 'N/A' }}. 
                It should be removed from inventory immediately.
            </div>
        </div>
    @elseif($medicine->isExpiringCritical())
        <div class="alert-box alert-danger">
            <span style="font-size: 24px;">🚨</span>
            <div>
                <strong>CRITICAL EXPIRY WARNING</strong> - This medicine will expire in {{ $medicine->getDaysUntilExpiry() }} days 
                ({{ $nextBatch ? $nextBatch->expiry_date->format('M d, Y') : 'N/A' }}). Immediate action required.
            </div>
        </div>
    @elseif($medicine->isExpiringSoon())
        <div class="alert-box alert-warning">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong>EXPIRY WARNING</strong> - This medicine will expire in approximately {{ $medicine->getMonthsUntilExpiry() }} months 
                ({{ $nextBatch ? $nextBatch->expiry_date->format('M d, Y') : 'N/A' }}).
            </div>
        </div>
    @endif

    @if($medicine->status == 'Out of Stock')
        <div class="alert-box alert-danger">
            <span style="font-size: 24px;">📦</span>
            <div>
                <strong>OUT OF STOCK</strong> - This medicine is currently unavailable. Reorder immediately.
            </div>
        </div>
    @elseif($medicine->status == 'Low Stock')
        <div class="alert-box alert-warning">
            <span style="font-size: 24px;">📉</span>
            <div>
                <strong>LOW STOCK WARNING</strong> - Current stock ({{ $medicine->quantity_in_stock }}) is below reorder level 
                ({{ $medicine->reorder_level }}). Consider restocking soon.
            </div>
        </div>
    @endif

    <!-- Stock Indicator -->
    <div class="stock-indicator">
        <div class="stock-circle">
            {{ $medicine->quantity_in_stock }}
        </div>
        <div class="stock-info">
            <h4>Current Stock Level</h4>
            <p>Reorder Level: {{ $medicine->reorder_level }} units | Status: {{ $medicine->status }}</p>
        </div>
    </div>

    <!-- Usage Statistics -->
    <div class="usage-stats">
        <div class="stat-card">
            <div class="label">Total Prescriptions</div>
            <div class="number">{{ $prescriptionCount }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Stock Movements</div>
            <div class="number">{{ $stockHistory->total() }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Days Until Expiry</div>
            <div class="number" style="color: {{ $medicine->getDaysUntilExpiry() < 90 ? '#e53e3e' : '#48bb78' }}">
                {{ $medicine->isExpired() ? 'EXPIRED' : $medicine->getDaysUntilExpiry() }}
            </div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="details-grid">
        <!-- Medicine Information -->
        <div class="detail-card">
            <h3>💊 Medicine Information</h3>
            <div class="detail-row">
                <span class="detail-label">Form</span>
                <span class="detail-value">{{ $medicine->form }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Strength</span>
                <span class="detail-value">{{ $medicine->strength }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Unit Price</span>
                <span class="detail-value">RM {{ number_format($medicine->unit_price, 2) }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Total Value</span>
                <span class="detail-value" style="color: #3182ce;">
                    RM {{ number_format($medicine->quantity_in_stock * $medicine->unit_price, 2) }}
                </span>
            </div>
        </div>

        <!-- Stock Information -->
        <div class="detail-card">
            <h3>📦 Stock Information</h3>
            <div class="detail-row">
                <span class="detail-label">Batch Number</span>
                <span class="detail-value">{{ $medicine->batch_number ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Supplier</span>
                <span class="detail-value">{{ $medicine->supplier ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Manufacture Date</span>
                <span class="detail-value">
                    {{ $medicine->manufacture_date ? $medicine->manufacture_date->format('M d, Y') : 'N/A' }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Next Expiry Date</span>
                <span class="detail-value" style="color: {{ $medicine->isExpiringCritical() ? '#e53e3e' : '#2d3748' }}">
                    {{ $medicine->getNextExpiringBatch() ? $medicine->getNextExpiringBatch()->expiry_date->format('M d, Y') : 'N/A' }}
                </span>
            </div>
        </div>

        <!-- Storage & Handling -->
        <div class="detail-card">
            <h3>🌡️ Storage & Handling</h3>
            <div class="detail-row">
                <span class="detail-label">Storage Conditions</span>
                <span class="detail-value">{{ $medicine->storage_conditions ?? 'Standard' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Requires Prescription</span>
                <span class="detail-value">{{ $medicine->requires_prescription ? 'Yes' : 'No' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Controlled Substance</span>
                <span class="detail-value">{{ $medicine->is_controlled_substance ? 'Yes' : 'No' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Last Updated</span>
                <span class="detail-value">{{ $medicine->updated_at->format('M d, Y H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Stock Movement History -->
    <div class="history-table">
        <h3>📊 Stock Movement History</h3>
        <table>
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Movement Type</th>
                    <th>Quantity</th>
                    <th>Balance After</th>
                    <th>Performed By</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stockHistory as $movement)
                <tr>
                    <td>{{ $movement->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        @php
                            $badgeClass = match($movement->movement_type) {
                                'Stock In' => 'movement-badge-in',
                                'Dispensed' => 'movement-badge-dispensed',
                                'Expired/Damaged' => 'movement-badge-expired',
                                default => 'movement-badge-out'
                            };
                        @endphp
                        <span class="{{ $badgeClass }}">{{ $movement->movement_type }}</span>
                    </td>
                    <td class="{{ $movement->quantity > 0 ? 'quantity-positive' : 'quantity-negative' }}">
                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                    </td>
                    <td><strong>{{ $movement->balance_after }}</strong></td>
                    <td>{{ $movement->pharmacist->user->name ?? 'System' }}</td>
                    <td>{{ $movement->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 30px; color: #a0aec0;">
                        No stock movement history available
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($stockHistory->hasPages())
        <div class="pagination" style="margin-top: 20px;">
            <p>
                Showing {{ $stockHistory->firstItem() }}–{{ $stockHistory->lastItem() }}
                of {{ $stockHistory->total() }} movements
            </p>

            <div class="pages">
                @if ($stockHistory->onFirstPage())
                    <button disabled>&laquo; Prev</button>
                @else
                    <a href="{{ $stockHistory->previousPageUrl() }}">
                        <button>&laquo; Prev</button>
                    </a>
                @endif

                @foreach ($stockHistory->getUrlRange(1, $stockHistory->lastPage()) as $page => $url)
                    @if ($page == $stockHistory->currentPage())
                        <button class="active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}">
                            <button>{{ $page }}</button>
                        </a>
                    @endif
                @endforeach

                @if ($stockHistory->hasMorePages())
                    <a href="{{ $stockHistory->nextPageUrl() }}">
                        <button>Next &raquo;</button>
                    </a>
                @else
                    <button disabled>Next &raquo;</button>
                @endif
            </div>
        </div>
        @endif
    </div>
</div>

</body>
</html>