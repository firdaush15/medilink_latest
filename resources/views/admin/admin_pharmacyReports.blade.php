<!--resources/views/admin/pharmacy-reports.blade.php-->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy Reports - MediLink Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_dashboard.css'])
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin: 0;
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
        
        .report-filters {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .filter-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 6px;
        }
        
        .filter-input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card-large {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #3182ce;
        }
        
        .stat-card-large .icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .stat-card-large .label {
            font-size: 13px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .stat-card-large .value {
            font-size: 32px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 6px;
        }
        
        .stat-card-large .subtext {
            font-size: 13px;
            color: #a0aec0;
        }
        
        .report-section {
            background: white;
            padding: 28px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        
        .report-section h3 {
            font-size: 20px;
            color: #2d3748;
            margin: 0 0 20px 0;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .category-breakdown {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 16px;
        }
        
        .category-item {
            padding: 16px;
            background: #f7fafc;
            border-radius: 8px;
            border-left: 4px solid #3182ce;
        }
        
        .category-item .name {
            font-size: 14px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }
        
        .category-item .stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .category-item .count {
            font-size: 24px;
            font-weight: 700;
            color: #3182ce;
        }
        
        .category-item .stock {
            font-size: 13px;
            color: #718096;
        }
        
        .top-medicines-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .medicine-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px;
            background: #f7fafc;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .medicine-item:hover {
            background: #edf2f7;
            transform: translateX(4px);
        }
        
        .medicine-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .rank-badge {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        
        .medicine-details .name {
            font-size: 15px;
            font-weight: 600;
            color: #2d3748;
        }
        
        .medicine-details .category {
            font-size: 12px;
            color: #718096;
        }
        
        .dispense-count {
            font-size: 20px;
            font-weight: 700;
            color: #3182ce;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table th {
            background: #f7fafc;
            padding: 12px;
            text-align: left;
            font-size: 13px;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .summary-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
            color: #2d3748;
        }
        
        .summary-table tr:hover {
            background: #f7fafc;
        }
        
        .value-highlight {
            font-size: 18px;
            font-weight: 700;
            color: #3182ce;
        }
        
        .alert-panel {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border-left: 4px solid #e53e3e;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-panel h4 {
            margin: 0 0 10px 0;
            color: #c53030;
            font-size: 16px;
        }
        
        .alert-panel ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .alert-panel li {
            color: #742a2a;
            margin-bottom: 6px;
        }
        
        @media print {
            .header-actions, .report-filters, .btn {
                display: none !important;
            }
            
            .report-section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">
    <!-- Page Header -->
    <div class="page-header">
        <h1>📊 Pharmacy Inventory Reports</h1>
        <div class="header-actions">
            <button class="btn btn-secondary" onclick="window.print()">🖨️ Print Report</button>
            <a href="{{ route('admin.pharmacy-inventory.export') }}" class="btn btn-primary">📥 Export CSV</a>
            <a href="{{ route('admin.pharmacy-inventory.index') }}" class="btn btn-secondary">← Back</a>
        </div>
    </div>

    <!-- Report Date Range Filter -->
    <div class="report-filters">
        <h4 style="margin: 0 0 15px 0; color: #2d3748;">Report Parameters</h4>
        <div class="filter-row">
            <div class="filter-group">
                <label>From Date</label>
                <input type="date" class="filter-input" value="{{ now()->subMonth()->format('Y-m-d') }}">
            </div>
            <div class="filter-group">
                <label>To Date</label>
                <input type="date" class="filter-input" value="{{ now()->format('Y-m-d') }}">
            </div>
            <div class="filter-group">
                <label>Report Type</label>
                <select class="filter-input">
                    <option>Summary Report</option>
                    <option>Detailed Report</option>
                    <option>Financial Report</option>
                    <option>Expiry Report</option>
                </select>
            </div>
            <div class="filter-group" style="display: flex; align-items: flex-end;">
                <button class="btn btn-primary" style="width: 100%;">Generate Report</button>
            </div>
        </div>
    </div>

    <!-- Key Statistics Overview -->
    <div class="stats-overview">
        <div class="stat-card-large">
            <div class="icon">💊</div>
            <div class="label">Total Medicines</div>
            <div class="value">{{ $stats['total_medicines'] }}</div>
            <div class="subtext">Active: {{ $stats['active_medicines'] }} | Inactive: {{ $stats['total_medicines'] - $stats['active_medicines'] }}</div>
        </div>

        <div class="stat-card-large" style="border-left-color: #48bb78;">
            <div class="icon">💰</div>
            <div class="label">Total Inventory Value</div>
            <div class="value">RM {{ number_format($stats['total_inventory_value'] ?? 0, 2) }}</div>
            <div class="subtext">Based on current stock levels</div>
        </div>

        <div class="stat-card-large" style="border-left-color: #f59e0b;">
            <div class="icon">⚠️</div>
            <div class="label">Low Stock Alerts</div>
            <div class="value">{{ $stats['low_stock'] }}</div>
            <div class="subtext">Requires reordering attention</div>
        </div>

        <div class="stat-card-large" style="border-left-color: #ef4444;">
            <div class="icon">🚨</div>
            <div class="label">Critical Issues</div>
            <div class="value">{{ $stats['out_of_stock'] + $stats['expired'] }}</div>
            <div class="subtext">Out: {{ $stats['out_of_stock'] }} | Expired: {{ $stats['expired'] }}</div>
        </div>
    </div>

    <!-- Critical Alerts Panel -->
    @if($stats['out_of_stock'] > 0 || $stats['expired'] > 0)
    <div class="alert-panel">
        <h4>🚨 Critical Inventory Alerts</h4>
        <ul>
            @if($stats['out_of_stock'] > 0)
                <li><strong>{{ $stats['out_of_stock'] }}</strong> medicines are completely out of stock and unavailable for dispensing</li>
            @endif
            @if($stats['expired'] > 0)
                <li><strong>{{ $stats['expired'] }}</strong> medicines have expired and must be removed from inventory</li>
            @endif
            @if($stats['low_stock'] > 0)
                <li><strong>{{ $stats['low_stock'] }}</strong> medicines are below reorder level and need restocking</li>
            @endif
        </ul>
    </div>
    @endif

    <!-- Category Breakdown -->
    <div class="report-section">
        <h3>📦 Inventory by Category</h3>
        <div class="category-breakdown">
            @foreach($stats['by_category'] as $category)
            <div class="category-item">
                <div class="name">{{ $category->category }}</div>
                <div class="stats">
                    <div class="count">{{ $category->count }}</div>
                    <div class="stock">{{ number_format($category->total_stock) }} units</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Top Dispensed Medicines -->
    <div class="report-section">
        <h3>🏆 Top 10 Most Dispensed Medicines</h3>
        <div class="top-medicines-list">
            @forelse($stats['top_dispensed'] as $index => $item)
            <div class="medicine-item">
                <div class="medicine-info">
                    <div class="rank-badge">{{ $index + 1 }}</div>
                    <div class="medicine-details">
                        <div class="name">{{ $item->medicine->medicine_name }}</div>
                        <div class="category">{{ $item->medicine->category }} • {{ $item->medicine->form }}</div>
                    </div>
                </div>
                <div class="dispense-count">{{ $item->dispense_count }} times</div>
            </div>
            @empty
            <p style="text-align: center; color: #a0aec0; padding: 20px;">No dispensing data available</p>
            @endforelse
        </div>
    </div>

    <!-- Stock Movement Summary -->
    <div class="report-section">
        <h3>📊 Stock Movement Summary (This Month)</h3>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Value</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total Movements</strong></td>
                    <td><span class="value-highlight">{{ $stats['movements_this_month'] }}</span></td>
                    <td>All inventory transactions this month</td>
                </tr>
                <tr>
                    <td><strong>Total Categories</strong></td>
                    <td><span class="value-highlight">{{ $stats['by_category']->count() }}</span></td>
                    <td>Distinct medicine categories</td>
                </tr>
                <tr>
                    <td><strong>Active Inventory Items</strong></td>
                    <td><span class="value-highlight">{{ $stats['active_medicines'] }}</span></td>
                    <td>Medicines currently available for dispensing</td>
                </tr>
                <tr>
                    <td><strong>Reorder Required</strong></td>
                    <td><span class="value-highlight" style="color: #f59e0b;">{{ $stats['low_stock'] }}</span></td>
                    <td>Medicines below minimum stock level</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Recommendations -->
    <div class="report-section">
        <h3>💡 Recommendations & Action Items</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            @if($stats['out_of_stock'] > 0)
            <div style="padding: 14px; background: #fee2e2; border-left: 4px solid #dc2626; border-radius: 6px;">
                <strong style="color: #991b1b;">URGENT:</strong>
                <span style="color: #7f1d1d;">{{ $stats['out_of_stock'] }} medicines are out of stock. Immediate restocking required to maintain service continuity.</span>
            </div>
            @endif

            @if($stats['low_stock'] > 0)
            <div style="padding: 14px; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 6px;">
                <strong style="color: #92400e;">ACTION NEEDED:</strong>
                <span style="color: #78350f;">{{ $stats['low_stock'] }} medicines are below reorder level. Review and place orders with suppliers.</span>
            </div>
            @endif

            @if($stats['expired'] > 0)
            <div style="padding: 14px; background: #fee2e2; border-left: 4px solid #dc2626; border-radius: 6px;">
                <strong style="color: #991b1b;">COMPLIANCE:</strong>
                <span style="color: #7f1d1d;">{{ $stats['expired'] }} expired medicines must be removed from inventory and disposed of properly following regulations.</span>
            </div>
            @endif

            <div style="padding: 14px; background: #dbeafe; border-left: 4px solid #3b82f6; border-radius: 6px;">
                <strong style="color: #1e40af;">OPTIMIZATION:</strong>
                <span style="color: #1e3a8a;">Review top dispensed medicines to optimize stock levels and ensure adequate supply of high-demand items.</span>
            </div>
        </div>
    </div>

    <!-- Report Footer -->
    <div style="text-align: center; padding: 30px; color: #a0aec0; border-top: 2px solid #e2e8f0; margin-top: 40px;">
        <p style="margin: 0 0 8px 0; font-size: 13px;">Report generated on {{ now()->format('F d, Y \a\t H:i') }}</p>
        <p style="margin: 0; font-size: 12px;">MediLink Pharmacy Inventory Management System</p>
    </div>
</div>

</body>
</html>