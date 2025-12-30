<!--admin_LeaveManagement.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management - Admin</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
            min-height: 100vh;
        }

        .page-header {
            background: white;
            padding: 25px 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .page-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #718096;
            margin: 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid;
        }

        .stat-card.pending { border-left-color: #ff9800; }
        .stat-card.approved { border-left-color: #4caf50; }
        .stat-card.rejected { border-left-color: #f44336; }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #718096;
            font-size: 14px;
        }

        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 200px;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
        }

        .filter-group input,
        .filter-group select {
            padding: 10px 15px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
            width: 100%;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .status-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .status-tab {
            padding: 12px 24px;
            border: none;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            color: #718096;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .status-tab:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .status-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .leave-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f9fa;
        }

        th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        tbody tr:hover {
            background: #f8f9fa;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .leave-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }

        .leave-status.pending {
            background: #fff3e0;
            color: #f57c00;
        }

        .leave-status.approved {
            background: #d4edda;
            color: #155724;
        }

        .leave-status.rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-right: 5px;
        }

        .btn:last-child {
            margin-right: 0;
        }

        .btn-success {
            background: #4caf50;
            color: white;
        }

        .btn-success:hover {
            background: #388e3c;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
            transform: translateY(-1px);
        }

        .btn-info {
            background: #2196f3;
            color: white;
        }

        .btn-info:hover {
            background: #1976d2;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            backdrop-filter: blur(5px);
        }

        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .modal-content h2 {
            margin-bottom: 20px;
            color: #1a202c;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
        }

        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-family: inherit;
            resize: vertical;
            min-height: 100px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #adb5bd;
        }

        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .filters-bar {
                flex-direction: column;
            }

            .status-tabs {
                overflow-x: auto;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <div class="page-header">
            <h1>🏖️ Leave Management</h1>
            <p>Review and manage staff leave requests</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <span>✓</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-error">
            <span>⚠</span>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-value">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending Requests</div>
            </div>
            <div class="stat-card approved">
                <div class="stat-value">{{ $stats['approved'] }}</div>
                <div class="stat-label">Approved This Year</div>
            </div>
            <div class="stat-card rejected">
                <div class="stat-value">{{ $stats['rejected'] }}</div>
                <div class="stat-label">Rejected Requests</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" class="filters-bar">
            <div class="filter-group">
                <label>Search Staff</label>
                <input type="text" name="search" placeholder="Staff name..." value="{{ request('search') }}">
            </div>

            <div class="filter-group">
                <label>Role</label>
                <select name="role">
                    <option value="">All Roles</option>
                    <option value="doctor" {{ request('role') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                    <option value="nurse" {{ request('role') == 'nurse' ? 'selected' : '' }}>Nurse</option>
                    <option value="pharmacist" {{ request('role') == 'pharmacist' ? 'selected' : '' }}>Pharmacist</option>
                    <option value="receptionist" {{ request('role') == 'receptionist' ? 'selected' : '' }}>Receptionist</option>
                </select>
            </div>

            <div class="filter-group">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-info">🔍 Filter</button>
            </div>
        </form>

        <!-- Status Tabs -->
        <div class="status-tabs">
            <a href="{{ route('admin.leaves.index', ['status' => 'pending']) }}" 
               class="status-tab {{ $status == 'pending' ? 'active' : '' }}">
                Pending ({{ $stats['pending'] }})
            </a>
            <a href="{{ route('admin.leaves.index', ['status' => 'approved']) }}" 
               class="status-tab {{ $status == 'approved' ? 'active' : '' }}">
                Approved
            </a>
            <a href="{{ route('admin.leaves.index', ['status' => 'rejected']) }}" 
               class="status-tab {{ $status == 'rejected' ? 'active' : '' }}">
                Rejected
            </a>
            <a href="{{ route('admin.leaves.index', ['status' => 'all']) }}" 
               class="status-tab {{ $status == 'all' ? 'active' : '' }}">
                All
            </a>
        </div>

        <!-- Table -->
        <div class="leave-table">
            <table>
                <thead>
                    <tr>
                        <th>Staff Name</th>
                        <th>Role</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaves as $leave)
                    <tr>
                        <td><strong>{{ $leave->user->name }}</strong></td>
                        <td>{{ ucfirst($leave->staff_role) }}</td>
                        <td>{{ $leave->leave_type }}</td>
                        <td>{{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}</td>
                        <td>{{ $leave->days }} day(s)</td>
                        <td><span class="leave-status {{ $leave->status }}">{{ ucfirst($leave->status) }}</span></td>
                        <td>
                            @if($leave->status == 'pending')
                            <button onclick="approveLeave({{ $leave->leave_id }})" class="btn btn-success">
                                ✓ Approve
                            </button>
                            <button onclick="openRejectModal({{ $leave->leave_id }})" class="btn btn-danger">
                                ✗ Reject
                            </button>
                            @else
                            <a href="{{ route('admin.leaves.show', $leave->leave_id) }}" class="btn btn-info">
                                👁️ View
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p>No leave requests found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div style="margin-top: 20px;">
            {{ $leaves->links() }}
        </div>
    </div>

    <!-- Reject Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <h2>Reject Leave Request</h2>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="form-group">
                    <label>Rejection Reason <span style="color: red;">*</span></label>
                    <textarea name="rejection_reason" required placeholder="Provide reason for rejection..."></textarea>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeRejectModal()" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Leave</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function approveLeave(leaveId) {
            if (confirm('Approve this leave request?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/leaves/${leaveId}/approve`;
                form.innerHTML = '@csrf';
                document.body.appendChild(form);
                form.submit();
            }
        }

        function openRejectModal(leaveId) {
            const modal = document.getElementById('rejectModal');
            const form = document.getElementById('rejectForm');
            form.action = `/admin/leaves/${leaveId}/reject`;
            modal.classList.add('show');
        }

        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.remove('show');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('rejectModal');
            if (event.target == modal) {
                closeRejectModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>