<!--admin_teamManagement.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nurse-Doctor Team Management - MediLink Admin</title>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .page-header h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .page-header p {
            color: #718096;
            font-size: 14px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #495057;
            border: 1px solid #dee2e6;
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
        }

        .stat-card.green { border-left-color: #4CAF50; }
        .stat-card.orange { border-left-color: #FF9800; }
        .stat-card.blue { border-left-color: #2196F3; }
        .stat-card.purple { border-left-color: #9C27B0; }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #718096;
            font-size: 14px;
            font-weight: 600;
        }

        /* Filters */
        .filters-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
        }

        .filter-group label {
            font-size: 13px;
            font-weight: 600;
            color: #495057;
        }

        .filter-group select {
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            font-size: 14px;
        }

        /* Doctor Cards */
        .doctor-cards-container {
            display: grid;
            gap: 25px;
        }

        .doctor-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-left: 5px solid #2196F3;
        }

        .doctor-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .doctor-info {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .doctor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 700;
        }

        .doctor-details h3 {
            font-size: 20px;
            color: #1a202c;
            margin-bottom: 5px;
        }

        .doctor-specialization {
            color: #718096;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .nurse-count-badge {
            background: #4CAF50;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
        }

        .nurse-count-badge.warning {
            background: #FF9800;
        }

        .nurse-count-badge.danger {
            background: #f44336;
        }

        /* Nurse List */
        .nurses-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }

        .nurse-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            border-left: 3px solid #4CAF50;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }

        .nurse-item:hover {
            background: #e9ecef;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .nurse-item.primary {
            border-left-color: #2196F3;
            background: #e3f2fd;
        }

        .nurse-item.backup {
            border-left-color: #FF9800;
            background: #fff3e0;
        }

        .nurse-item.floater {
            border-left-color: #9C27B0;
            background: #f3e5f5;
        }

        .nurse-info-left {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .nurse-name {
            font-weight: 600;
            color: #1a202c;
            font-size: 15px;
        }

        .nurse-type {
            font-size: 11px;
            text-transform: uppercase;
            font-weight: 700;
            color: #718096;
        }

        .nurse-schedule {
            font-size: 12px;
            color: #6c757d;
            margin-top: 3px;
        }

        .nurse-actions {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 14px;
        }

        .icon-btn.edit {
            background: #e3f2fd;
            color: #2196F3;
        }

        .icon-btn.edit:hover {
            background: #2196F3;
            color: white;
        }

        .icon-btn.delete {
            background: #ffebee;
            color: #f44336;
        }

        .icon-btn.delete:hover {
            background: #f44336;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #adb5bd;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .add-nurse-btn {
            background: white;
            border: 2px dashed #dee2e6;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            color: #667eea;
            font-weight: 600;
        }

        .add-nurse-btn:hover {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <div>
                <h1>👥 Nurse-Doctor Team Management</h1>
                <p>Manage nurse assignments and team configurations</p>
            </div>
            <div class="header-actions">
                <button class="btn btn-secondary" onclick="window.location.reload()">
                    🔄 Refresh
                </button>
                <a href="{{ route('admin.teams.create') }}" class="btn btn-primary">
                    + Assign Nurse to Doctor
                </a>
            </div>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success">
            ✓ {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">
            ⚠ {{ $errors->first() }}
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card green">
                <div class="stat-value">{{ $stats['active_assignments'] }}</div>
                <div class="stat-label">Active Assignments</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value">{{ $stats['doctors_with_nurses'] }}/{{ $stats['total_doctors'] }}</div>
                <div class="stat-label">Doctors with Nurses</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value">{{ $stats['nurses_assigned'] }}/{{ $stats['total_nurses'] }}</div>
                <div class="stat-label">Nurses Assigned</div>
            </div>
            <div class="stat-card purple">
                <div class="stat-value">{{ $stats['coverage_rate'] }}%</div>
                <div class="stat-label">Coverage Rate</div>
            </div>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('admin.teams.index') }}">
            <div class="filters-bar">
                <div class="filter-group">
                    <label>Filter by Doctor</label>
                    <select name="doctor_id" onchange="this.form.submit()">
                        <option value="">All Doctors</option>
                        @foreach($allDoctors as $doc)
                        <option value="{{ $doc->doctor_id }}" {{ $doctorFilter == $doc->doctor_id ? 'selected' : '' }}>
                            Dr. {{ $doc->user->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <label>Status</label>
                    <select name="status" onchange="this.form.submit()">
                        <option value="active" {{ $statusFilter == 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="all" {{ $statusFilter == 'all' ? 'selected' : '' }}>All</option>
                        <option value="inactive" {{ $statusFilter == 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-secondary" style="width: fit-content;">
                        🔍 Apply Filters
                    </button>
                </div>
            </div>
        </form>

        <!-- Doctor Cards -->
        <div class="doctor-cards-container">
            @forelse($doctors as $doctor)
            <div class="doctor-card">
                <div class="doctor-header">
                    <div class="doctor-info">
                        <div class="doctor-avatar">
                            {{ strtoupper(substr($doctor->user->name, 0, 2)) }}
                        </div>
                        <div class="doctor-details">
                            <h3>Dr. {{ $doctor->user->name }}</h3>
                            <div class="doctor-specialization">
                                🩺 {{ $doctor->specialization }}
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $assignedCount = $doctor->assignedNurses->count();
                        $badgeClass = $assignedCount === 0 ? 'danger' : ($assignedCount < 2 ? 'warning' : '');
                    @endphp
                    <span class="nurse-count-badge {{ $badgeClass }}">
                        {{ $assignedCount }} {{ Str::plural('Nurse', $assignedCount) }}
                    </span>
                </div>

                <div class="nurses-list">
                    @forelse($doctor->assignedNurses as $nurse)
                    @php
                        $assignmentId = $nurse->pivot->assignment_id ?? null;
                        $assignmentType = $nurse->pivot->assignment_type ?? 'primary';
                        $priorityOrder = $nurse->pivot->priority_order ?? 1;
                        $shiftStart = $nurse->pivot->shift_start ?? null;
                        $shiftEnd = $nurse->pivot->shift_end ?? null;
                        $workingDays = $nurse->pivot->working_days ?? null;
                    @endphp
                    
                    @if($assignmentId)
                    <div class="nurse-item {{ $assignmentType }}">
                        <div class="nurse-info-left">
                            <div class="nurse-name">👩‍⚕️ {{ $nurse->user->name }}</div>
                            <div class="nurse-type">
                                {{ ucfirst($assignmentType) }} • Priority {{ $priorityOrder }}
                            </div>
                            @if($shiftStart && $shiftEnd)
                            <div class="nurse-schedule">
                                ⏰ {{ \Carbon\Carbon::parse($shiftStart)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($shiftEnd)->format('H:i') }}
                            </div>
                            @endif
                            @if($workingDays)
                            <div class="nurse-schedule">
                                📅 {{ implode(', ', array_map(fn($d) => substr($d, 0, 3), $workingDays)) }}
                            </div>
                            @endif
                        </div>

                        <div class="nurse-actions">
                            <button class="icon-btn edit" 
                                    onclick="window.location.href='{{ route('admin.teams.edit', $assignmentId) }}'">
                                ✏️
                            </button>
                            <form action="{{ route('admin.teams.destroy', $assignmentId) }}" 
                                  method="POST" 
                                  style="display: inline;"
                                  onsubmit="return confirm('Remove this nurse assignment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="icon-btn delete">🗑️</button>
                            </form>
                        </div>
                    </div>
                    @else
                    <div class="nurse-item">
                        <div class="nurse-info-left">
                            <div class="nurse-name">👩‍⚕️ {{ $nurse->user->name }}</div>
                            <div class="nurse-type" style="color: #dc3545;">
                                ⚠️ Assignment data missing
                            </div>
                        </div>
                    </div>
                    @endif
                    @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">👥</div>
                        <div>No nurses assigned yet</div>
                    </div>
                    @endforelse

                    <a href="{{ route('admin.teams.create', ['doctor_id' => $doctor->doctor_id]) }}" 
                       class="add-nurse-btn">
                        + Assign Nurse to This Doctor
                    </a>
                </div>
            </div>
            @empty
            <div class="doctor-card">
                <div class="empty-state">
                    <div class="empty-state-icon">🏥</div>
                    <div>No doctors found</div>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</body>
</html>