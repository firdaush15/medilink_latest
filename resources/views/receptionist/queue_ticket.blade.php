<!--queue_ticket.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-In Confirmation - {{ $appointment->patient->user->name }}</title>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; background: white; }
            .ticket { box-shadow: none; border: 2px dashed #333; }
        }
        
        body {
            font-family: 'Courier New', monospace;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 400px;
            width: 100%;
        }
        
        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        
        .alert-success strong {
            display: block;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .ticket {
            width: 100%;
            background: white;
            padding: 20px;
            border: 2px dashed #333;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .ticket-header {
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        
        .hospital-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        
        .patient-name {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin: 15px 0;
            text-transform: uppercase;
        }
        
        .ticket-info {
            text-align: left;
            margin: 15px 0;
            font-size: 14px;
        }
        
        .ticket-info div {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .ticket-info strong {
            flex: 0 0 40%;
        }
        
        .status-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 13px;
        }
        
        .status-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .display-screen-alert {
            background: #2196F3;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        
        .display-screen-alert .icon {
            font-size: 48px;
            display: block;
            margin-bottom: 10px;
        }
        
        .waiting-instruction {
            background: #fff3cd;
            border: 2px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-size: 12px;
            text-align: left;
        }
        
        .waiting-instruction strong {
            display: block;
            margin-bottom: 8px;
            color: #856404;
            font-size: 14px;
        }
        
        .waiting-instruction ul {
            margin: 5px 0 0 20px;
            padding: 0;
        }
        
        .footer {
            border-top: 2px solid #333;
            padding-top: 15px;
            margin-top: 15px;
            font-size: 11px;
            color: #666;
        }
        
        .barcode {
            margin: 15px 0;
        }
        
        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            font-family: Arial, sans-serif;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .btn-print {
            background: #4CAF50;
            color: white;
        }
        
        .btn-print:hover {
            background: #45a049;
        }
        
        .btn-close {
            background: #2196F3;
            color: white;
        }
        
        .btn-close:hover {
            background: #1976D2;
        }
        
        .print-instructions {
            background: #fff3cd;
            border: 1px solid #ffc107;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 13px;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        
        .print-instructions strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        @if(session('success'))
        <div class="alert-success no-print">
            <strong>✓ Check-In Successful!</strong>
            {{ session('success') }}
        </div>
        @endif

        <div class="print-instructions no-print">
            <strong>📋 Instructions:</strong>
            1. Click "Print Ticket" below<br>
            2. Give the printed ticket to the patient<br>
            3. Click "Done - Back to Check-In" when finished
        </div>

        <div class="ticket">
            <div class="ticket-header">
                <p class="hospital-name">🏥 MEDILINK HOSPITAL</p>
                <p style="margin: 5px 0; font-size: 12px;">Check-In Confirmation</p>
            </div>

            <!-- ✅ PATIENT NAME (Most Important) -->
            <div class="patient-name">
                {{ $appointment->patient->user->name }}
            </div>

            <!-- ✅ WATCH DISPLAY SCREEN ALERT -->
            <div class="display-screen-alert">
                <span class="icon">📺</span>
                WATCH THE DISPLAY SCREEN<br>
                <small style="font-size: 14px; font-weight: normal;">for your NAME</small>
            </div>

            <div class="ticket-info">
                <div>
                    <strong>Patient ID:</strong>
                    <span>P{{ str_pad($appointment->patient->patient_id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div>
                    <strong>Doctor:</strong>
                    <span>Dr. {{ $appointment->doctor->user->name }}</span>
                </div>
                <div>
                    <strong>Department:</strong>
                    <span>{{ $appointment->doctor->specialization }}</span>
                </div>
                <div>
                    <strong>Appointment:</strong>
                    <span>{{ $appointment->appointment_time->format('h:i A') }}</span>
                </div>
                <div>
                    <strong>Checked In:</strong>
                    <span>{{ $appointment->arrived_at->format('h:i A') }}</span>
                </div>
                <div>
                    <strong>Date:</strong>
                    <span>{{ $appointment->arrived_at->format('d M Y') }}</span>
                </div>
            </div>

            @php
                $patientsAhead = $appointment->getPatientsAheadCount();
            @endphp

            <div class="status-info {{ $appointment->is_late ? 'status-warning' : '' }}">
                @if($appointment->is_late)
                    <strong>⚠️ LATE ARRIVAL</strong><br>
                    You arrived {{ $appointment->late_penalty_minutes }} minutes after your appointment time.<br>
                    <strong style="color: #856404;">On-time patients will be prioritized.</strong>
                @else
                    <strong>✅ On-Time Arrival</strong><br>
                    Thank you for arriving on time!
                @endif
                
                <div style="margin-top: 10px;">
                    Currently: <strong>{{ $patientsAhead }}</strong> patient(s) ahead of you
                </div>
                
                <div style="margin-top: 8px; font-size: 12px;">
                    Status: <strong>{{ $appointment->getCurrentStageDisplay() }}</strong>
                </div>
            </div>

            @if($appointment->estimated_call_time)
            <div style="text-align: center; margin: 15px 0; font-size: 14px; color: #666;">
                ⏰ Estimated call time: {{ $appointment->estimated_call_time->format('h:i A') }}<br>
                <small>(Times may vary based on consultations)</small>
            </div>
            @endif

            <!-- ✅ IMPORTANT WAITING INSTRUCTIONS -->
            <div class="waiting-instruction">
                <strong>⚠️ IMPORTANT INSTRUCTIONS:</strong>
                <ul>
                    <li><strong>Watch the TV screen</strong> in the waiting area</li>
                    <li><strong>Your NAME will appear</strong> when it's your turn</li>
                    <li><strong>Listen for audio announcements</strong></li>
                    <li><strong>Do NOT leave</strong> the waiting area</li>
                    <li>Proceed immediately when your name is called</li>
                </ul>
            </div>

            <!-- Simple barcode -->
            <div class="barcode">
                <svg width="200" height="50" viewBox="0 0 200 50">
                    <text x="100" y="30" text-anchor="middle" font-family="monospace" font-size="20">
                        *{{ $appointment->appointment_id }}*
                    </text>
                </svg>
            </div>

            <div class="footer">
                <p style="margin: 5px 0;">🪑 Waiting Area: {{ $appointment->doctor->specialization }} Clinic</p>
                <p style="margin: 5px 0;">📺 Watch the display screen for your name</p>
                <p style="margin: 5px 0;">🔊 Listen for audio announcements</p>
            </div>
        </div>

        <div class="actions no-print">
            <button class="btn btn-print" onclick="printTicket()">
                🖨️ Print Ticket
            </button>
            <a href="{{ route('receptionist.check-in') }}" class="btn btn-close">
                ✓ Done - Back to Check-In
            </a>
        </div>
    </div>

    <script>
        function printTicket() {
            window.print();
        }

        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printTicket();
            }
        });
    </script>
</body>
</html>