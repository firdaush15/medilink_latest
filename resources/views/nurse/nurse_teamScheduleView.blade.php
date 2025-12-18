<!--nurse_teamScheduleView.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Team & Schedule - Nurse Dashboard</title>
    @vite(['resources/css/nurse/nurse_sidebar.css'])
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
            color: #4CAF50;
            border-bottom-color: #4CAF50;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* My Doctors Section */
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

        .doctor-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
        }

        .doctor-card {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            position: relative;
        }

        .assignment-type-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.25);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .doctor-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .doctor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: 700;
        }

        .doctor-details h3 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .doctor-specialization {
            font-size: 14px;
            opacity: 0.9;
        }

        .assignment-schedule {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid rgba(255,255,255,0.2);
            font-size: 13px;
        }

        .schedule-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
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
            transition: all 0.3s;
        }

        .week-nav button:hover {
            background: #f8f9fa;
        }

        .current-week {
            font-weight: 700;
            font-size: 18px;
            color: #1a202c;
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
            border: 2px solid transparent;
            transition: all 0.3s;
        }

        .day-card.today {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
            border-color: #4CAF50;
        }

        .day-card.has-shift {
            border-color: #4CAF50;
        }

        .day-header {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .day-date {
            font-size: 12px;
            opacity: 0.7;
        }

        .shift-info {
            background: white;
            padding: 12px;
            border-radius: 8px;
            margin-top: 10px;
            font-size: 13px;
        }

        .day-card.today .shift-info {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .shift-time {
            font-weight: 700;
            margin-bottom: 5px;
        }

        .shift-template {
            font-size: 11px;
            opacity: 0.8;
        }

        .no-shift {
            color: #adb5bd;
            font-size: 13px;
            margin-top: 10px;
            padding: 10px;
        }

        /* Leave Section */
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
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #4CAF50;
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
            font-weight: 600;
        }

        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #2c3e50;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .btn {
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4CAF50 0%, #388E3C 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4);
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
            border-left: 4px solid #dee2e6;
        }

        .leave-item.pending {
            border-left-color: #FF9800;
        }

        .leave-item.approved {
            border-left-color: #4CAF50;
        }

        .leave-item.rejected {
            border-left-color: #f44336;
        }

        .leave-status {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
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
            border-left: 4px solid;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #adb5bd;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #6c757d;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <div class="page-header">
            <h1>👥 My Team & Schedule</h1>
            <p>View assigned doctors, manage shifts, and apply for leave</p>

            <div class="tabs-container">
                <button class="tab-btn active" onclick="switchTab('team')">
                    👨‍⚕️ My Doctors
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

        <!-- MY DOCTORS TAB -->
        <div id="team-tab" class="tab-content active">
            <div class="team-section">
                <div class="team-header">
                    <h2>Assigned Doctors</h2>
                    <span style="color: #718096;">{{ $assignedDoctors->count() }} doctors assigned</span>
                </div>

                @if($assignedDoctors->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">👨‍⚕️</div>
                    <h3>No Doctors Assigned Yet</h3>
                    <p>You haven't been assigned to any doctors yet. Contact administration for assignments.</p>
                </div>
                @else
                <div class="doctor-cards">
                    @foreach($assignedDoctors as $doctor)
                    <div class="doctor-card">
                        <div class="assignment-type-badge">
                            {{ ucfirst($doctor->pivot->assignment_type ?? 'Primary') }}
                        </div>
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

                        <div class="assignment-schedule">
                            <div class="schedule-row">
                                <span>Priority:</span>
                                <strong>#{{ $doctor->pivot->priority_order ?? 1 }}</strong>
                            </div>
                            @if($doctor->pivot->working_days)
                            <div class="schedule-row">
                                <span>Days:</span>
                                <strong>{{ implode(', ', array_map(fn($d) => substr($d, 0, 3), $doctor->pivot->working_days)) }}</strong>
                            </div>
                            @endif
                            @if($doctor->pivot->shift_start && $doctor->pivot->shift_end)
                            <div class="schedule-row">
                                <span>Hours:</span>
                                <strong>
                                    {{ \Carbon\Carbon::parse($doctor->pivot->shift_start)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($doctor->pivot->shift_end)->format('H:i') }}
                                </strong>
                            </div>
                            @endif
                            <div class="schedule-row">
                                <span>Status:</span>
                                <strong>{{ $doctor->pivot->is_active ? '✓ Active' : '✗ Inactive' }}</strong>
                            </div>
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

                        <div class="day-card {{ $isToday ? 'today' : '' }} {{ $dayShift ? 'has-shift' : '' }}">
                            <div class="day-header">
                                {{ $day->format('D') }}
                            </div>
                            <div class="day-date">{{ $day->format('M d') }}</div>

                            @if($dayShift)
                            <div class="shift-info">
                                <div class="shift-time">
                                    ⏰ {{ $dayShift->start_time->format('H:i') }} - {{ $dayShift->end_time->format('H:i') }}
                                </div>
                                <div class="shift-template">
                                    {{ $dayShift->template->template_name ?? 'Custom' }}
                                </div>
                                <div style="margin-top: 8px; font-weight: 600; font-size: 11px;">
                                    {{ ucfirst($dayShift->status) }}
                                </div>
                            </div>
                            @else
                            <div class="no-shift">
                                <div style="font-size: 24px; margin-bottom: 5px;">😴</div>
                                Off Day
                            </div>
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

                <div class="form-section">
                    <h3 style="margin-bottom: 20px;">Apply for Leave</h3>
                    <form action="{{ route('nurse.leave.apply') }}" method="POST">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <label>Leave Type <span style="color: #e74c3c;">*</span></label>
                                <select name="leave_type" required>
                                    <option value="">Select Type</option>
                                    <option value="annual">Annual Leave</option>
                                    <option value="sick">Sick Leave</option>
                                    <option value="emergency">Emergency Leave</option>
                                    <option value="maternity">Maternity Leave</option>
                                    <option value="unpaid">Unpaid Leave</option>
                                </select>
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

                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date <span style="color: #e74c3c;">*</span></label>
                                <input type="date" name="start_date" required min="{{ now()->format('Y-m-d') }}">
                            </div>

                            <div class="form-group">
                                <label>End Date <span style="color: #e74c3c;">*</span></label>
                                <input type="date" name="end_date" required min="{{ now()->format('Y-m-d') }}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Reason for Leave <span style="color: #e74c3c;">*</span></label>
                            <textarea name="reason" required placeholder="Please provide detailed reason for your leave application..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            📝 Submit Leave Request
                        </button>
                    </form>
                </div>

                <div class="leave-history">
                    <h3 style="margin-bottom: 20px;">Recent Leave Requests</h3>
                    @forelse($recentLeaves as $leave)
                    <div class="leave-item {{ $leave->status }}">
                        <div>
                            <div style="font-weight: 700; margin-bottom: 5px; font-size: 16px;">
                                {{ ucfirst($leave->leave_type) }} Leave
                            </div>
                            <div style="font-size: 13px; color: #718096; margin-bottom: 5px;">
                                📅 {{ $leave->start_date->format('M d, Y') }} - {{ $leave->end_date->format('M d, Y') }}
                                ({{ $leave->days }} {{ $leave->days > 1 ? 'days' : 'day' }})
                            </div>
                            <div style="font-size: 12px; color: #6c757d;">
                                {{ Str::limit($leave->reason, 100) }}
                            </div>
                        </div>
                        <span class="leave-status {{ $leave->status }}">
                            {{ ucfirst($leave->status) }}
                        </span>
                    </div>
                    @empty
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <h3>No Leave Requests Yet</h3>
                        <p>You haven't applied for any leave yet</p>
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

        // Validate dates
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const startDate = new Date(document.querySelector('input[name="start_date"]').value);
            const endDate = new Date(document.querySelector('input[name="end_date"]').value);

            if (endDate < startDate) {
                e.preventDefault();
                alert('End date must be after or equal to start date!');
            }
        });
    </script>
</body>
</html>