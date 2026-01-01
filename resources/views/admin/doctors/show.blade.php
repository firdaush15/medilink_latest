<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Doctor Profile</title>
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
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e7ff;
        }

        .page-header h2 {
            font-size: 28px;
            color: #1e293b;
            margin: 0;
        }

        .btn-back {
            background: #64748b;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-back:hover {
            background: #475569;
            transform: translateY(-2px);
        }

        .content-grid {
            display: grid;
            grid-template-columns: 350px 1fr;
            gap: 20px;
        }

        .profile-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            margin: 0 auto 20px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #e0e7ff;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            font-weight: 600;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 8px;
        }

        .profile-specialty {
            color: #64748b;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .status-available { background: #d1fae5; color: #065f46; }
        .status-on-leave { background: #fef3c7; color: #92400e; }
        .status-unavailable { background: #fee2e2; color: #991b1b; }

        .profile-info {
            text-align: left;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f1f5f9;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 8px;
            background: #f8fafc;
            border-radius: 8px;
        }

        .info-icon {
            font-size: 20px;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }

        .stats-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .stat-box {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid;
        }

        .stat-box.total { border-left-color: #3b82f6; }
        .stat-box.completed { border-left-color: #10b981; }
        .stat-box.cancelled { border-left-color: #ef4444; }
        .stat-box.rating { border-left-color: #f59e0b; }

        .stat-label {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
        }

        .leave-notice {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .leave-notice h4 {
            margin: 0 0 10px 0;
            color: #92400e;
            font-size: 18px;
        }

        .leave-notice p {
            margin: 5px 0;
            color: #78350f;
            font-size: 14px;
        }

        .recent-appointments {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .appointment-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.2s;
        }

        .appointment-item:hover {
            background: #f1f5f9;
            transform: translateX(5px);
        }

        .appointment-date {
            font-weight: 600;
            color: #1e293b;
        }

        .appointment-patient {
            color: #64748b;
            font-size: 14px;
        }

        .appointment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .no-appointments {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        @media (max-width: 968px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <div class="page-header">
            <h2>👨‍⚕️ Doctor Profile</h2>
            <a href="{{ route('admin.doctors') }}" class="btn-back">
                ← Back to List
            </a>
        </div>

        <div class="content-grid">
            <!-- Left Column: Profile Card -->
            <div>
                <div class="profile-card">
                    <div class="profile-avatar">
                        @if($doctor->profile_photo)
                            <img src="{{ asset('storage/' . $doctor->profile_photo) }}" alt="{{ $doctor->user->name }}">
                        @else
                            <div class="avatar-placeholder">{{ substr($doctor->user->name, 0, 1) }}</div>
                        @endif
                    </div>

                    <h3 class="profile-name">Dr. {{ $doctor->user->name }}</h3>
                    <p class="profile-specialty">{{ $doctor->specialization }}</p>

                    <span class="status-badge {{ $doctor->availability_status === 'Available' ? 'status-available' : ($doctor->availability_status === 'On Leave' ? 'status-on-leave' : 'status-unavailable') }}">
                        {{ $doctor->availability_status }}
                    </span>

                    <div class="profile-info">
                        <div class="info-item">
                            <span class="info-icon">📞</span>
                            <div class="info-content">
                                <div class="info-label">Phone</div>
                                <div class="info-value">{{ $doctor->phone_number }}</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <span class="info-icon">✉️</span>
                            <div class="info-content">
                                <div class="info-label">Email</div>
                                <div class="info-value">{{ $doctor->user->email }}</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <span class="info-icon">🏥</span>
                            <div class="info-content">
                                <div class="info-label">Department</div>
                                <div class="info-value">{{ $doctor->specialization }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Details -->
            <div>
                <!-- Current Leave Notice -->
                @if($currentLeave)
                <div class="leave-notice">
                    <h4>🏖️ Currently on Leave</h4>
                    <p><strong>Period:</strong> {{ $currentLeave->start_date->format('M d, Y') }} - {{ $currentLeave->end_date->format('M d, Y') }}</p>
                    <p><strong>Type:</strong> {{ $currentLeave->leave_type }}</p>
                    @if($currentLeave->reason)
                    <p><strong>Reason:</strong> {{ $currentLeave->reason }}</p>
                    @endif
                </div>
                @endif

                <!-- Performance Stats -->
                <div class="stats-section">
                    <h4 class="section-title">📊 Performance Metrics</h4>
                    <div class="stats-grid">
                        <div class="stat-box total">
                            <div class="stat-label">Total Appointments</div>
                            <div class="stat-number">{{ $totalAppointments }}</div>
                        </div>

                        <div class="stat-box completed">
                            <div class="stat-label">Completed</div>
                            <div class="stat-number">{{ $completedAppointments }}</div>
                        </div>

                        <div class="stat-box cancelled">
                            <div class="stat-label">Cancelled</div>
                            <div class="stat-number">{{ $cancelledAppointments }}</div>
                        </div>

                        <div class="stat-box rating">
                            <div class="stat-label">Average Rating</div>
                            <div class="stat-number">{{ number_format($averageRating ?? 0, 1) }} ⭐</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Appointments -->
                <div class="recent-appointments">
                    <h4 class="section-title">📅 Recent Appointments</h4>
                    @php
                        $recentAppointments = $doctor->appointments()
                            ->with('patient.user')
                            ->latest('appointment_date')
                            ->take(5)
                            ->get();
                    @endphp

                    @forelse($recentAppointments as $apt)
                    <div class="appointment-item">
                        <div>
                            <div class="appointment-date">{{ $apt->appointment_date->format('M d, Y') }} at {{ $apt->appointment_time->format('h:i A') }}</div>
                            <div class="appointment-patient">Patient: {{ $apt->patient->user->name }}</div>
                        </div>
                        <span class="appointment-status status-{{ $apt->status }}">{{ ucfirst($apt->status) }}</span>
                    </div>
                    @empty
                    <div class="no-appointments">
                        <p>No recent appointments</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</body>
</html>