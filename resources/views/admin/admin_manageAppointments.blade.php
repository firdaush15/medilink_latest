<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MediLink | Appointments Management</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_manageAppointments.css'])
</head>
<body>

    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h2>Appointments Management</h2>
                <p class="subtitle">Monitor and manage all patient appointments</p>
            </div>
            <div class="header-actions">
                <button class="btn-export" onclick="window.print()">
                    <span>📊</span> Export Report
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card today">
                <div class="stat-icon">📅</div>
                <div class="stat-content">
                    <h3>Today's Appointments</h3>
                    <p class="stat-number">{{ $todayCount ?? 0 }}</p>
                    <span class="stat-label">
                        @php
                            $todayCompleted = \App\Models\Appointment::whereDate('appointment_date', today())
                                ->where('status', 'completed')->count();
                        @endphp
                        {{ $todayCompleted }} completed
                    </span>
                </div>
            </div>

            <div class="stat-card confirmed">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3>Confirmed</h3>
                    <p class="stat-number">{{ $confirmCount ?? 0 }}</p>
                    <span class="stat-label">Awaiting patient arrival</span>
                </div>
            </div>

            <div class="stat-card in-progress">
                <div class="stat-icon">⏳</div>
                <div class="stat-content">
                    <h3>In Progress</h3>
                    <p class="stat-number">
                        @php
                            $inProgress = \App\Models\Appointment::whereIn('status', [
                                'checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation'
                            ])->count();
                        @endphp
                        {{ $inProgress }}
                    </p>
                    <span class="stat-label">Currently being processed</span>
                </div>
            </div>

            <div class="stat-card completed">
                <div class="stat-icon">✔️</div>
                <div class="stat-content">
                    <h3>Completed</h3>
                    <p class="stat-number">{{ $completedCount ?? 0 }}</p>
                    <span class="stat-label">All time total</span>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="filters-section">
            <form method="GET" action="{{ route('admin.appointments') }}" class="filters-form">
                <div class="search-group">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search by doctor or patient name...">
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                </div>

                <div class="filter-group">
                    <input type="date" name="date" value="{{ request('date') }}" 
                           onchange="this.form.submit()" class="filter-input">

                    <select name="status" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Statuses</option>
                        <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>✅ Confirmed</option>
                        <option value="checked_in" {{ request('status') == 'checked_in' ? 'selected' : '' }}>🏥 Checked In</option>
                        <option value="in_consultation" {{ request('status') == 'in_consultation' ? 'selected' : '' }}>🩺 In Consultation</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>✔️ Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                        <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>⚠️ No Show</option>
                    </select>

                    <select name="sort" onchange="this.form.submit()" class="filter-select">
                        <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Newest First</option>
                        <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                    </select>

                    <a href="{{ route('admin.appointments') }}" class="btn-clear">Clear</a>
                </div>
            </form>
        </div>

        <!-- Appointments Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appointments as $appointment)
                    <tr>
                        <td>
                            <div class="date-time">
                                <strong>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}</strong>
                                <small>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td>
                            <div class="person-info">
                                <div class="person-avatar">
                                    <div class="avatar-placeholder patient">
                                        {{ substr($appointment->patient->user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div>
                                    <strong>{{ $appointment->patient->user->name }}</strong>
                                    <small>{{ $appointment->patient->phone_number }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="person-info">
                                <div class="person-avatar">
                                    <div class="avatar-placeholder doctor">
                                        {{ substr($appointment->doctor->user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div>
                                    <strong>Dr. {{ $appointment->doctor->user->name }}</strong>
                                    <small>{{ $appointment->doctor->specialization }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="department-badge">{{ $appointment->doctor->specialization }}</span>
                        </td>
                        <td>
                            @php
                            $statusConfig = [
                                'completed' => ['class' => 'status-completed', 'icon' => '✔️', 'text' => 'Completed'],
                                'confirmed' => ['class' => 'status-confirmed', 'icon' => '✅', 'text' => 'Confirmed'],
                                'checked_in' => ['class' => 'status-progress', 'icon' => '🏥', 'text' => 'Checked In'],
                                'vitals_pending' => ['class' => 'status-progress', 'icon' => '📋', 'text' => 'Vitals Pending'],
                                'vitals_recorded' => ['class' => 'status-progress', 'icon' => '📊', 'text' => 'Vitals Recorded'],
                                'ready_for_doctor' => ['class' => 'status-progress', 'icon' => '👨‍⚕️', 'text' => 'Ready'],
                                'in_consultation' => ['class' => 'status-progress', 'icon' => '🩺', 'text' => 'In Consultation'],
                                'cancelled' => ['class' => 'status-cancelled', 'icon' => '❌', 'text' => 'Cancelled'],
                                'no_show' => ['class' => 'status-no-show', 'icon' => '⚠️', 'text' => 'No Show'],
                            ];
                            $status = $statusConfig[$appointment->status] ?? ['class' => 'status-unknown', 'icon' => '❓', 'text' => ucfirst($appointment->status)];
                            @endphp
                            <span class="status-badge {{ $status['class'] }}">
                                {{ $status['icon'] }} {{ $status['text'] }}
                            </span>
                        </td>
                        <td>
                            @if($appointment->status === 'completed')
                                <div class="progress-indicator completed">
                                    <span class="progress-icon">✓</span>
                                    <span>Done</span>
                                </div>
                            @elseif(in_array($appointment->status, ['checked_in', 'vitals_pending', 'vitals_recorded', 'ready_for_doctor', 'in_consultation']))
                                <div class="progress-indicator in-progress">
                                    <span class="progress-icon">⏳</span>
                                    <span>{{ $appointment->getCurrentStage() }}</span>
                                </div>
                            @elseif($appointment->status === 'confirmed')
                                <div class="progress-indicator scheduled">
                                    <span class="progress-icon">📅</span>
                                    <span>Scheduled</span>
                                </div>
                            @else
                                <div class="progress-indicator inactive">
                                    <span class="progress-icon">✕</span>
                                    <span>{{ ucfirst($appointment->status) }}</span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.appointments.show', $appointment->appointment_id) }}" title="View Details">
                                    <button class="btn-action btn-view">👁️</button>
                                </a>
                                
                                @if(!in_array($appointment->status, ['completed', 'cancelled', 'no_show']))
                                    <a href="{{ route('admin.appointments.reschedule', $appointment->appointment_id) }}" title="Reschedule">
                                        <button class="btn-action btn-reschedule">📅</button>
                                    </a>
                                    
                                    <button class="btn-action btn-cancel" title="Cancel Appointment" 
                                        onclick="cancelAppointment({{ $appointment->appointment_id }})">
                                        ✕
                                    </button>
                                @endif

                                @if($appointment->status === 'confirmed' && $appointment->appointment_date->isPast())
                                    <button class="btn-action btn-no-show" title="Mark as No Show" 
                                        onclick="markNoShow({{ $appointment->appointment_id }})">
                                        ⚠️
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="no-data">
                            <div class="empty-state">
                                <span class="empty-icon">📅</span>
                                <p>No appointments found</p>
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
                Showing {{ $appointments->firstItem() ?? 0 }}–{{ $appointments->lastItem() ?? 0 }}
                of {{ $appointments->total() }} appointments
            </p>
            <div class="pagination-buttons">
                @if ($appointments->onFirstPage())
                    <button class="pagination-btn" disabled>← Prev</button>
                @else
                    <a href="{{ $appointments->previousPageUrl() }}">
                        <button class="pagination-btn">← Prev</button>
                    </a>
                @endif

                @foreach ($appointments->getUrlRange(1, $appointments->lastPage()) as $page => $url)
                    @if ($page == $appointments->currentPage())
                        <button class="pagination-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}">
                            <button class="pagination-btn">{{ $page }}</button>
                        </a>
                    @endif
                @endforeach

                @if ($appointments->hasMorePages())
                    <a href="{{ $appointments->nextPageUrl() }}">
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

        .btn-export, .btn-primary {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-export {
            background: #64748b;
            color: white;
        }

        .btn-export:hover {
            background: #475569;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(100, 116, 139, 0.3);
        }

        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
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

        .stat-card.today { border-left-color: #3b82f6; }
        .stat-card.confirmed { border-left-color: #10b981; }
        .stat-card.in-progress { border-left-color: #f59e0b; }
        .stat-card.completed { border-left-color: #8b5cf6; }

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
            flex-wrap: wrap;
        }

        .filter-input, .filter-select {
            padding: 10px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .filter-input:hover, .filter-select:hover {
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

        .date-time {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .date-time strong {
            color: #1e293b;
            font-size: 14px;
        }

        .date-time small {
            color: #64748b;
            font-size: 12px;
        }

        .person-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .person-avatar {
            width: 40px;
            height: 40px;
        }

        .avatar-placeholder {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .avatar-placeholder.patient {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .avatar-placeholder.doctor {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }

        .person-info strong {
            font-size: 14px;
            color: #1e293b;
            display: block;
        }

        .person-info small {
            font-size: 12px;
            color: #64748b;
            display: block;
            margin-top: 2px;
        }

        .department-badge {
            background: #f1f5f9;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            color: #475569;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            white-space: nowrap;
        }

        .status-completed { background: #d1fae5; color: #065f46; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-progress { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-no-show { background: #ffe4e6; color: #9f1239; }

        .progress-indicator {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
        }

        .progress-indicator.completed { color: #059669; }
        .progress-indicator.in-progress { color: #d97706; }
        .progress-indicator.scheduled { color: #2563eb; }
        .progress-indicator.inactive { color: #64748b; }

        .progress-icon {
            font-size: 16px;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            justify-content: center;
        }

        .btn-action {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
            transform: scale(1.1);
        }

        .btn-reschedule {
            background: #f59e0b;
            color: white;
        }

        .btn-reschedule:hover {
            background: #d97706;
            transform: scale(1.1);
        }

        .btn-cancel {
            background: #ef4444;
            color: white;
        }

        .btn-cancel:hover {
            background: #dc2626;
            transform: scale(1.1);
        }

        .btn-no-show {
            background: #64748b;
            color: white;
        }

        .btn-no-show:hover {
            background: #475569;
            transform: scale(1.1);
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

            .search-group, .filter-group {
                width: 100%;
            }

            .pagination-container {
                flex-direction: column;
                gap: 15px;
            }
        }
    </style>

    <script>
        function cancelAppointment(appointmentId) {
            const reason = prompt('Enter cancellation reason:');
            if (reason) {
                if (confirm('Are you sure you want to cancel this appointment?')) {
                    fetch(`/admin/appointments/${appointmentId}/cancel`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ reason: reason })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Appointment cancelled successfully');
                            location.reload();
                        } else {
                            alert('Failed to cancel appointment');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred');
                    });
                }
            }
        }

        function markNoShow(appointmentId) {
            if (confirm('Mark this appointment as No Show? This will update the patient record.')) {
                fetch(`/admin/appointments/${appointmentId}/no-show`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Appointment marked as No Show');
                        location.reload();
                    } else {
                        alert('Failed to mark as no show');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                });
            }
        }
    </script>
</body>
</html>