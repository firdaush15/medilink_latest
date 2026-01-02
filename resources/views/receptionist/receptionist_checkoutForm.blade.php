<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Process Payment - Receptionist</title>
    @vite(['resources/css/doctor/doctor_sidebar.css'])
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

        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            overflow: hidden;
        }

        .form-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
        }

        .form-header h1 {
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }

        .form-body {
            padding: 2rem;
        }

        .info-section {
            background: #f7fafc;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }

        .info-item h4 {
            color: #718096;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .info-item p {
            color: #2d3748;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .billing-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .billing-table th,
        .billing-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .billing-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .total-row {
            font-size: 1.25rem;
            font-weight: 700;
            color: #2d3748;
        }

        .total-row td {
            border-top: 2px solid #2d3748;
            padding-top: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .payment-methods {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .payment-method {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 1.5rem 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .payment-method:hover {
            border-color: #667eea;
        }

        .payment-method.selected {
            border-color: #667eea;
            background: #f0f4ff;
        }

        .payment-method input[type="radio"] {
            display: none;
        }

        .payment-method-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            flex: 1;
            text-decoration: none;
            text-align: center;
            display: inline-block;
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
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        @include('receptionist.sidebar.receptionist_sidebar')

        <div class="main-content">
            <div class="form-container">
                <div class="form-header">
                    <h1>💳 Process Payment</h1>
                    <p>Complete checkout for {{ $appointment->patient->user->name }}</p>
                </div>

                <div class="form-body">
                    @if(session('error'))
                    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        <strong>❌ Error:</strong> {{ session('error') }}
                    </div>
                    @endif

                    @if ($errors->any())
                    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: 0.5rem; margin-bottom: 1.5rem;">
                        <strong>❌ Validation Errors:</strong>
                        <ul style="margin-left: 1.5rem; margin-top: 0.5rem;">
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <!-- Patient & Appointment Info -->
                    <div class="info-section">
                        <div class="info-grid">
                            <div class="info-item">
                                <h4>Patient</h4>
                                <p>{{ $appointment->patient->user->name }}</p>
                            </div>
                            <div class="info-item">
                                <h4>Doctor</h4>
                                <p>{{ $appointment->doctor->user->name }}</p>
                            </div>
                            <div class="info-item">
                                <h4>Date</h4>
                                <p>{{ $appointment->appointment_date->format('M d, Y') }}</p>
                            </div>
                            <div class="info-item">
                                <h4>Time</h4>
                                <p>{{ $appointment->appointment_time->format('h:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Breakdown -->
                    <table class="billing-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="text-align: right;">Amount (RM)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Consultation Fee ({{ $appointment->doctor->specialization }})</td>
                                <td style="text-align: right;">{{ number_format($consultationFee, 2) }}</td>
                            </tr>
                            @if($pharmacyFee > 0)
                            <tr>
                                <td>Pharmacy/Medication</td>
                                <td style="text-align: right;">{{ number_format($pharmacyFee, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td><strong>Subtotal</strong></td>
                                <td style="text-align: right;"><strong>{{ number_format($subtotal, 2) }}</strong></td>
                            </tr>
                            <tr>
                                <td>SST (6%)</td>
                                <td style="text-align: right;">{{ number_format($tax, 2) }}</td>
                            </tr>
                            <tr class="total-row">
                                <td>TOTAL</td>
                                <td style="text-align: right;">{{ number_format($total, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Payment Form -->
                    <form action="{{ route('receptionist.checkout.process', $appointment->appointment_id) }}" method="POST">
                        @csrf

                        <div class="form-group">
                            <label>Payment Method *</label>
                            <div class="payment-methods">
                                <label class="payment-method selected">
                                    <input type="radio" name="payment_method" value="cash" checked>
                                    <div class="payment-method-icon">💵</div>
                                    <div>Cash</div>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="card">
                                    <div class="payment-method-icon">💳</div>
                                    <div>Card</div>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="online">
                                    <div class="payment-method-icon">📱</div>
                                    <div>Online</div>
                                </label>
                                <label class="payment-method">
                                    <input type="radio" name="payment_method" value="insurance">
                                    <div class="payment-method-icon">🏥</div>
                                    <div>Insurance</div>
                                </label>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Amount Paid (RM) *</label>
                            <input type="number" name="amount_paid" step="0.01" value="{{ $total }}" required>
                        </div>

                        <div class="form-group">
                            <label>Notes (Optional)</label>
                            <textarea name="notes" rows="3" placeholder="Any additional notes..."></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="{{ route('receptionist.checkout.index') }}" class="btn btn-secondary">
                                ← Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                ✓ Complete Payment & Print Receipt
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                document.querySelectorAll('.payment-method').forEach(m => m.classList.remove('selected'));
                this.classList.add('selected');
            });
        });
    </script>
</body>

</html>