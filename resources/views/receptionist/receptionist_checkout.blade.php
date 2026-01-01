<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout & Payment - Receptionist</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        /* ✅ NEW: Info Banner */
        .info-banner {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .info-banner h3 {
            font-size: 1.1rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .info-banner ul {
            margin-left: 1.5rem;
            line-height: 1.8;
        }

        /* ✅ NEW: Waiting for Pharmacy Banner */
        .pharmacy-pending-banner {
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .pharmacy-pending-banner .icon {
            font-size: 2rem;
        }

        /* Header */
        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: #2d3748;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .page-header p {
            color: #718096;
            font-size: 0.95rem;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: #718096;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            color: #2d3748;
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-card.pending { border-left: 4px solid #f59e0b; }
        .stat-card.completed { border-left: 4px solid #10b981; }
        .stat-card.revenue { border-left: 4px solid #3b82f6; }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .tab {
            padding: 0.75rem 1.5rem;
            border: 2px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: #4a5568;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab:hover {
            background: #f7fafc;
        }

        .tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* Content Section */
        .content-section {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .section-title {
            color: #2d3748;
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Table */
        .checkout-table {
            width: 100%;
            border-collapse: collapse;
        }

        .checkout-table thead {
            background: #f7fafc;
        }

        .checkout-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            border-bottom: 2px solid #e2e8f0;
        }

        .checkout-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        .checkout-table tbody tr:hover {
            background: #f7fafc;
        }

        /* Patient Info */
        .patient-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .patient-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.25rem;
        }

        .patient-details h4 {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .patient-details p {
            color: #718096;
            font-size: 0.85rem;
        }

        /* Status Badge */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-paid {
            background: #dbeafe;
            color: #1e40af;
        }

        /* Buttons */
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            text-align: center;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-success {
            background: #10b981;
            color: white;
        }

        .btn-success:hover {
            background: #059669;
        }

        .btn-info {
            background: #3b82f6;
            color: white;
        }

        .btn-info:hover {
            background: #2563eb;
        }

        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state svg {
            width: 120px;
            height: 120px;
            margin: 0 auto 1.5rem;
            opacity: 0.3;
        }

        .empty-state h3 {
            color: #4a5568;
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #9ca3af;
        }

        /* Quick Info */
        .quick-info {
            display: flex;
            gap: 2rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .quick-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #4a5568;
        }

        .quick-info-item strong {
            color: #2d3748;
        }

        /* Amount Display */
        .amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: #10b981;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .checkout-table {
                font-size: 0.85rem;
            }

            .checkout-table th,
            .checkout-table td {
                padding: 0.75rem 0.5rem;
            }

            .patient-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .quick-info {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        @include('receptionist.sidebar.receptionist_sidebar')

        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1>💰 Checkout & Payment</h1>
                <p>Process payments and generate receipts for completed consultations</p>
            </div>

            <!-- ✅ NEW: Workflow Info Banner -->
            <div class="info-banner">
                <h3>ℹ️ Checkout Workflow Information</h3>
                <ul>
                    <li><strong>No Prescription:</strong> Patient can proceed directly to checkout after consultation</li>
                    <li><strong>With Prescription:</strong> Patient MUST visit pharmacy first for medication dispensing before checkout</li>
                    <li><strong>Your Role:</strong> Process final payment once all services (consultation + pharmacy) are complete</li>
                </ul>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card pending">
                    <h3>⏳ Pending Checkout</h3>
                    <div class="value">{{ $stats['pending_checkout'] }}</div>
                </div>
                <div class="stat-card completed">
                    <h3>✅ Checked Out Today</h3>
                    <div class="value">{{ $stats['checked_out_today'] }}</div>
                </div>
                <div class="stat-card revenue">
                    <h3>💵 Total Collected</h3>
                    <div class="value">RM {{ number_format($stats['total_collected_today'], 2) }}</div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab active" onclick="showTab('pending')">
                    ⏳ Pending Checkout ({{ $pendingCheckouts->count() }})
                </button>
                <button class="tab" onclick="showTab('history')">
                    📋 Today's History ({{ $checkedOutToday->count() }})
                </button>
            </div>

            <!-- Pending Checkouts -->
            <div id="pending-tab" class="content-section">
                <h2 class="section-title">
                    💳 Ready for Payment
                </h2>

                @if($pendingCheckouts->isEmpty())
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h3>All Clear!</h3>
                    <p>No pending checkouts at the moment. Patients with prescriptions must complete pharmacy visit first.</p>
                </div>
                @else
                <table class="checkout-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Appointment Time</th>
                            <th>Consultation Ended</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingCheckouts as $appointment)
                        <tr>
                            <td>
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        {{ substr($appointment->patient->user->name, 0, 1) }}
                                    </div>
                                    <div class="patient-details">
                                        <h4>{{ $appointment->patient->user->name }}</h4>
                                        <p>ID: P{{ str_pad($appointment->patient_id, 4, '0', STR_PAD_LEFT) }}</p>
                                        @if($appointment->prescriptions->isNotEmpty())
                                        <p style="color: #10b981; font-weight: 600;">✓ Pharmacy Complete</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $appointment->doctor->user->name }}</strong><br>
                                <small style="color: #718096;">{{ $appointment->doctor->specialization }}</small>
                            </td>
                            <td>{{ $appointment->appointment_time->format('h:i A') }}</td>
                            <td>{{ $appointment->consultation_ended_at->format('h:i A') }}</td>
                            <td>
                                <span class="status-badge status-pending">⏳ Pending Payment</span>
                            </td>
                            <td>
                                <a href="{{ route('receptionist.checkout.show', $appointment->appointment_id) }}" 
                                   class="btn btn-success btn-sm">
                                    💳 Process Payment
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Checkout History -->
            <div id="history-tab" class="content-section" style="display: none;">
                <h2 class="section-title">
                    📋 Today's Checkout History
                </h2>

                @if($checkedOutToday->isEmpty())
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3>No Checkouts Yet</h3>
                    <p>No patients have been checked out today.</p>
                </div>
                @else
                <table class="checkout-table">
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Checked Out At</th>
                            <th>Amount Paid</th>
                            <th>Checked Out By</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkedOutToday as $appointment)
                        <tr>
                            <td>
                                <div class="patient-info">
                                    <div class="patient-avatar">
                                        {{ substr($appointment->patient->user->name, 0, 1) }}
                                    </div>
                                    <div class="patient-details">
                                        <h4>{{ $appointment->patient->user->name }}</h4>
                                        <p>ID: P{{ str_pad($appointment->patient_id, 4, '0', STR_PAD_LEFT) }}</p>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>{{ $appointment->doctor->user->name }}</strong><br>
                                <small style="color: #718096;">{{ $appointment->doctor->specialization }}</small>
                            </td>
                            <td>{{ $appointment->checked_out_at->format('h:i A') }}</td>
                            <td>
                                <span class="amount">RM {{ number_format($appointment->payment_amount, 2) }}</span>
                            </td>
                            <td>{{ $appointment->checkedOutBy->name }}</td>
                            <td>
                                <a href="{{ route('receptionist.checkout.receipt', $appointment->appointment_id) }}" 
                                   class="btn btn-info btn-sm" target="_blank">
                                    🖨️ View Receipt
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    <script>
        function showTab(tab) {
            document.getElementById('pending-tab').style.display = 'none';
            document.getElementById('history-tab').style.display = 'none';
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));

            if (tab === 'pending') {
                document.getElementById('pending-tab').style.display = 'block';
                document.querySelectorAll('.tab')[0].classList.add('active');
            } else {
                document.getElementById('history-tab').style.display = 'block';
                document.querySelectorAll('.tab')[1].classList.add('active');
            }
        }

        // Auto-refresh every 30 seconds
        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>