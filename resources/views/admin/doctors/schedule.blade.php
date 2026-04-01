<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Doctor Schedule</title>
    @vite(['resources/css/admin/admin_sidebar.css'])
    <style>
        body {
            margin: 0;
            font-family: "Poppins", sans-serif;
            background-color: #e4f4ff;
        }

        .main {
            margin-left: 230px;
            padding: 20px;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .page-header h2 {
            font-size: 28px;
            color: #1e293b;
        }

        .btn-back {
            background: #64748b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
        }

        .doctor-header {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .doctor-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: 600;
        }

        .doctor-info h3 {
            margin: 0;
            font-size: 24px;
            color: #1e293b;
        }

        .doctor-info p {
            margin: 5px 0 0 0;
            color: #64748b;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .shift-item, .leave-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #3b82f6;
        }

        .leave-item {
            border-left-color: #f59e0b;
        }

        .shift-date, .leave-date {
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .shift-time, .leave-type {
            color: #64748b;
            font-size: 14px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .nurse-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .nurse-badge {
            background: #eff6ff;
            color: #1e40af;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <div class="page-header">
            <h2>📅 Doctor Schedule Management</h2>
            <a href="{{ route('admin.doctors') }}" class="btn-back">← Back to List</a>
        </div>

        <div class="doctor-header">
            <div class="doctor-avatar">{{ substr($doctor->user->name, 0, 1) }}</div>
            <div class="doctor-info">
                <h3>Dr. {{ $doctor->user->name }}</h3>
                <p>{{ $doctor->specialization }}</p>
            </div>
        </div>

        <div class="content-grid">
            <!-- Upcoming Shifts -->
            <div class="card">
                <h4 class="card-title">🕒 Upcoming Shifts (Next 2 Weeks)</h4>
                @forelse($shifts as $shift)
                <div class="shift-item">
                    <div class="shift-date">{{ $shift->shift_date->format('l, M d, Y') }}</div>
                    <div class="shift-time">
                        {{ $shift->start_time->format('h:i A') }} - {{ $shift->end_time->format('h:i A') }}
                        ({{ $shift->getDurationHours() }} hours)
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-state-icon">📅</div>
                    <p>No upcoming shifts scheduled</p>
                </div>
                @endforelse
            </div>

            <!-- Leave Schedule -->
            <div class="card">
                <h4 class="card-title">🏖️ Approved Leaves</h4>
                @forelse($leaves as $leave)
                <div class="leave-item">
                    <div class="leave-date">
                        {{ $leave->start_date->format('M d') }} - {{ $leave->end_date->format('M d, Y') }}
                    </div>
                    <div class="leave-type">
                        {{ $leave->leave_type }} ({{ $leave->days }} {{ $leave->days == 1 ? 'day' : 'days' }})
                    </div>
                    @if($leave->reason)
                    <div style="color: #64748b; font-size: 13px; margin-top: 5px;">
                        Reason: {{ $leave->reason }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-state-icon">🏖️</div>
                    <p>No upcoming leaves</p>
                </div>
                @endforelse
            </div>

            <!-- Assigned Nurses -->
            <div class="card">
                <h4 class="card-title">👩‍⚕️ Assigned Nurses</h4>
                @if($doctor->assignedNurses->isNotEmpty())
                <div class="nurse-list">
                    @foreach($doctor->assignedNurses as $nurse)
                    <div class="nurse-badge">{{ $nurse->user->name }}</div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <div class="empty-state-icon">👩‍⚕️</div>
                    <p>No nurses currently assigned</p>
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <h4 class="card-title">⚡ Quick Actions</h4>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <a href="{{ route('admin.shifts.index') }}" style="text-decoration: none;">
                        <button style="width: 100%; padding: 12px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                            📅 Manage All Shifts
                        </button>
                    </a>
                    <a href="{{ route('admin.leaves.index') }}" style="text-decoration: none;">
                        <button style="width: 100%; padding: 12px; background: #f59e0b; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                            🏖️ View Leave Requests
                        </button>
                    </a>
                    <a href="{{ route('admin.teams.index') }}" style="text-decoration: none;">
                        <button style="width: 100%; padding: 12px; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 500;">
                            👥 Manage Team Assignments
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>