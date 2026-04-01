<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink | Appointment Details</title>
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

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 20px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            margin-bottom: 20px;
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

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            color: #64748b;
            font-size: 14px;
        }

        .info-value {
            color: #1e293b;
            font-weight: 500;
            font-size: 14px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-completed { background: #d1fae5; color: #065f46; }
        .status-confirmed { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-in_consultation { background: #fef3c7; color: #92400e; }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -23px;
            top: 8px;
            width: 2px;
            height: calc(100% - 8px);
            background: #e2e8f0;
        }

        .timeline-item:last-child::before {
            display: none;
        }

        .timeline-dot {
            position: absolute;
            left: -30px;
            top: 0;
            width: 16px;
            height: 16px;
            background: #3b82f6;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 2px #3b82f6;
        }

        .timeline-content {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
        }

        .timeline-time {
            font-size: 12px;
            color: #64748b;
            margin-bottom: 4px;
        }

        .timeline-action {
            font-size: 14px;
            color: #1e293b;
            font-weight: 500;
        }

        .timeline-note {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
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
            <h2>📋 Appointment Details</h2>
            <a href="{{ route('admin.appointments') }}" class="btn-back">← Back to List</a>
        </div>

        <div class="content-grid">
            <div>
                <!-- Appointment Info -->
                <div class="card">
                    <h3 class="card-title">📅 Appointment Information</h3>
                    <div class="info-row">
                        <span class="info-label">Date</span>
                        <span class="info-value">{{ $appointment->appointment_date->format('F d, Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Time</span>
                        <span class="info-value">{{ $appointment->appointment_time->format('h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="status-badge status-{{ $appointment->status }}">
                            {{ ucfirst(str_replace('_', ' ', $appointment->status)) }}
                        </span>
                    </div>
                    @if($appointment->reason)
                    <div class="info-row">
                        <span class="info-label">Reason</span>
                        <span class="info-value">{{ $appointment->reason }}</span>
                    </div>
                    @endif
                </div>

                <!-- Patient Info -->
                <div class="card">
                    <h3 class="card-title">👤 Patient Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value">{{ $appointment->patient->user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Age</span>
                        <span class="info-value">{{ $appointment->patient->age }} years</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender</span>
                        <span class="info-value">{{ $appointment->patient->gender }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $appointment->patient->phone_number }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email</span>
                        <span class="info-value">{{ $appointment->patient->user->email }}</span>
                    </div>
                </div>

                <!-- Doctor Info -->
                <div class="card">
                    <h3 class="card-title">👨‍⚕️ Doctor Information</h3>
                    <div class="info-row">
                        <span class="info-label">Name</span>
                        <span class="info-value">Dr. {{ $appointment->doctor->user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Specialization</span>
                        <span class="info-value">{{ $appointment->doctor->specialization }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone</span>
                        <span class="info-value">{{ $appointment->doctor->phone_number }}</span>
                    </div>
                </div>

                <!-- Vitals (if recorded) -->
                @if($appointment->vitals->isNotEmpty())
                <div class="card">
                    <h3 class="card-title">💉 Vital Signs</h3>
                    @foreach($appointment->vitals as $vital)
                    <div class="info-row">
                        <span class="info-label">Temperature</span>
                        <span class="info-value">{{ $vital->temperature }}°C</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Blood Pressure</span>
                        <span class="info-value">{{ $vital->blood_pressure }} mmHg</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Heart Rate</span>
                        <span class="info-value">{{ $vital->heart_rate }} BPM</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Oxygen Saturation</span>
                        <span class="info-value">{{ $vital->oxygen_saturation }}%</span>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <!-- Workflow Timeline -->
            <div>
                <div class="card">
                    <h3 class="card-title">🔄 Workflow Timeline</h3>
                    <div class="timeline">
                        @forelse($appointment->workflowLogs as $log)
                        <div class="timeline-item">
                            <div class="timeline-dot"></div>
                            <div class="timeline-content">
                                <div class="timeline-time">{{ $log->timestamp->format('M d, Y h:i A') }}</div>
                                <div class="timeline-action">{{ $log->to_stage_display }}</div>
                                @if($log->notes)
                                <div class="timeline-note">{{ $log->notes }}</div>
                                @endif
                                <div class="timeline-note" style="margin-top: 6px;">
                                    By: {{ $log->changedBy->name }} ({{ ucfirst($log->changed_by_type) }})
                                </div>
                            </div>
                        </div>
                        @empty
                        <p style="color: #94a3b8; text-align: center; padding: 20px;">No workflow history available</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>