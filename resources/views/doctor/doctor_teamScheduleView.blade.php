{{-- resources/views/doctor/doctor_teamScheduleView.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Team & Schedule - Doctor Dashboard</title>
    @vite(['resources/css/doctor/doctor_sidebar.css'])
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

        .tabs-container {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            border-bottom: 2px solid #e1e8ed;
        }

        .tab-btn {
            padding: 12px 24px;
            border: none;
            background: none;
            cursor: pointer;
            font-weight: 600;
            color: #718096;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* My Team Section */
        .team-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .team-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e1e8ed;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .nurse-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            position: relative;
        }

        .nurse-card.primary {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
        }

        .nurse-card.backup {
            background: linear-gradient(135deg, #FF9800 0%, #F57C00 100%);
        }

        .nurse-type-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .nurse-name {
            font-size: 20px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .nurse-schedule {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 10px;
        }

        /* My Shifts Section */
        .shifts-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .week-nav {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
        }

        .week-nav button {
            padding: 10px 20px;
            border: 1px solid #dee2e6;
            background: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .current-week {
            font-weight: 700;
            font-size: 18px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 15px;
        }

        .day-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
        }

        .day-card.today {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .day-header {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .shift-info {
            background: white;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
        }

        .day-card.today .shift-info {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .no-shift {
            color: #adb5bd;
            font-size: 13px;
            margin-top: 10px;
        }

        /* Leave Application Section */
        .leave-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-top: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .leave-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #667eea;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
        }

        .stat-label {
            color: #718096;
            font-size: 13px;
            margin-top: 5px;
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

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .leave-history {
            margin-top: 30px;
        }

        .leave-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .leave-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
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

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    @include('doctor.sidebar.doctor_sidebar')

    <div class="main-content">
        <div class="page-header">
            <h1>👥 My Team & Schedule</h1>
            <p>Manage your nursing team, view shifts, and apply for leave</p>

            <div class="tabs-container">
                <button class="tab-btn active" onclick="switchTab('team')">
                    👥 My Team
                </button>
                <button class="tab-btn" onclick="switchTab('shifts')">
                    📅 My Shifts
                </button>
                <button class="tab-btn" onclick="switchTab('leave')">
                    🏖️ Leave Management
                </button>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">✓ {{ session('success') }}</div>
        @endif

        @if($errors->any())
        <div class="alert alert-error">⚠ {{ $errors->first() }}</div>
        @endif

        <!-- MY TEAM TAB -->
        <div id="team-tab" class="tab-content active">
            <div class="team-section">
                <div class="team-header">
                    <h2>My Assigned Nurses</h2>
                    <span style="color: #718096;">{{ $assignedNurses->count() }} nurses assigned</span>
                </div>

                @if($assignedNurses->isEmpty())
                <div style="text-align: center; padding: 40px; color: #adb5bd;">
                    <div style="font-size: 48px; margin-bottom: 15px;">👥</div>
                    <h3>No Nurses Assigned Yet</h3>
                    <p>Contact administration to get nursing team assigned</p>
                </div>
                @else
                <div class="team-grid">
                    @foreach($assignedNurses as $nurse)
                    <div class="nurse-card {{ $nurse->pivot->assignment_type ?? 'primary' }}">
                        <div class="nurse-type-badge">
                            {{ ucfirst($nurse->pivot->assignment_type ?? 'Primary') }}
                        </div>
                        <div class="nurse-name">👩‍⚕️ {{ $nurse->user->name }}</div>
                        <div style="font-size: 14px; opacity: 0.9;">
                            Priority: {{ $nurse->pivot->priority_order ?? 1 }}
                        </div>
                        @if($nurse->pivot->working_days)
                        <div class="nurse-schedule">
                            📅 {{ implode(', ', array_map(fn($d) => substr($d, 0, 3), $nurse->pivot->working_days)) }}
                        </div>
                        @endif
                        @if($nurse->pivot->shift_start && $nurse->pivot->shift_end)
                        <div class="nurse-schedule">
                            ⏰ {{ \Carbon\Carbon::parse($nurse->pivot->shift_start)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($nurse->pivot->shift_end)->format('H:i') }}
                        </div>
                        @endif
                        <div style="margin-top: 15px; font-size: 13px; opacity: 0.85;">
                            Status: {{ ucfirst($nurse->availability_status) }}
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        <!-- MY SHIFTS TAB -->
        <div id="shifts-tab" class="tab-content">
            <div class="shifts-section">
                <div class="week-nav">
                    <button onclick="previousWeek()">◀ Previous</button>
                    <span class="current-week">{{ $weekStart->format('M d') }} - {{ $weekEnd->format('M d, Y') }}</span>
                    <button onclick="nextWeek()">Next ▶</button>
                </div>

                <div class="calendar-grid">
                    @for($i = 0; $i < 7; $i++)
                        @php
                            $day = $weekStart->copy()->addDays($i);
                            $isToday = $day->isToday();
                            $dayShift = $myShifts->first(function($shift) use ($day) {
                                return $shift->shift_date->isSameDay($day);
                            });
                        @endphp

                        <div class="day-card {{ $isToday ? 'today' : '' }}">
                            <div class="day-header">
                                {{ $day->format('D') }}<br>
                                <span style="font-size: 12px;">{{ $day->format('M d') }}</span>
                            </div>

                            @if($dayShift)
                            <div class="shift-info">
                                <div style="font-weight: 700;">
                                    {{ $dayShift->start_time->format('H:i') }} - {{ $dayShift->end_time->format('H:i') }}
                                </div>
                                <div style="margin-top: 5px; font-size: 11px;">
                                    {{ $dayShift->template->template_name ?? 'Custom' }}
                                </div>
                                <div style="margin-top: 5px; font-weight: 600;">
                                    Status: {{ ucfirst($dayShift->status) }}
                                </div>
                            </div>
                            @else
                            <div class="no-shift">Off Day</div>
                            @endif
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- LEAVE MANAGEMENT TAB -->
        <div id="leave-tab" class="tab-content">
            <div class="leave-section">
                <h2 style="margin-bottom: 20px;">Leave Balance</h2>
                <div class="leave-stats">
                    <div class="stat-card">
                        <div class="stat-value">{{ $leaveBalance['annual'] ?? 14 }}</div>
                        <div class="stat-label">Annual Leave Days</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $leaveBalance['sick'] ?? 14 }}</div>
                        <div class="stat-label">Sick Leave Days</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $leaveBalance['used'] ?? 0 }}</div>
                        <div class="stat-label">Days Used</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">{{ $pendingLeaves ?? 0 }}</div>
                        <div class="stat-label">Pending Requests</div>
                    </div>
                </div>

                <h3 style="margin-bottom: 20px;">Apply for Leave</h3>
                <form action="{{ route('doctor.leave.apply') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
                        <div class="form-group">
                            <label>Leave Type <span style="color: #e74c3c;">*</span></label>
                            <select name="leave_type" required>
                                <option value="">Select Type</option>
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="emergency">Emergency Leave</option>
                                <option value="unpaid">Unpaid Leave</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Start Date <span style="color: #e74c3c;">*</span></label>
                            <input type="date" name="start_date" required min="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label>End Date <span style="color: #e74c3c;">*</span></label>
                            <input type="date" name="end_date" required min="{{ now()->format('Y-m-d') }}">
                        </div>

                        <div class="form-group">
                            <label>Half Day?</label>
                            <select name="is_half_day">
                                <option value="0">Full Day</option>
                                <option value="1">Half Day (Morning)</option>
                                <option value="2">Half Day (Afternoon)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Reason <span style="color: #e74c3c;">*</span></label>
                        <textarea name="reason" required placeholder="Please provide reason for leave..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">📝 Submit Leave Request</button>
                </form>

                <div class="leave-history">
                    <h3 style="margin-bottom: 20px;">Recent Leave Requests</h3>
                    @forelse($recentLeaves as $leave)
                    <div class="leave-item">
                        <div>
                            <div style="font-weight: 700; margin-bottom: 5px;">
                                {{ ucfirst($leave->leave_type) }} Leave
                            </div>
                            <div style="font-size: 13px; color: #718096;">
                                {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}
                                ({{ $leave->days }} days)
                            </div>
                        </div>
                        <span class="leave-status {{ $leave->status }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </div>
                    @empty
                    <div style="text-align: center; padding: 40px; color: #adb5bd;">
                        No leave requests yet
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            event.target.classList.add('active');
            document.getElementById(tab + '-tab').classList.add('active');
        }

        function previousWeek() {
            const params = new URLSearchParams(window.location.search);
            const currentStart = new Date(params.get('week_start') || '{{ $weekStart->format("Y-m-d") }}');
            currentStart.setDate(currentStart.getDate() - 7);
            params.set('week_start', currentStart.toISOString().split('T')[0]);
            window.location.search = params.toString();
        }

        function nextWeek() {
            const params = new URLSearchParams(window.location.search);
            const currentStart = new Date(params.get('week_start') || '{{ $weekStart->format("Y-m-d") }}');
            currentStart.setDate(currentStart.getDate() + 7);
            params.set('week_start', currentStart.toISOString().split('T')[0]);
            window.location.search = params.toString();
        }
    </script>
</body>
</html>