<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $appointment->patient->user->name }}</title>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white;
            }
            .receipt-container {
                box-shadow: none;
                border: 2px solid #000;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            background: #f3f4f6;
            padding: 2rem;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border: 2px solid #000;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .receipt-header {
            text-align: center;
            border-bottom: 2px dashed #000;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .receipt-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .receipt-header p {
            font-size: 0.9rem;
            margin: 0.25rem 0;
            line-height: 1.6;
        }

        .receipt-header .receipt-title {
            margin-top: 1rem;
            font-size: 1.2rem;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .receipt-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .receipt-info div {
            line-height: 1.8;
        }

        .receipt-info strong {
            display: inline-block;
            width: 140px;
        }

        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }

        .receipt-table th,
        .receipt-table td {
            padding: 0.75rem;
            text-align: left;
            border-bottom: 1px solid #000;
        }

        .receipt-table th {
            background: #000;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        .receipt-table td:last-child,
        .receipt-table th:last-child {
            text-align: right;
        }

        .receipt-table .total-row {
            font-weight: bold;
            font-size: 1.2rem;
            border-top: 2px solid #000;
        }

        .receipt-table .total-row td {
            padding-top: 1rem;
        }

        .receipt-footer {
            border-top: 2px dashed #000;
            padding-top: 1.5rem;
            text-align: center;
        }

        .receipt-footer p {
            margin: 0.5rem 0;
            line-height: 1.6;
        }

        .receipt-footer .thank-you {
            font-weight: bold;
            font-size: 1.1rem;
            margin: 1rem 0;
        }

        .receipt-footer .footer-note {
            margin-top: 1rem;
            font-size: 0.85rem;
            color: #666;
        }

        .actions {
            text-align: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            margin: 0 0.5rem;
            border: 2px solid #000;
            background: white;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.2s;
            font-family: 'Courier New', monospace;
            font-size: 1rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #000;
            color: white;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .receipt-container {
                padding: 1rem;
            }

            .receipt-info {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .receipt-header h1 {
                font-size: 1.5rem;
            }

            .receipt-table {
                font-size: 0.85rem;
            }

            .actions {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
            }

            .btn {
                width: 100%;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Header -->
        <div class="receipt-header">
            <h1>🏥 MEDILINK HOSPITAL</h1>
            <p>123 Medical Street, Kuala Lumpur, Malaysia</p>
            <p>Tel: +60 3-1234 5678 | Email: info@medilink.com</p>
            <p>SSM: 202401234567 | GST: 001234567890</p>
            <p class="receipt-title">PAYMENT RECEIPT</p>
        </div>

        <!-- Receipt Info -->
        <div class="receipt-info">
            <div>
                <strong>Receipt No:</strong> R{{ str_pad($appointment->appointment_id, 6, '0', STR_PAD_LEFT) }}<br>
                <strong>Date:</strong> {{ $appointment->checked_out_at->format('M d, Y') }}<br>
                <strong>Time:</strong> {{ $appointment->checked_out_at->format('h:i A') }}<br>
                <strong>Cashier:</strong> {{ $appointment->checkedOutBy->name }}
            </div>
            <div>
                <strong>Patient:</strong> {{ $appointment->patient->user->name }}<br>
                <strong>Patient ID:</strong> P{{ str_pad($appointment->patient_id, 4, '0', STR_PAD_LEFT) }}<br>
                <strong>Doctor:</strong> {{ $appointment->doctor->user->name }}<br>
                <strong>Department:</strong> {{ $appointment->doctor->specialization }}
            </div>
        </div>

        <!-- Billing Table -->
        <table class="receipt-table">
            <thead>
                <tr>
                    <th>DESCRIPTION</th>
                    <th>AMOUNT (RM)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Consultation Fee ({{ $appointment->doctor->specialization }})</td>
                    <td>{{ number_format($consultationFee, 2) }}</td>
                </tr>
                @if($pharmacyFee > 0)
                <tr>
                    <td>Pharmacy/Medication</td>
                    <td>{{ number_format($pharmacyFee, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Subtotal</td>
                    <td>{{ number_format($subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td>SST (6%)</td>
                    <td>{{ number_format($tax, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>TOTAL PAID</td>
                    <td>{{ number_format($total, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="receipt-footer">
            <p class="thank-you">*** PAID IN FULL ***</p>
            <p>Thank you for choosing MediLink Hospital!</p>
            <p>For inquiries, please contact our front desk.</p>
            <p class="footer-note">
                This is a computer-generated receipt and does not require a signature.<br>
                Please retain this receipt for your records.
            </p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions no-print">
        <button onclick="window.print()" class="btn">🖨️ Print Receipt</button>
        <button onclick="window.close()" class="btn">✕ Close</button>
        <a href="{{ route('receptionist.checkout.index') }}" class="btn">← Back to Checkout</a>
    </div>

    <script>
        // Auto-print on load (optional - comment out if not needed)
        // window.onload = function() {
        //     window.print();
        // }
    </script>
</body>
</html>