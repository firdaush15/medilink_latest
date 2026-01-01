<!--nurse_queueManagement.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Patient Queue - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f5f7fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .page-header {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Assigned Badge */
        .assigned-badge {
            background: #2196F3;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            margin-left: 10px;
        }

        /* Current Patient Card */
        .current-patient-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .current-patient-card h2 {
            font-size: 24px;
            margin-bottom: 10px;
            opacity: 0.9;
        }

        .current-patient-card .name {
            font-size: 48px;
            font-weight: bold;
            margin: 20px 0;
            text-transform: uppercase;
        }

        .patient-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
        }

        .detail-item {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }

        .detail-item strong {
            display: block;
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-call {
            flex: 1;
            background: #4CAF50;
            color: white;
            border: none;
            padding: 20px;
            font-size: 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-call:hover {
            background: #45a049;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.4);
        }

        /* Upcoming Queue */
        .upcoming-queue {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }

        .upcoming-queue h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
        }

        .queue-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .queue-item:hover {
            background: #e8f4f8;
            transform: translateX(5px);
        }

        .queue-item .position {
            width: 50px;
            height: 50px;
            background: #2196F3;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            margin-right: 20px;
        }

        .queue-item .info {
            flex: 1;
        }

        .queue-item .name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }

        .queue-item .meta {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        /* Empty State */
        .empty-queue {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-queue svg {
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .empty-queue h3 {
            color: #666;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <div class="page-header">
            <h1>📋 My Patient Queue <span class="assigned-badge">✅ Assigned to Me</span></h1>
            <p>Call your assigned patients in priority order</p>
        </div>

        @if(session('success'))
        <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            ✓ {{ session('success') }}
        </div>
        @endif

        @if($nextPatient)
        <div class="current-patient-card">
            <h2>🔔 NEXT PATIENT TO CALL (Assigned to You)</h2>
            <div class="name">{{ $nextPatient->patient->user->name }}</div>
            
            <div class="patient-details">
                <div class="detail-item">
                    <strong>Patient ID</strong>
                    <div>P{{ str_pad($nextPatient->patient->patient_id, 4, '0', STR_PAD_LEFT) }}</div>
                </div>
                <div class="detail-item">
                    <strong>Age / Gender</strong>
                    <div>{{ $nextPatient->patient->age }} / {{ $nextPatient->patient->gender }}</div>
                </div>
                <div class="detail-item">
                    <strong>Waiting Time</strong>
                    <div>{{ $nextPatient->arrived_at->diffForHumans(null, true) }}</div>
                </div>
            </div>

            <div class="patient-details">
                <div class="detail-item">
                    <strong>Appointment Time</strong>
                    <div>{{ $nextPatient->appointment_time->format('h:i A') }}</div>
                </div>
                <div class="detail-item">
                    <strong>Doctor</strong>
                    <div>Dr. {{ $nextPatient->doctor->user->name }}</div>
                </div>
                <div class="detail-item">
                    <strong>Queue Position</strong>
                    <div>#{{ $nextPatient->queue_number ?? '?' }}</div>
                </div>
            </div>
            
            <div class="action-buttons">
                <form action="{{ route('nurse.call-patient', $nextPatient->appointment_id) }}" method="POST" style="flex: 1;">
                    @csrf
                    <button type="submit" class="btn-call">
                        📢 CALL PATIENT<br>
                        <small style="font-size: 14px; font-weight: normal;">(Display on TV + Start Vitals Recording)</small>
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="empty-queue" style="background: white; padding: 60px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.1);">
            <svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
            </svg>
            <h3>No Patients Assigned to You</h3>
            <p>All your assigned patients have been called or there are no new assignments.</p>
            <p style="margin-top: 10px; color: #999; font-size: 14px;">
                When receptionist checks in patients for your doctors, they will appear here.
            </p>
        </div>
        @endif

        @if($upcomingPatients && $upcomingPatients->count() > 0)
        <div class="upcoming-queue">
            <h3>⏳ My Upcoming Patients ({{ $upcomingPatients->count() }})</h3>
            
            @foreach($upcomingPatients as $index => $patient)
            <div class="queue-item">
                <div class="position">{{ $index + 1 }}</div>
                <div class="info">
                    <div class="name">
                        {{ $patient->patient->user->name }}
                        <span class="assigned-badge">Assigned to You</span>
                    </div>
                    <div class="meta">
                        Appointment: {{ $patient->appointment_time->format('h:i A') }} • 
                        Waiting: {{ $patient->arrived_at->diffForHumans(null, true) }} • 
                        Dr. {{ $patient->doctor->user->name }}
                        @if($patient->is_late)
                            <span style="background: #ff9800; color: white; padding: 2px 8px; border-radius: 4px; font-size: 12px; margin-left: 8px;">⚠️ Late</span>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <!-- Info Box -->
        <div style="background: #e3f2fd; border-left: 4px solid #2196F3; padding: 20px; border-radius: 8px; margin-top: 30px;">
            <h3 style="margin-bottom: 10px; color: #1565c0;">ℹ️ How Assignment Queue Works</h3>
            <ul style="color: #555; line-height: 1.8; padding-left: 20px;">
                <li><strong>Smart Assignment:</strong> System assigns patients to you based on your doctor team</li>
                <li><strong>Your Queue Only:</strong> You only see patients assigned to YOU</li>
                <li><strong>Priority Order:</strong> Patients sorted by appointment time + arrival time</li>
                <li><strong>TV Display:</strong> When you call, patient name shows on waiting room screen</li>
                <li><strong>One Patient at a Time:</strong> Call next patient when you finish current one</li>
            </ul>
        </div>
    </div>
    
    <script>
        // Auto-refresh every 30 seconds
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>