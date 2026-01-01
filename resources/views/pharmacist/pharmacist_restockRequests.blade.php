<!--pharmacist_restockRequest.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock Requests - Pharmacist</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .page-header h1 { font-size: 28px; color: #1a202c; }
        
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-block;
        }
        
        .btn-primary { background: #3182ce; color: white; }
        .btn-primary:hover { background: #2c5282; }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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
        
        .filter-box {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            min-width: 180px;
        }
        
        .requests-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
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
        
        .badge-pending { background: #fef3c7; color: #92400e; }
        .badge-approved { background: #d1fae5; color: #065f46; }
        .badge-ordered { background: #dbeafe; color: #1e40af; }
        .badge-received { background: #d1fae5; color: #065f46; }
        .badge-rejected { background: #fee2e2; color: #991b1b; }
        .badge-cancelled { background: #e5e7eb; color: #374151; }
        
        .priority-critical { background: #fee2e2; color: #991b1b; }
        .priority-urgent { background: #fed7aa; color: #92400e; }
        .priority-normal { background: #dbeafe; color: #1e40af; }
        
        .action-link {
            color: #3182ce;
            text-decoration: none;
            font-weight: 500;
        }
        
        .action-link:hover { text-decoration: underline; }
        
        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .pagination {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            margin-top: 20px;
            border-radius: 12px;
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
        .pagination button.active { background: #3182ce; color: white; border-color: #3182ce; }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')
    
    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>📦 Restock Requests</h1>
            <a href="{{ route('pharmacist.restock.create') }}" class="btn btn-primary">+ Create New Request</a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="icon">⏳</div>
                <div class="label">Pending Approval</div>
                <div class="value">{{ $stats['pending'] }}</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="icon">✓</div>
                <div class="label">Approved</div>
                <div class="value">{{ $stats['approved'] }}</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="icon">📤</div>
                <div class="label">Ordered</div>
                <div class="value">{{ $stats['ordered'] }}</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #ef4444;">
                <div class="icon">✗</div>
                <div class="label">Rejected</div>
                <div class="value">{{ $stats['rejected'] }}</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="filter-box">
            <form method="GET" action="{{ route('pharmacist.restock.index') }}">
                <div class="filter-row">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Ordered" {{ request('status') == 'Ordered' ? 'selected' : '' }}>Ordered</option>
                        <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                        <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    
                    <select name="priority" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Priority</option>
                        <option value="Critical" {{ request('priority') == 'Critical' ? 'selected' : '' }}>Critical</option>
                        <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                    </select>
                    
                    <a href="{{ route('pharmacist.restock.index') }}" class="btn" style="background: #f3f4f6; padding: 10px 20px; text-decoration: none; color: #374151;">
                        Clear Filters
                    </a>
                </div>
            </form>
        </div>

        <!-- Requests Table -->
        <div class="requests-table">
            <table>
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Current Stock</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Requested Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                    <tr>
                        <td><strong>{{ $request->request_number }}</strong></td>
                        <td>
                            <strong>{{ $request->medicine->medicine_name }}</strong><br>
                            <small style="color: #6b7280;">{{ $request->medicine->form }} {{ $request->medicine->strength }}</small>
                        </td>
                        <td><strong>{{ $request->quantity_requested }}</strong> units</td>
                        <td style="color: {{ $request->current_stock <= $request->medicine->reorder_level ? '#ef4444' : '#10b981' }}; font-weight: 600;">
                            {{ $request->current_stock }} units
                        </td>
                        <td><span class="badge priority-{{ strtolower($request->priority) }}">{{ $request->priority }}</span></td>
                        <td><span class="badge badge-{{ strtolower(str_replace(' ', '-', $request->status)) }}">{{ $request->status }}</span></td>
                        <td>{{ $request->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('pharmacist.restock.show', $request->request_id) }}" class="action-link">
                                View Details →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 60px 20px; color: #9ca3af;">
                            <div style="font-size: 48px; margin-bottom: 16px;">📦</div>
                            <p style="font-size: 16px; margin-bottom: 8px;">No restock requests found</p>
                            <small>Create your first restock request to get started</small>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination">
            <p style="color: #6b7280;">Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} requests</p>
            <div style="display: flex; gap: 8px;">
                @if($requests->onFirstPage())
                    <button disabled>« Prev</button>
                @else
                    <a href="{{ $requests->previousPageUrl() }}">
                        <button>« Prev</button>
                    </a>
                @endif

                @foreach($requests->getUrlRange(1, min(5, $requests->lastPage())) as $page => $url)
                    @if($page == $requests->currentPage())
                        <button class="active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}">
                            <button>{{ $page }}</button>
                        </a>
                    @endif
                @endforeach

                @if($requests->hasMorePages())
                    <a href="{{ $requests->nextPageUrl() }}">
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