<!-- resources/views/admin/admin_restockReceipts.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Receipts - Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .container { 
            margin-left: 260px;
            padding: 20px;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-header h1 { font-size: 28px; color: #1a202c; }
        
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #3182ce;
        }
        
        .stat-card .icon { font-size: 32px; margin-bottom: 10px; }
        .stat-card .label { font-size: 13px; color: #718096; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .stat-card .value { font-size: 32px; font-weight: 700; color: #2d3748; }
        .stat-card .subtext { font-size: 13px; color: #a0aec0; margin-top: 4px; }
        
        .filter-bar {
            background: white;
            padding: 16px 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            align-items: center;
        }
        
        .filter-bar select {
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            min-width: 180px;
        }
        
        .receipts-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .section-header {
            padding: 20px;
            background: #f7fafc;
            border-bottom: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-header h2 { font-size: 20px; color: #2d3748; margin: 0; }
        
        table { width: 100%; border-collapse: collapse; }
        
        th {
            background: #f7fafc;
            padding: 16px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }
        
        td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #2d3748;
        }
        
        tr:hover { background: #f7fafc; }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-accepted { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-on-hold { background: #fed7aa; color: #92400e; }
        
        .btn {
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 13px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-view { background: #3b82f6; color: white; }
        .btn-view:hover { background: #2563eb; }
        .btn-export { background: #10b981; color: white; }
        .btn-export:hover { background: #059669; }
        
        .quality-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #9ca3af;
        }
        
        .empty-state .icon { font-size: 64px; margin-bottom: 16px; }
        .empty-state h3 { font-size: 18px; margin-bottom: 8px; color: #6b7280; }
        
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            margin-top: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .pagination button {
            padding: 8px 16px;
            border: 2px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .pagination button:hover:not(:disabled) { background: #f7fafc; }
        .pagination button:disabled { opacity: 0.5; cursor: not-allowed; }
        
        .on-hold-row { background: #fffbeb !important; }
        .on-hold-row:hover { background: #fef3c7 !important; }
        
        @media (max-width: 1024px) {
            .container { margin-left: 220px; }
        }
        
        @media (max-width: 768px) {
            .container { 
                margin-left: 0; 
                padding: 15px;
                margin-top: 60px;
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
            <a href="route('admin.pharmacy-inventory.index')">Pharmacy</a>
            <span>/</span>
            <span>Stock Receipts</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>📥 Stock Receipts</h1>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.restock.index') }}" class="btn btn-view">← Back to Restock</a>
                <button class="btn btn-export" onclick="window.print()">🖨️ Print</button>
            </div>
        </div>

        <!-- Quality Warning (if there are items on hold) -->
        @if($stats['on_hold'] > 0)
        <div class="quality-warning">
            <span style="font-size: 24px;">⚠️</span>
            <div>
                <strong>Quality Issues Detected:</strong> {{ $stats['on_hold'] }} receipt(s) are on hold pending quality review.
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="icon">📦</div>
                <div class="label">Total Received (30 Days)</div>
                <div class="value">{{ $stats['total_received'] }}</div>
                <div class="subtext">Stock receipts</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="icon">⚠️</div>
                <div class="label">Quality On Hold</div>
                <div class="value">{{ $stats['on_hold'] }}</div>
                <div class="subtext">Pending review</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="icon">💰</div>
                <div class="label">Total Value (30 Days)</div>
                <div class="value">RM {{ number_format($stats['total_value'], 2) }}</div>
                <div class="subtext">Inventory received</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #8b5cf6;">
                <div class="icon">📊</div>
                <div class="label">Average Receipt Value</div>
                <div class="value">RM {{ $stats['total_received'] > 0 ? number_format($stats['total_value'] / $stats['total_received'], 2) : '0.00' }}</div>
                <div class="subtext">Per receipt</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.restock.receipts') }}" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                <select name="quality_status" onchange="this.form.submit()">
                    <option value="">All Quality Status</option>
                    <option value="Accepted" {{ request('quality_status') == 'Accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="On Hold" {{ request('quality_status') == 'On Hold' ? 'selected' : '' }}>On Hold</option>
                    <option value="Rejected" {{ request('quality_status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                
                @if(request('quality_status'))
                    <a href="{{ route('admin.restock.receipts') }}" class="btn" style="background: #f3f4f6; color: #374151;">
                        Clear Filter
                    </a>
                @endif
            </form>
        </div>

        <!-- Receipts Table -->
        <div class="receipts-section">
            <div class="section-header">
                <h2>Stock Receipt Records</h2>
                <span style="color: #6b7280; font-size: 14px;">
                    Showing {{ $receipts->firstItem() ?? 0 }}–{{ $receipts->lastItem() ?? 0 }} of {{ $receipts->total() }}
                </span>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Medicine</th>
                        <th>Quantity Received</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th>Supplier</th>
                        <th>Total Cost</th>
                        <th>Quality Status</th>
                        <th>Received Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr class="{{ $receipt->quality_status == 'On Hold' ? 'on-hold-row' : '' }}">
                        <td><strong>{{ $receipt->receipt_number }}</strong></td>
                        <td>
                            <strong>{{ $receipt->medicine->medicine_name }}</strong><br>
                            <small style="color: #6b7280;">{{ $receipt->medicine->form }} {{ $receipt->medicine->strength }}</small>
                        </td>
                        <td><strong>{{ number_format($receipt->quantity_received) }}</strong> units</td>
                        <td>{{ $receipt->batch_number }}</td>
                        <td>
                            @php
                                $daysUntilExpiry = now()->diffInDays($receipt->expiry_date, false);
                                $expiryColor = $daysUntilExpiry < 90 ? '#ef4444' : ($daysUntilExpiry < 180 ? '#f59e0b' : '#10b981');
                            @endphp
                            <span style="color: {{ $expiryColor }}; font-weight: 600;">
                                {{ $receipt->expiry_date->format('M d, Y') }}
                            </span>
                            @if($daysUntilExpiry < 365)
                                <br><small style="color: {{ $expiryColor }};">{{ round($daysUntilExpiry / 30) }} months</small>
                            @endif
                        </td>
                        <td>{{ $receipt->supplier }}</td>
                        <td><strong style="color: #10b981;">RM {{ number_format($receipt->total_cost, 2) }}</strong></td>
                        <td>
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $receipt->quality_status)) }}">
                                {{ $receipt->quality_status }}
                            </span>
                        </td>
                        <td>
                            {{ $receipt->received_at->format('M d, Y') }}<br>
                            <small style="color: #9ca3af;">{{ $receipt->received_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.restock.receipts.show', $receipt->receipt_id) }}" class="btn btn-view">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <div class="icon">📥</div>
                                <h3>No Stock Receipts Found</h3>
                                <p>No receipts match your current filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($receipts->hasPages())
        <div class="pagination">
            <p style="color: #6b7280;">
                Showing {{ $receipts->firstItem() }}–{{ $receipts->lastItem() }} of {{ $receipts->total() }} receipts
            </p>
            <div style="display: flex; gap: 8px;">
                @if($receipts->onFirstPage())
                    <button disabled>« Prev</button>
                @else
                    <a href="{{ $receipts->previousPageUrl() }}">
                        <button>« Prev</button>
                    </a>
                @endif

                @if($receipts->hasMorePages())
                    <a href="{{ $receipts->nextPageUrl() }}">
                        <button>Next »</button>
                    </a>
                @else
                    <button disabled>Next »</button>
                @endif
            </div>
        </div>
        @endif
    </div>
</body>
</html>