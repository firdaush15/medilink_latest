<!-- resources/views/pharmacist/pharmacist_inventoryStockHistory.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stock History - {{ $medicine->medicine_name }} - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_inventory.css'])
    <style>
        .history-container {
            background: white;
            border-radius: 12px;
            padding: 24px;
            margin-top: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .medicine-info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 24px;
        }
        
        .medicine-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-top: 16px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 18px;
            font-weight: 600;
        }
        
        .filter-bar {
            display: flex;
            gap: 12px;
            align-items: center;
            margin-bottom: 24px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .filter-bar select,
        .filter-bar input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .movements-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .movements-table thead {
            background: #f8f9fa;
        }
        
        .movements-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        .movements-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .movements-table tbody tr:hover {
            background: #f8f9fa;
        }
        
        .movement-type-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .movement-stock-in {
            background: #d4edda;
            color: #155724;
        }
        
        .movement-dispensed {
            background: #fff3cd;
            color: #856404;
        }
        
        .movement-expired {
            background: #f8d7da;
            color: #721c24;
        }
        
        .movement-damaged {
            background: #f5c6cb;
            color: #721c24;
        }
        
        .movement-returned {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .movement-adjustment {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .quantity-indicator {
            font-weight: 600;
            font-size: 16px;
        }
        
        .quantity-positive {
            color: #28a745;
        }
        
        .quantity-negative {
            color: #dc3545;
        }
        
        .balance-cell {
            font-weight: 600;
            color: #495057;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }
        
        .empty-state svg {
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 8px;
            color: #495057;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
        }
        
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .btn-export {
            background: #28a745;
            color: white;
        }
        
        .btn-export:hover {
            background: #218838;
        }
        
        .pagination-container {
            margin-top: 24px;
            display: flex;
            justify-content: center;
        }
        
        .batch-info {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>📊 Stock Movement History</h1>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Medicine Info Card -->
        <div class="medicine-info-card">
            <h2>{{ $medicine->medicine_name }}</h2>
            <div class="medicine-info-grid">
                <div class="info-item">
                    <span class="info-label">Generic Name</span>
                    <span class="info-value">{{ $medicine->generic_name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Category</span>
                    <span class="info-value">{{ $medicine->category }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Form</span>
                    <span class="info-value">{{ $medicine->form }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Strength</span>
                    <span class="info-value">{{ $medicine->strength }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Current Stock</span>
                    <span class="info-value">{{ $medicine->quantity_in_stock }} units</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">{{ $medicine->status }}</span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="{{ route('pharmacist.inventory') }}" class="btn btn-secondary">
                ← Back to Inventory
            </a>
            <a href="{{ route('pharmacist.inventory.show', $medicine->medicine_id) }}" class="btn btn-primary">
                👁️ View Details
            </a>
            <a href="{{ route('pharmacist.inventory.edit', $medicine->medicine_id) }}" class="btn btn-primary">
                ✏️ Edit Medicine
            </a>
            <button onclick="exportHistory()" class="btn btn-export">
                📥 Export History
            </button>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Movements</div>
                <div class="stat-value">{{ $movements->total() }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Stock In</div>
                <div class="stat-value">
                    +{{ $medicine->stockMovements()->where('movement_type', 'Stock In')->sum('quantity') }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Dispensed</div>
                <div class="stat-value">
                    {{ abs($medicine->stockMovements()->where('movement_type', 'Dispensed')->sum('quantity')) }}
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Expired/Damaged</div>
                <div class="stat-value">
                    {{ abs($medicine->stockMovements()->whereIn('movement_type', ['Expired', 'Damaged'])->sum('quantity')) }}
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" style="display: flex; gap: 12px; width: 100%;">
                <select name="movement_type" onchange="this.form.submit()">
                    <option value="">All Movement Types</option>
                    <option value="Stock In" {{ request('movement_type') == 'Stock In' ? 'selected' : '' }}>Stock In</option>
                    <option value="Dispensed" {{ request('movement_type') == 'Dispensed' ? 'selected' : '' }}>Dispensed</option>
                    <option value="Returned" {{ request('movement_type') == 'Returned' ? 'selected' : '' }}>Returned</option>
                    <option value="Expired" {{ request('movement_type') == 'Expired' ? 'selected' : '' }}>Expired</option>
                    <option value="Damaged" {{ request('movement_type') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                    <option value="Adjustment" {{ request('movement_type') == 'Adjustment' ? 'selected' : '' }}>Adjustment</option>
                </select>

                <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From Date" onchange="this.form.submit()">
                <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To Date" onchange="this.form.submit()">

                @if(request('movement_type') || request('date_from') || request('date_to'))
                <a href="{{ route('pharmacist.inventory.stock-history', $medicine->medicine_id) }}" class="btn btn-secondary" style="white-space: nowrap;">
                    ✖ Clear
                </a>
                @endif
            </form>
        </div>

        <!-- Movements Table -->
        <div class="history-container">
            @if($movements->count() > 0)
            <table class="movements-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Movement Type</th>
                        <th>Batch Number</th>
                        <th>Quantity</th>
                        <th>Balance After</th>
                        <th>Pharmacist</th>
                        <th>Notes</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                    <tr>
                        <td>
                            <div style="font-weight: 600;">{{ $movement->created_at->format('M d, Y') }}</div>
                            <div style="font-size: 12px; color: #6c757d;">{{ $movement->created_at->format('h:i A') }}</div>
                        </td>
                        <td>
                            <span class="movement-type-badge movement-{{ strtolower(str_replace(' ', '-', $movement->movement_type)) }}">
                                {{ $movement->movement_type }}
                            </span>
                        </td>
                        <td>
                            @if($movement->batch)
                                <div class="batch-info">
                                    <strong>{{ $movement->batch->batch_number }}</strong><br>
                                    <small>Exp: {{ $movement->batch->expiry_date->format('M d, Y') }}</small>
                                </div>
                            @else
                                <span class="text-muted">{{ $movement->batch_number ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td>
                            <span class="quantity-indicator {{ $movement->quantity > 0 ? 'quantity-positive' : 'quantity-negative' }}">
                                {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                            </span>
                        </td>
                        <td class="balance-cell">{{ $movement->balance_after }}</td>
                        <td>
                            @if($movement->pharmacist)
                                {{ $movement->pharmacist->user->name }}
                            @else
                                <span class="text-muted">System</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $movement->notes ?? '-' }}</small>
                        </td>
                        <td>
                            @if($movement->reference_number)
                                <small class="badge">{{ $movement->reference_number }}</small>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination-container">
                {{ $movements->appends(request()->query())->links() }}
            </div>
            @else
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3>No Stock Movements Found</h3>
                <p>There are no stock movements recorded for this medicine yet.</p>
            </div>
            @endif
        </div>
    </div>

    <script>
        function exportHistory() {
            const medicineId = {{ $medicine->medicine_id }};
            const params = new URLSearchParams(window.location.search);
            
            window.location.href = `/pharmacist/inventory/${medicineId}/stock-history/export?${params.toString()}`;
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(() => {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>