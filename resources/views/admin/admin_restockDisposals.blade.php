<!-- resources/views/admin/admin_restockDisposals.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Disposals - Admin</title>
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
        }
        
        .filter-row {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-row select, .filter-row input {
            padding: 8px 12px;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            min-width: 180px;
        }
        
        .disposals-section {
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
        
        .badge-expired { background: #fee2e2; color: #991b1b; }
        .badge-near-expiry { background: #fed7aa; color: #92400e; }
        .badge-damaged { background: #fef3c7; color: #92400e; }
        .badge-contaminated { background: #fee2e2; color: #991b1b; }
        .badge-recalled { background: #e0e7ff; color: #3730a3; }
        .badge-other { background: #e5e7eb; color: #374151; }
        
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
        
        .high-value-alert {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #991b1b;
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
        
        .high-value-row { background: #fef2f2 !important; }
        .high-value-row:hover { background: #fee2e2 !important; }
        
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
            <a href="{{ route('admin.pharmacy-inventory.index') }}">Pharmacy</a>
            <span>/</span>
            <span>Disposals</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <h1>🗑️ Medicine Disposals</h1>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.restock.index') }}" class="btn btn-view">← Back to Restock</a>
                <button class="btn btn-export" onclick="window.print()">🖨️ Print</button>
            </div>
        </div>

        <!-- High Value Alert -->
        @php
            $highValueDisposals = $disposals->where('estimated_loss', '>', 1000)->count();
        @endphp
        @if($highValueDisposals > 0)
        <div class="high-value-alert">
            <span style="font-size: 24px;">💰</span>
            <div>
                <strong>High-Value Disposals:</strong> {{ $highValueDisposals }} disposal(s) this period exceed RM1,000 in value.
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #ef4444;">
                <div class="icon">🗑️</div>
                <div class="label">Total Disposals (This Month)</div>
                <div class="value">{{ $stats['total_this_month'] }}</div>
                <div class="subtext">Medicine items disposed</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="icon">⏰</div>
                <div class="label">Expired Items</div>
                <div class="value">{{ $stats['expired_count'] }}</div>
                <div class="subtext">Due to expiration</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #dc2626;">
                <div class="icon">💸</div>
                <div class="label">Total Loss (This Month)</div>
                <div class="value">RM {{ number_format($stats['total_loss'], 2) }}</div>
                <div class="subtext">Financial impact</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #8b5cf6;">
                <div class="icon">📊</div>
                <div class="label">Average Loss Per Item</div>
                <div class="value">RM {{ $stats['total_this_month'] > 0 ? number_format($stats['total_loss'] / $stats['total_this_month'], 2) : '0.00' }}</div>
                <div class="subtext">Per disposal</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.restock.disposals') }}">
                <div class="filter-row">
                    <select name="reason" onchange="this.form.submit()">
                        <option value="">All Reasons</option>
                        <option value="Expired" {{ request('reason') == 'Expired' ? 'selected' : '' }}>Expired</option>
                        <option value="Near Expiry" {{ request('reason') == 'Near Expiry' ? 'selected' : '' }}>Near Expiry</option>
                        <option value="Damaged" {{ request('reason') == 'Damaged' ? 'selected' : '' }}>Damaged</option>
                        <option value="Contaminated" {{ request('reason') == 'Contaminated' ? 'selected' : '' }}>Contaminated</option>
                        <option value="Recalled by Manufacturer" {{ request('reason') == 'Recalled by Manufacturer' ? 'selected' : '' }}>Recalled</option>
                        <option value="Quality Issue" {{ request('reason') == 'Quality Issue' ? 'selected' : '' }}>Quality Issue</option>
                        <option value="Other" {{ request('reason') == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    
                    <input type="date" name="from_date" value="{{ request('from_date') }}" placeholder="From Date">
                    <input type="date" name="to_date" value="{{ request('to_date') }}" placeholder="To Date">
                    
                    <button type="submit" class="btn" style="background: #3b82f6; color: white;">Apply Filters</button>
                    
                    @if(request('reason') || request('from_date') || request('to_date'))
                        <a href="{{ route('admin.restock.disposals') }}" class="btn" style="background: #f3f4f6; color: #374151;">
                            Clear Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Disposals Table -->
        <div class="disposals-section">
            <div class="section-header">
                <h2>Disposal Records</h2>
                <span style="color: #6b7280; font-size: 14px;">
                    Showing {{ $disposals->firstItem() ?? 0 }}–{{ $disposals->lastItem() ?? 0 }} of {{ $disposals->total() }}
                </span>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Disposal #</th>
                        <th>Medicine</th>
                        <th>Quantity Disposed</th>
                        <th>Batch Number</th>
                        <th>Reason</th>
                        <th>Method</th>
                        <th>Estimated Loss</th>
                        <th>Disposed By</th>
                        <th>Disposal Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($disposals as $disposal)
                    <tr class="{{ $disposal->estimated_loss > 1000 ? 'high-value-row' : '' }}">
                        <td><strong>{{ $disposal->disposal_number }}</strong></td>
                        <td>
                            <strong>{{ $disposal->medicine->medicine_name }}</strong><br>
                            <small style="color: #6b7280;">{{ $disposal->medicine->form }} {{ $disposal->medicine->strength }}</small>
                        </td>
                        <td><strong style="color: #ef4444;">{{ number_format($disposal->quantity_disposed) }}</strong> units</td>
                        <td>{{ $disposal->batch_number ?? 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $disposal->reason)) }}">
                                {{ $disposal->reason }}
                            </span>
                        </td>
                        <td>{{ $disposal->disposal_method }}</td>
                        <td>
                            <strong style="color: {{ $disposal->estimated_loss > 1000 ? '#dc2626' : '#6b7280' }};">
                                RM {{ number_format($disposal->estimated_loss, 2) }}
                            </strong>
                            @if($disposal->estimated_loss > 1000)
                                <br><small style="color: #dc2626;">💰 High Value</small>
                            @endif
                        </td>
                        <td>
                            {{ $disposal->disposedBy->user->name }}<br>
                            @if($disposal->witnessedBy)
                                <small style="color: #6b7280;">Witness: {{ $disposal->witnessedBy->name }}</small>
                            @endif
                        </td>
                        <td>
                            {{ $disposal->disposed_at->format('M d, Y') }}<br>
                            <small style="color: #9ca3af;">{{ $disposal->disposed_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <a href="{{ route('admin.restock.disposals.show', $disposal->disposal_id) }}" class="btn btn-view">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10">
                            <div class="empty-state">
                                <div class="icon">🗑️</div>
                                <h3>No Disposals Found</h3>
                                <p>No disposal records match your current filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($disposals->hasPages())
        <div class="pagination">
            <p style="color: #6b7280;">
                Showing {{ $disposals->firstItem() }}–{{ $disposals->lastItem() }} of {{ $disposals->total() }} disposals
            </p>
            <div style="display: flex; gap: 8px;">
                @if($disposals->onFirstPage())
                    <button disabled>« Prev</button>
                @else
                    <a href="{{ $disposals->previousPageUrl() }}">
                        <button>« Prev</button>
                    </a>
                @endif

                @if($disposals->hasMorePages())
                    <a href="{{ $disposals->nextPageUrl() }}">
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