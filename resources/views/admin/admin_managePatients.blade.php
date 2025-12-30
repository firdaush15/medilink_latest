<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Manage Patients</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_managePatients.css'])
</head>

<body>

    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h2>Manage Patients</h2>
                <p class="subtitle">View patient records, contact information, and visit history</p>
            </div>
            <div class="header-actions">
                <button class="btn-export" onclick="window.print()">
                    <span>📊</span> Export Report
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>Total Patients</h3>
                    <p class="stat-number">{{ $patients->total() }}</p>
                    <span class="stat-label">Registered in system</span>
                </div>
            </div>

            <div class="stat-card active">
                <div class="stat-icon">🏥</div>
                <div class="stat-content">
                    <h3>Active Today</h3>
                    <p class="stat-number">
                        @php
                        $activeToday = \App\Models\Appointment::whereDate('appointment_date', today())
                        ->whereIn('status', ['checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'])
                        ->distinct('patient_id')
                        ->count('patient_id');
                        @endphp
                        {{ $activeToday }}
                    </p>
                    <span class="stat-label">With appointments today</span>
                </div>
            </div>

            <div class="stat-card flagged">
                <div class="stat-icon">⚠️</div>
                <div class="stat-content">
                    <h3>Flagged</h3>
                    <p class="stat-number">{{ \App\Models\Patient::where('is_flagged', true)->count() }}</p>
                    <span class="stat-label">Require attention</span>
                </div>
            </div>

            <div class="stat-card new">
                <div class="stat-icon">✨</div>
                <div class="stat-content">
                    <h3>New This Week</h3>
                    <p class="stat-number">{{ \App\Models\Patient::where('created_at', '>=', now()->startOfWeek())->count() }}</p>
                    <span class="stat-label">Recent registrations</span>
                </div>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="filters-section">
            <form method="GET" action="{{ route('admin.patients') }}" class="filters-form">
                <div class="search-group">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search" placeholder="Search patient by name..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                </div>

                <div class="filter-group">
                    <select name="flagged" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Patients</option>
                        <option value="1" {{ request('flagged')=='1'?'selected':'' }}>⚠️ Flagged Only</option>
                        <option value="0" {{ request('flagged')=='0'?'selected':'' }}>✓ Regular Only</option>
                    </select>

                    <a href="{{ route('admin.patients') }}" class="btn-clear">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Patients Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Contact</th>
                        <th>Last Visit</th>
                        <th class="text-center">Total Visits</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patients as $patient)
                    <tr>
                        <!-- In the table row -->
                        <td>
                            <div class="patient-info">
                                <div class="patient-avatar">
                                    <div class="avatar-placeholder">{{ substr($patient->user->name, 0, 1) }}</div>
                                </div>
                                <div>
                                    <strong class="patient-name">
                                        {{ $patient->user->name ?? 'N/A' }}
                                        @if($patient->is_flagged)
                                        <span class="flag-icon" title="{{ $patient->flag_reason }}">⚠️</span>
                                        @endif
                                    </strong>
                                </div>
                            </div>
                        </td>

                        <!-- Status column -->
                        <td>
                            @if($patient->is_flagged)
                            <span class="status-badge status-flagged">⚠️ Flagged</span>
                            @else
                            <span class="status-badge status-active">✓ Active</span>
                            @endif
                        </td>
                        <td>
                            <span class="gender-badge">
                                @if($patient->gender === 'Male')
                                👨 Male
                                @elseif($patient->gender === 'Female')
                                👩 Female
                                @else
                                {{ $patient->gender ?? 'N/A' }}
                                @endif
                            </span>
                        </td>
                        <td>
                            <span class="age-text">{{ $patient->age ?? 'N/A' }} years</span>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div>📞 {{ $patient->phone_number ?? 'N/A' }}</div>
                                <div class="email-text">✉️ {{ $patient->user->email }}</div>
                            </div>
                        </td>
                        <td>
                            @php
                            $lastVisit = $patient->appointments()
                            ->where('status', 'completed')
                            ->latest('appointment_date')
                            ->first();
                            @endphp
                            @if($lastVisit)
                            <div class="visit-info">
                                <strong>{{ $lastVisit->appointment_date->format('M d, Y') }}</strong>
                                <small>{{ $lastVisit->appointment_date->diffForHumans() }}</small>
                            </div>
                            @else
                            <span class="no-visit">No visits yet</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="visit-count">
                                {{ $patient->appointments()->where('status', 'completed')->count() }}
                            </span>
                        </td>
                        <td>
                            @if($patient->is_flagged)
                            <span class="status-badge status-flagged">⚠️ Flagged</span>
                            @else
                            <span class="status-badge status-active">✓ Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.patients.show', $patient->patient_id) }}" title="View Full Profile">
                                    <button class="btn-action btn-view">👁️ View</button>
                                </a>
                                <a href="{{ route('admin.patients.edit', $patient->patient_id) }}" title="Update Contact Info">
                                    <button class="btn-action btn-edit">✏️ Edit</button>
                                </a>
                                @if($patient->is_flagged)
                                <button class="btn-action btn-unflag" title="Remove Flag"
                                    onclick="unflagPatient({{ $patient->patient_id }})">
                                    ✓ Unflag
                                </button>
                                @else
                                <button class="btn-action btn-flag" title="Flag Patient"
                                    onclick="flagPatient({{ $patient->patient_id }})">
                                    ⚠️ Flag
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="no-data">
                            <div class="empty-state">
                                <span class="empty-icon">👥</span>
                                <p>No patients found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            <p class="pagination-info">
                Showing {{ $patients->firstItem() ?? 0 }}–{{ $patients->lastItem() ?? 0 }} of {{ $patients->total() }} patients
            </p>
            <div class="pagination-buttons">
                @if ($patients->onFirstPage())
                <button class="pagination-btn" disabled>← Prev</button>
                @else
                <a href="{{ $patients->previousPageUrl() }}">
                    <button class="pagination-btn">← Prev</button>
                </a>
                @endif

                @foreach ($patients->getUrlRange(1, $patients->lastPage()) as $page => $url)
                @if ($page == $patients->currentPage())
                <button class="pagination-btn active">{{ $page }}</button>
                @else
                <a href="{{ $url }}">
                    <button class="pagination-btn">{{ $page }}</button>
                </a>
                @endif
                @endforeach

                @if ($patients->hasMorePages())
                <a href="{{ $patients->nextPageUrl() }}">
                    <button class="pagination-btn">Next →</button>
                </a>
                @else
                <button class="pagination-btn" disabled>Next →</button>
                @endif
            </div>
        </div>
    </div>

    <style>
        /* Modern UI Styles */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e7ff;
        }

        .page-header h2 {
            font-size: 28px;
            color: #1e293b;
            margin: 0;
            font-weight: 600;
        }

        .subtitle {
            color: #64748b;
            font-size: 14px;
            margin-top: 5px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn-export {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
            background: #64748b;
            color: white;
        }

        .btn-export:hover {
            background: #475569;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            display: flex;
            gap: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-card.total {
            border-left-color: #3b82f6;
        }

        .stat-card.active {
            border-left-color: #10b981;
        }

        .stat-card.flagged {
            border-left-color: #f59e0b;
        }

        .stat-card.new {
            border-left-color: #8b5cf6;
        }

        .stat-icon {
            font-size: 32px;
            line-height: 1;
        }

        .stat-content h3 {
            font-size: 14px;
            color: #64748b;
            margin: 0 0 8px 0;
            font-weight: 500;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            display: block;
            margin-top: 5px;
        }

        /* Filters Section */
        .filters-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filters-form {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .search-group {
            display: flex;
            gap: 10px;
            flex: 1;
            min-width: 300px;
        }

        .search-box {
            display: flex;
            align-items: center;
            background: #f1f5f9;
            border-radius: 8px;
            padding: 10px 15px;
            flex: 1;
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .search-box:focus-within {
            background: white;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        .search-icon {
            margin-right: 10px;
            font-size: 16px;
        }

        .search-box input {
            border: none;
            background: transparent;
            outline: none;
            width: 100%;
            font-size: 14px;
        }

        .btn-search {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-search:hover {
            background: #2563eb;
        }

        .filter-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .filter-select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-select:hover {
            border-color: #3b82f6;
        }

        .btn-clear {
            background: #64748b;
            color: white;
            padding: 10px 15px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-clear:hover {
            background: #475569;
        }

        /* Table Styles */
        .table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        }

        .data-table th {
            padding: 16px;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .data-table td {
            padding: 16px;
            border-bottom: 1px solid #f1f5f9;
        }

        .data-table tbody tr {
            transition: all 0.2s;
        }

        .data-table tbody tr:hover {
            background: #f8fafc;
        }

        .patient-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .patient-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
        }

        .avatar-placeholder {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .patient-name {
            font-size: 15px;
            color: #1e293b;
            font-weight: 600;
        }

        .flag-icon {
            font-size: 16px;
            margin-left: 6px;
        }

        .gender-badge {
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }

        .age-text {
            color: #64748b;
            font-size: 14px;
        }

        .contact-info div {
            font-size: 13px;
            color: #475569;
            margin-bottom: 4px;
        }

        .email-text {
            color: #64748b;
            font-size: 12px;
        }

        .visit-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .visit-info strong {
            color: #1e293b;
            font-size: 14px;
        }

        .visit-info small {
            color: #94a3b8;
            font-size: 12px;
        }

        .no-visit {
            color: #94a3b8;
            font-style: italic;
            font-size: 13px;
        }

        .visit-count {
            background: #dbeafe;
            color: #1e40af;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .status-flagged {
            background: #fef3c7;
            color: #92400e;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: #f59e0b;
            color: white;
        }

        .btn-edit:hover {
            background: #d97706;
            transform: translateY(-2px);
        }

        .btn-flag {
            background: #ef4444;
            color: white;
        }

        .btn-flag:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        .btn-unflag {
            background: #10b981;
            color: white;
        }

        .btn-unflag:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .text-center {
            text-align: center;
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state {
            color: #94a3b8;
        }

        .empty-icon {
            font-size: 48px;
            display: block;
            margin-bottom: 16px;
        }

        /* Pagination */
        .pagination-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 12px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .pagination-info {
            color: #64748b;
            font-size: 14px;
        }

        .pagination-buttons {
            display: flex;
            gap: 8px;
        }

        .pagination-btn {
            padding: 8px 14px;
            border: 1px solid #e2e8f0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            color: #475569;
            transition: all 0.3s;
        }

        .pagination-btn:hover:not(:disabled):not(.active) {
            background: #f8fafc;
            border-color: #3b82f6;
            color: #3b82f6;
        }

        .pagination-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .pagination-btn:disabled {
            background: #f1f5f9;
            color: #cbd5e1;
            cursor: not-allowed;
        }

        @media (max-width: 768px) {
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .filters-form {
                flex-direction: column;
            }

            .search-group,
            .filter-group {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>

    <script>
        function flagPatient(patientId) {
            const reason = prompt('Enter reason for flagging this patient:');
            if (reason && reason.trim() !== '') {
                fetch(`{{ url('admin/patients') }}/${patientId}/flag`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            reason: reason
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Patient flagged successfully');
                            location.reload();
                        } else {
                            alert('Failed to flag patient');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred: ' + error.message);
                    });
            }
        }

        function unflagPatient(patientId) {
            if (confirm('Remove flag from this patient?')) {
                fetch(`{{ url('admin/patients') }}/${patientId}/unflag`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            alert('Flag removed successfully');
                            location.reload();
                        } else {
                            alert('Failed to unflag patient');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred: ' + error.message);
                    });
            }
        }
    </script>
</body>

</html>