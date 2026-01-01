<!-- resources\views\pharmacist\pharmacist_receipts.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Receipts - Pharmacist</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: #f5f7fa;
            margin: 0;
            padding: 0;
        }
        
        .container { 
            max-width: 1600px; 
            margin: 0 auto; 
            padding: 30px;
            margin-left: 270px; /* Account for sidebar */
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .page-header h1 { 
            font-size: 32px; 
            color: #1a202c;
            font-weight: 700;
        }
        
        .btn {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary { 
            background: #3b82f6; 
            color: white;
            box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        .btn-primary:hover { 
            background: #2563eb;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(59, 130, 246, 0.4);
        }
        
        /* Success/Error Messages */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }
        
        .alert-error {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 30px;
        }
        
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
        
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }
        
        .stat-card .icon { 
            font-size: 36px; 
            margin-bottom: 12px;
            display: block;
        }
        
        .stat-card .label { 
            font-size: 12px; 
            color: #6b7280; 
            text-transform: uppercase; 
            margin-bottom: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        .stat-card .value { 
            font-size: 28px; 
            font-weight: 700; 
            color: #1a202c;
            line-height: 1;
        }
        
        /* Color variants for stat cards */
        .stat-card-green { border-left-color: #10b981; }
        .stat-card-blue { border-left-color: #3b82f6; }
        .stat-card-orange { border-left-color: #f59e0b; }
        .stat-card-purple { border-left-color: #8b5cf6; }
        
        /* Table Container */
        .receipts-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .table-header {
            padding: 20px 24px;
            border-bottom: 2px solid #f3f4f6;
        }
        
        .table-header h2 {
            font-size: 18px;
            font-weight: 600;
            color: #1a202c;
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse;
        }
        
        th {
            background: #f9fafb;
            padding: 14px 20px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        td {
            padding: 16px 20px;
            border-bottom: 1px solid #f3f4f6;
            font-size: 14px;
            color: #374151;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        tbody tr {
            transition: background-color 0.15s;
        }
        
        tbody tr:hover { 
            background: #f9fafb;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 16px;
            font-size: 11px;
            font-weight: 700;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .badge-accepted { 
            background: #d1fae5; 
            color: #065f46;
        }
        
        .badge-rejected { 
            background: #fee2e2; 
            color: #991b1b;
        }
        
        .badge-on-hold { 
            background: #fed7aa; 
            color: #92400e;
        }
        
        .action-link {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: color 0.2s;
        }
        
        .action-link:hover { 
            color: #2563eb;
        }
        
        .medicine-name {
            font-weight: 600;
            color: #1a202c;
            display: block;
            margin-bottom: 4px;
        }
        
        .medicine-details {
            font-size: 12px;
            color: #9ca3af;
        }
        
        .empty-state {
            text-align: center; 
            padding: 80px 20px; 
            color: #9ca3af;
        }
        
        .empty-state-icon {
            font-size: 64px; 
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state-text {
            font-size: 16px;
            color: #6b7280;
        }
        
        /* Pagination */
        .pagination {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            padding: 20px 24px; 
            background: white; 
            margin-top: 20px; 
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }
        
        .pagination-buttons {
            display: flex; 
            gap: 8px;
        }
        
        .pagination-btn {
            padding: 10px 18px; 
            border: 2px solid #e5e7eb; 
            background: white; 
            border-radius: 8px; 
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            transition: all 0.2s;
        }
        
        .pagination-btn:hover:not(:disabled) {
            border-color: #3b82f6;
            color: #3b82f6;
            background: #eff6ff;
        }
        
        .pagination-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }
        
        /* Expiry date colors */
        .expiry-critical { color: #ef4444; font-weight: 700; }
        .expiry-warning { color: #f59e0b; font-weight: 600; }
        .expiry-safe { color: #10b981; font-weight: 600; }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>📥 Stock Receipts</h1>
            <a href="{{ route('pharmacist.receipts.create') }}" class="btn btn-primary">
                <span>+</span>
                <span>Record New Receipt</span>
            </a>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <span style="font-size: 20px;">✅</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">
                <span style="font-size: 20px;">❌</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card stat-card-green">
                <span class="icon">📦</span>
                <div class="label">Total Received</div>
                <div class="value">{{ $receipts->total() }}</div>
            </div>
            
            <div class="stat-card stat-card-blue">
                <span class="icon">✓</span>
                <div class="label">This Month</div>
                <div class="value">{{ $receipts->where('received_at', '>=', now()->startOfMonth())->count() }}</div>
            </div>
            
            <div class="stat-card stat-card-orange">
                <span class="icon">⚠️</span>
                <div class="label">Quality On Hold</div>
                <div class="value">{{ $receipts->where('quality_status', 'On Hold')->count() }}</div>
            </div>
            
            <div class="stat-card stat-card-purple">
                <span class="icon">💰</span>
                <div class="label">Total Value (Month)</div>
                <div class="value">RM {{ number_format($receipts->where('received_at', '>=', now()->startOfMonth())->sum('total_cost'), 2) }}</div>
            </div>
        </div>

        <!-- Receipts Table -->
        <div class="receipts-table">
            <div class="table-header">
                <h2>Recent Stock Receipts</h2>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Batch Number</th>
                        <th>Expiry Date</th>
                        <th>Quality Status</th>
                        <th>Received Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($receipts as $receipt)
                    <tr>
                        <td><strong>{{ $receipt->receipt_number }}</strong></td>
                        <td>
                            <span class="medicine-name">{{ $receipt->medicine->medicine_name }}</span>
                            <span class="medicine-details">{{ $receipt->medicine->form }} {{ $receipt->medicine->strength }}</span>
                        </td>
                        <td><strong>{{ $receipt->quantity_received }}</strong> units</td>
                        <td>{{ $receipt->batch_number }}</td>
                        <td>
                            @php
                                $daysUntilExpiry = now()->diffInDays($receipt->expiry_date, false);
                                $expiryClass = $daysUntilExpiry < 90 ? 'expiry-critical' : ($daysUntilExpiry < 180 ? 'expiry-warning' : 'expiry-safe');
                            @endphp
                            <span class="{{ $expiryClass }}">
                                {{ $receipt->expiry_date->format('M d, Y') }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $receipt->quality_status)) }}">
                                {{ $receipt->quality_status }}
                            </span>
                        </td>
                        <td>{{ $receipt->received_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('pharmacist.receipts.show', $receipt->receipt_id) }}" class="action-link">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="empty-state">
                            <div class="empty-state-icon">📥</div>
                            <p class="empty-state-text">No stock receipts recorded yet</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($receipts->hasPages())
        <div class="pagination">
            <p class="pagination-info">
                Showing {{ $receipts->firstItem() }}–{{ $receipts->lastItem() }} of {{ $receipts->total() }}
            </p>
            <div class="pagination-buttons">
                @if($receipts->onFirstPage())
                    <button disabled class="pagination-btn">« Previous</button>
                @else
                    <a href="{{ $receipts->previousPageUrl() }}">
                        <button class="pagination-btn">« Previous</button>
                    </a>
                @endif

                @if($receipts->hasMorePages())
                    <a href="{{ $receipts->nextPageUrl() }}">
                        <button class="pagination-btn">Next »</button>
                    </a>
                @else
                    <button disabled class="pagination-btn">Next »</button>
                @endif
            </div>
        </div>
        @endif
    </div>
</body>
</html>