<!-- resources\views\admin\admin_restock.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock Approvals - Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }
        
        /* Main container - accounts for sidebar */
        .container { 
            margin-left: 260px; /* Match sidebar width */
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
        
        .alert-banner {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .alert-banner .icon { font-size: 32px; }
        .alert-banner .content h3 { margin: 0 0 5px 0; font-size: 18px; color: #991b1b; }
        .alert-banner .content p { margin: 0; color: #7f1d1d; }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            border-left: 4px solid #10b981;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #065f46;
            font-weight: 500;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            border-left: 4px solid #dc2626;
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            color: #991b1b;
            font-weight: 500;
        }
        
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
        
        .requests-section {
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
        
        .critical-row { background: #fef2f2 !important; }
        .critical-row:hover { background: #fee2e2 !important; }
        
        .urgent-row { background: #fef9f5 !important; }
        .urgent-row:hover { background: #fed7aa !important; }
        
        .badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-critical { background: #fee2e2; color: #991b1b; }
        .badge-urgent { background: #fed7aa; color: #92400e; }
        .badge-normal { background: #dbeafe; color: #1e40af; }
        .badge-pending { background: #fef3c7; color: #92400e; }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
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
        
        .btn-approve { background: #10b981; color: white; }
        .btn-approve:hover { background: #059669; }
        .btn-reject { background: #ef4444; color: white; }
        .btn-reject:hover { background: #dc2626; }
        .btn-view { background: #3b82f6; color: white; }
        .btn-view:hover { background: #2563eb; }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active { display: flex; }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        
        .modal-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .modal-header h3 { font-size: 20px; color: #1a202c; margin: 0; }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }
        
        .modal-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-cancel {
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-submit {
            padding: 10px 20px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-submit.reject { background: #ef4444; }
        
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
        
        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .container { margin-left: 220px; }
        }
        
        @media (max-width: 768px) {
            .container { 
                margin-left: 0; 
                padding: 15px;
                margin-top: 60px; /* Account for mobile header if any */
            }
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')
    
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>✅ Restock Request Approvals</h1>
            <div style="display: flex; gap: 10px;">
                <a href="{{ route('admin.restock.reports') }}" class="btn btn-view">📊 View Reports</a>
                <a href="{{ route('admin.restock.receipts') }}" class="btn btn-view">📥 Receipts</a>
                <a href="{{ route('admin.restock.disposals') }}" class="btn btn-view">🗑️ Disposals</a>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert-success">
                ✓ {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-error">
                ✗ {{ session('error') }}
            </div>
        @endif

        <!-- Critical Alert (show only if there are critical pending requests) -->
        @if($stats['critical_pending'] > 0)
        <div class="alert-banner">
            <div class="icon">🚨</div>
            <div class="content">
                <h3>{{ $stats['critical_pending'] }} Critical Request{{ $stats['critical_pending'] > 1 ? 's' : '' }} Awaiting Approval</h3>
                <p>Out-of-stock medicines need immediate attention</p>
            </div>
        </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card" style="border-left-color: #f59e0b;">
                <div class="icon">⏳</div>
                <div class="label">Pending Approval</div>
                <div class="value">{{ $stats['pending'] }}</div>
                @php
                    $criticalCount = \App\Models\RestockRequest::where('status', 'Pending')->where('priority', 'Critical')->count();
                    $urgentCount = \App\Models\RestockRequest::where('status', 'Pending')->where('priority', 'Urgent')->count();
                @endphp
                <div class="subtext">{{ $criticalCount }} critical, {{ $urgentCount }} urgent</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #10b981;">
                <div class="icon">✓</div>
                <div class="label">Approved This Month</div>
                <div class="value">{{ $stats['approved'] }}</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #3b82f6;">
                <div class="icon">📤</div>
                <div class="label">Currently Ordered</div>
                <div class="value">{{ $stats['ordered'] }}</div>
            </div>
            
            <div class="stat-card" style="border-left-color: #6366f1;">
                <div class="icon">💰</div>
                <div class="label">Pending Value</div>
                <div class="value">RM {{ number_format($stats['total_value_pending'], 2) }}</div>
                <div class="subtext">Total estimated cost</div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <form method="GET" action="{{ route('admin.restock.index') }}" style="display: flex; gap: 12px; width: 100%; align-items: center;">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending Only</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                
                <select name="priority" onchange="this.form.submit()">
                    <option value="">All Priority</option>
                    <option value="Critical" {{ request('priority') == 'Critical' ? 'selected' : '' }}>Critical Only</option>
                    <option value="Urgent" {{ request('priority') == 'Urgent' ? 'selected' : '' }}>Urgent Only</option>
                    <option value="Normal" {{ request('priority') == 'Normal' ? 'selected' : '' }}>Normal</option>
                </select>
                
                @if(request('status') || request('priority'))
                    <a href="{{ route('admin.restock.index') }}" class="btn" style="background: #f3f4f6; color: #374151;">
                        Clear Filters
                    </a>
                @endif
            </form>
        </div>

        <!-- Pending Requests Table -->
        <div class="requests-section">
            <div class="section-header">
                <h2>Restock Requests</h2>
                <span style="color: #6b7280; font-size: 14px;">
                    Showing {{ $requests->firstItem() ?? 0 }}–{{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }}
                </span>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Request #</th>
                        <th>Medicine</th>
                        <th>Requested By</th>
                        <th>Qty Requested</th>
                        <th>Current Stock</th>
                        <th>Priority</th>
                        <th>Est. Cost</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                    <tr class="{{ $request->priority === 'Critical' ? 'critical-row' : ($request->priority === 'Urgent' ? 'urgent-row' : '') }}">
                        <td><strong>{{ $request->request_number }}</strong></td>
                        <td>
                            <strong>{{ $request->medicine->medicine_name }}</strong><br>
                            <small style="color: #6b7280;">{{ $request->medicine->form }} {{ $request->medicine->strength }}</small>
                        </td>
                        <td>{{ $request->requestedBy->user->name }}</td>
                        <td><strong>{{ number_format($request->quantity_requested) }}</strong></td>
                        <td style="color: {{ $request->current_stock == 0 ? '#dc2626' : ($request->current_stock <= $request->medicine->reorder_level ? '#f59e0b' : '#10b981') }}; font-weight: 700;">
                            {{ $request->current_stock }} units 
                            @if($request->current_stock == 0) 
                                ⚠️
                            @endif
                        </td>
                        <td><span class="badge badge-{{ strtolower($request->priority) }}">{{ $request->priority }}</span></td>
                        <td><strong>RM {{ number_format($request->estimated_total_cost, 2) }}</strong></td>
                        <td>
                            {{ $request->created_at->format('M d, Y') }}<br>
                            <small style="color: #9ca3af;">{{ $request->created_at->diffForHumans() }}</small>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.restock.show', $request->request_id) }}" class="btn btn-view">View</a>
                                @if($request->status === 'Pending')
                                    <button class="btn btn-approve" onclick="showApproveModal({{ $request->request_id }}, '{{ $request->request_number }}')">Approve</button>
                                    <button class="btn btn-reject" onclick="showRejectModal({{ $request->request_id }}, '{{ $request->request_number }}')">Reject</button>
                                @else
                                    <span class="badge badge-{{ strtolower($request->status) }}">{{ $request->status }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <div class="icon">📋</div>
                                <h3>No Restock Requests Found</h3>
                                <p>All requests have been processed or no requests match your filters</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($requests->hasPages())
        <div class="pagination">
            <p style="color: #6b7280;">
                Showing {{ $requests->firstItem() }}–{{ $requests->lastItem() }} of {{ $requests->total() }} requests
            </p>
            <div style="display: flex; gap: 8px;">
                @if($requests->onFirstPage())
                    <button disabled>« Prev</button>
                @else
                    <a href="{{ $requests->previousPageUrl() }}">
                        <button>« Prev</button>
                    </a>
                @endif

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

    <!-- Approve Modal -->
    <div class="modal" id="approveModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✅ Approve Restock Request</h3>
                <p id="approve-request-number" style="color: #6b7280; font-size: 14px; margin-top: 5px;"></p>
            </div>
            <form id="approveForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Approval Notes (Optional)</label>
                    <textarea name="approval_notes" placeholder="Enter any notes or special instructions..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('approveModal')">Cancel</button>
                    <button type="submit" class="btn-submit">Approve Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal" id="rejectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>❌ Reject Restock Request</h3>
                <p id="reject-request-number" style="color: #6b7280; font-size: 14px; margin-top: 5px;"></p>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Rejection Reason <span style="color: #dc2626;">*</span></label>
                    <textarea name="rejection_reason" required placeholder="Please explain why this request is being rejected..."></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" class="btn-submit reject">Reject Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showApproveModal(id, requestNumber) {
            document.getElementById('approveForm').action = `/admin/restock/${id}/approve`;
            document.getElementById('approve-request-number').textContent = `Request: ${requestNumber}`;
            document.getElementById('approveModal').classList.add('active');
        }
        
        function showRejectModal(id, requestNumber) {
            document.getElementById('rejectForm').action = `/admin/restock/${id}/reject`;
            document.getElementById('reject-request-number').textContent = `Request: ${requestNumber}`;
            document.getElementById('rejectModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        // Close modal on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });
        
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.alert-success, .alert-error').forEach(alert => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>