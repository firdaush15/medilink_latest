<!-- resources\views\pharmacist\pharmacist_restockShow.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restock Request Details - {{ $request->request_number }}</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
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

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            margin-left: 280px; /* Add this - adjust based on your sidebar width */
        }

        .breadcrumb {
            display: flex;
            gap: 8px;
            align-items: center;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: #3182ce;
            text-decoration: none;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e2e8f0;
        }

        .header-left h1 {
            font-size: 28px;
            color: #1a202c;
            margin-bottom: 10px;
        }

        .header-meta {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-urgent {
            background: #fed7aa;
            color: #92400e;
        }

        .badge-normal {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-ordered {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #3b82f6;
            color: white;
        }

        .btn-primary:hover {
            background: #2563eb;
        }

        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #d1d5db;
        }

        .btn-danger {
            background: #ef4444;
            color: white;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .detail-card h3 {
            font-size: 16px;
            color: #2d3748;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f7fafc;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            color: #718096;
            font-size: 14px;
        }

        .detail-value {
            color: #2d3748;
            font-weight: 600;
            font-size: 14px;
            text-align: right;
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .alert-warning {
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
            color: #92400e;
        }

        .alert-danger {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .timeline {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .timeline h3 {
            font-size: 18px;
            color: #2d3748;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid #e2e8f0;
        }

        .timeline-item {
            display: flex;
            gap: 16px;
            padding: 16px 0;
            border-left: 2px solid #e5e7eb;
            margin-left: 12px;
            padding-left: 24px;
            position: relative;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 24px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #3b82f6;
        }

        .timeline-item:last-child {
            border-left: none;
        }

        .timeline-content {
            flex: 1;
        }

        .timeline-time {
            font-size: 13px;
            color: #6b7280;
        }

        .timeline-action {
            font-weight: 600;
            color: #1f2937;
            margin: 4px 0;
        }

        .timeline-notes {
            font-size: 14px;
            color: #4b5563;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="{{ route('pharmacist.dashboard') }}">Dashboard</a>
            <span>/</span>
            <a href="{{ route('pharmacist.restock.index') }}">Restock Requests</a>
            <span>/</span>
            <span>{{ $request->request_number }}</span>
        </div>

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>{{ $request->request_number }}</h1>
                <div class="header-meta">
                    <span class="badge badge-{{ strtolower($request->priority) }}">
                        {{ $request->priority }}
                    </span>
                    <span class="badge badge-{{ strtolower(str_replace(' ', '-', $request->status)) }}">
                        {{ $request->status }}
                    </span>
                </div>
            </div>
            <div class="action-buttons">
                @if($request->status == 'Approved')
                <button class="btn btn-primary" onclick="showMarkOrderedModal()">
                    Mark as Ordered
                </button>
                @endif
                @if($request->status == 'Pending')
                <form action="{{ route('pharmacist.restock.cancel', $request->request_id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" onclick="return confirm('Cancel this request?')">
                        Cancel Request
                    </button>
                </form>
                @endif
                <a href="{{ route('pharmacist.restock.index') }}" class="btn btn-secondary">← Back</a>
            </div>
        </div>

        <!-- Status Alerts -->
        @if($request->status == 'Approved')
        <div class="alert alert-success">
            <span style="font-size: 24px;">✅</span>
            <div>
                <strong>Request Approved!</strong> You can now proceed to order from the supplier.
            </div>
        </div>
        @elseif($request->status == 'Rejected')
        <div class="alert alert-danger">
            <span style="font-size: 24px;">❌</span>
            <div>
                <strong>Request Rejected:</strong> {{ $request->rejection_reason }}
            </div>
        </div>
        @elseif($request->status == 'Pending')
        <div class="alert alert-warning">
            <span style="font-size: 24px;">⏳</span>
            <div>
                <strong>Awaiting Admin Approval</strong>
            </div>
        </div>
        @endif

        <!-- Details Grid -->
        <div class="details-grid">
            <!-- Medicine Details -->
            <div class="detail-card">
                <h3>💊 Medicine Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Medicine Name</span>
                    <span class="detail-value">{{ $request->medicine->medicine_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Form & Strength</span>
                    <span class="detail-value">{{ $request->medicine->form }} {{ $request->medicine->strength }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Current Stock</span>
                    <span class="detail-value" style="color: {{ $request->current_stock <= $request->medicine->reorder_level ? '#ef4444' : '#10b981' }}">
                        {{ $request->current_stock }} units
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reorder Level</span>
                    <span class="detail-value">{{ $request->medicine->reorder_level }} units</span>
                </div>
            </div>

            <!-- Request Details -->
            <div class="detail-card">
                <h3>📋 Request Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Quantity Requested</span>
                    <span class="detail-value" style="color: #3b82f6; font-size: 18px;">{{ $request->quantity_requested }} units</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Estimated Cost</span>
                    <span class="detail-value" style="color: #10b981; font-size: 18px;">RM {{ number_format($request->estimated_total_cost, 2) }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Preferred Supplier</span>
                    <span class="detail-value">{{ $request->preferred_supplier ?? 'Not specified' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Requested Date</span>
                    <span class="detail-value">{{ $request->created_at->format('M d, Y H:i') }}</span>
                </div>
            </div>

            <!-- Approval Info (if approved/rejected) -->
            @if($request->approved_by)
            <div class="detail-card">
                <h3>✅ Approval Information</h3>
                <div class="detail-row">
                    <span class="detail-label">Approved By</span>
                    <span class="detail-value">{{ $request->approvedBy->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Approval Date</span>
                    <span class="detail-value">{{ $request->approved_at?->format('M d, Y H:i') }}</span>
                </div>
                @if($request->approval_notes)
                <div class="detail-row">
                    <span class="detail-label">Notes</span>
                    <span class="detail-value">{{ $request->approval_notes }}</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- Justification -->
        <div class="detail-card" style="margin-bottom: 30px;">
            <h3>📝 Justification</h3>
            <p style="color: #4b5563; line-height: 1.6;">{{ $request->justification }}</p>
        </div>

        <!-- Timeline -->
        <div class="timeline">
            <h3>📊 Request Timeline</h3>
            @foreach($request->logs as $log)
            <div class="timeline-item">
                <div class="timeline-content">
                    <div class="timeline-action">
                        {{ ucfirst($log->action) }} - {{ $log->to_status }}
                    </div>
                    <div class="timeline-time">
                        {{ $log->performed_at->format('M d, Y H:i A') }} by {{ $log->performedBy->name }}
                    </div>
                    @if($log->notes)
                    <div class="timeline-notes">{{ $log->notes }}</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Mark as Ordered Modal -->
    <div class="modal" id="markOrderedModal">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px;">Mark as Ordered</h3>
            <form action="{{ route('pharmacist.restock.mark-ordered', $request->request_id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label>Purchase Order Number *</label>
                    <input type="text" name="purchase_order_number" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Expected Delivery Date</label>
                    <input type="date" name="expected_delivery_date" class="form-control" min="{{ date('Y-m-d') }}">
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showMarkOrderedModal() {
            document.getElementById('markOrderedModal').classList.add('active');
        }

        function closeModal() {
            document.getElementById('markOrderedModal').classList.remove('active');
        }
    </script>
</body>

</html>