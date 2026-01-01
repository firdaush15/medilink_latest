<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Manage Doctors</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_manageDoctors.css'])
</head>
<body>

    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h2>Manage Doctors</h2>
                <p class="subtitle">View and manage doctor profiles, schedules, and availability</p>
            </div>
            <div class="header-actions">
                <button class="btn-export" onclick="window.print()">
                    <span>📊</span> Export Report
                </button>
                {{-- ✅ FIXED: Changed to correct route name --}}
                <a href="{{ route('admin.leaves.index') }}">
                    <button class="btn-primary">
                        <span>📋</span> Leave Requests
                    </button>
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card total">
                <div class="stat-icon">👨‍⚕️</div>
                <div class="stat-content">
                    <h3>Total Doctors</h3>
                    <p class="stat-number">{{ $totalDoctors }}</p>
                    <span class="stat-change positive">+{{ $newDoctorsThisWeek }} new this week</span>
                </div>
            </div>

            <div class="stat-card available">
                <div class="stat-icon">✅</div>
                <div class="stat-content">
                    <h3>Available</h3>
                    <p class="stat-number">{{ $availableDoctors }}</p>
                    <span class="stat-label">Currently active</span>
                </div>
            </div>

            <div class="stat-card unavailable">
                <div class="stat-icon">🚫</div>
                <div class="stat-content">
                    <h3>Unavailable</h3>
                    <p class="stat-number">{{ $unavailableDoctors }}</p>
                    <span class="stat-label">Not accepting patients</span>
                </div>
            </div>

            <div class="stat-card on-leave">
                <div class="stat-icon">🏖️</div>
                <div class="stat-content">
                    <h3>On Leave</h3>
                    <p class="stat-number">{{ $onLeaveDoctors }}</p>
                    <span class="stat-change">+{{ $newLeavesThisWeek }} new this week</span>
                </div>
            </div>
        </div>

        <!-- Search & Filter Section -->
        <div class="filters-section">
            <form method="GET" action="{{ route('admin.doctors') }}" class="filters-form">
                <div class="search-group">
                    <div class="search-box">
                        <span class="search-icon">🔍</span>
                        <input type="text" name="search" placeholder="Search doctor by name..." value="{{ request('search') }}">
                    </div>
                    <button type="submit" class="btn-search">Search</button>
                </div>

                <div class="filter-group">
                    <select name="specialization" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Specializations</option>
                        <option value="Cardiology" {{ request('specialization')=='Cardiology'?'selected':'' }}>Cardiology</option>
                        <option value="Neurology" {{ request('specialization')=='Neurology'?'selected':'' }}>Neurology</option>
                        <option value="Orthopedics" {{ request('specialization')=='Orthopedics'?'selected':'' }}>Orthopedics</option>
                        <option value="Pediatrics" {{ request('specialization')=='Pediatrics'?'selected':'' }}>Pediatrics</option>
                        <option value="Dermatology" {{ request('specialization')=='Dermatology'?'selected':'' }}>Dermatology</option>
                    </select>

                    <select name="status" onchange="this.form.submit()" class="filter-select">
                        <option value="">All Status</option>
                        <option value="Available" {{ request('status')=='Available'?'selected':'' }}>✅ Available</option>
                        <option value="On Leave" {{ request('status')=='On Leave'?'selected':'' }}>🏖️ On Leave</option>
                        <option value="Unavailable" {{ request('status')=='Unavailable'?'selected':'' }}>🚫 Unavailable</option>
                    </select>

                    <a href="{{ route('admin.doctors') }}" class="btn-clear">Clear Filters</a>
                </div>
            </form>
        </div>

        <!-- Doctors Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Doctor</th>
                        <th>Specialization</th>
                        <th>Contact Information</th>
                        <th>Status</th>
                        <th class="text-center">Today's Apt.</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($doctors as $doctor)
                    <tr>
                        <td>
                            <div class="doctor-info">
                                <div class="doctor-avatar">
                                    @if($doctor->profile_photo)
                                        <img src="{{ asset('storage/' . $doctor->profile_photo) }}" alt="{{ $doctor->user->name }}">
                                    @else
                                        <div class="avatar-placeholder">{{ substr($doctor->user->name, 0, 1) }}</div>
                                    @endif
                                </div>
                                <div>
                                    <strong class="doctor-name">Dr. {{ $doctor->user->name ?? 'N/A' }}</strong>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="specialization-badge">{{ $doctor->specialization ?? 'General' }}</span>
                        </td>
                        <td>
                            <div class="contact-info">
                                <div>📞 {{ $doctor->phone_number ?? 'N/A' }}</div>
                                <div class="email-text">✉️ {{ $doctor->user->email }}</div>
                            </div>
                        </td>
                        <td>
                            @php
                                $statusClass = match($doctor->availability_status) {
                                    'Available' => 'status-available',
                                    'On Leave' => 'status-on-leave',
                                    'Unavailable' => 'status-unavailable',
                                    default => 'status-unknown'
                                };
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                @if($doctor->availability_status === 'Available')
                                    ✅ Available
                                @elseif($doctor->availability_status === 'On Leave')
                                    🏖️ On Leave
                                @elseif($doctor->availability_status === 'Unavailable')
                                    🚫 Unavailable
                                @else
                                    ❓ Unknown
                                @endif
                            </span>
                        </td>
                        <td class="text-center">
                            @php
                                $todayCount = $doctor->appointments()
                                    ->whereDate('appointment_date', today())
                                    ->count();
                            @endphp
                            <span class="appointment-count">{{ $todayCount }}</span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.doctors.show', $doctor->doctor_id) }}" title="View Profile">
                                    <button class="btn-action btn-view">👁️ View</button>
                                </a>
                                <a href="{{ route('admin.doctors.edit', $doctor->doctor_id) }}" title="Update Info">
                                    <button class="btn-action btn-edit">✏️ Edit</button>
                                </a>
                                <a href="{{ route('admin.doctors.schedule', $doctor->doctor_id) }}" title="Manage Schedule">
                                    <button class="btn-action btn-schedule">📅 Schedule</button>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="no-data">
                            <div class="empty-state">
                                <span class="empty-icon">👨‍⚕️</span>
                                <p>No doctors found</p>
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
                Showing {{ $doctors->firstItem() ?? 0 }}–{{ $doctors->lastItem() ?? 0 }} of {{ $doctors->total() }} doctors
            </p>
            <div class="pagination-buttons">
                @if ($doctors->onFirstPage())
                    <button class="pagination-btn" disabled>← Prev</button>
                @else
                    <a href="{{ $doctors->previousPageUrl() }}">
                        <button class="pagination-btn">← Prev</button>
                    </a>
                @endif

                @foreach ($doctors->getUrlRange(1, $doctors->lastPage()) as $page => $url)
                    @if ($page == $doctors->currentPage())
                        <button class="pagination-btn active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}">
                            <button class="pagination-btn">{{ $page }}</button>
                        </a>
                    @endif
                @endforeach

                @if ($doctors->hasMorePages())
                    <a href="{{ $doctors->nextPageUrl() }}">
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

        .stat-card.total { border-left-color: #3b82f6; }
        .stat-card.available { border-left-color: #10b981; }
        .stat-card.unavailable { border-left-color: #ef4444; }
        .stat-card.on-leave { border-left-color: #f59e0b; }

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

        .stat-change, .stat-label {
            font-size: 13px;
            color: #64748b;
            display: block;
            margin-top: 5px;
        }

        .stat-change.positive {
            color: #10b981;
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

        .doctor-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .doctor-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            overflow: hidden;
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-placeholder {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
        }

        .doctor-name {
            font-size: 15px;
            color: #1e293b;
            font-weight: 600;
        }

        .specialization-badge {
            background: #eff6ff;
            color: #2563eb;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
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

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .status-available {
            background: #d1fae5;
            color: #065f46;
        }

        .status-on-leave {
            background: #fef3c7;
            color: #92400e;
        }

        .status-unavailable {
            background: #fee2e2;
            color: #991b1b;
        }

        .appointment-count {
            background: #dbeafe;
            color: #1e40af;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
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

        .btn-schedule {
            background: #10b981;
            color: white;
        }

        .btn-schedule:hover {
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

            .search-group, .filter-group {
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
</body>
</html>