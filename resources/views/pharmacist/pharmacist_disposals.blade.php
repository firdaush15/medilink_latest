<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disposal Records - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .main-content {
            margin-left: 260px;
            padding: 30px;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .top-bar h1 {
            font-size: 28px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-create {
            background: #3182ce;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-create:hover {
            background: #2c5282;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(49, 130, 206, 0.3);
        }

        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: #c6f6d5;
            border-left: 4px solid #22543d;
            color: #22543d;
        }

        .disposal-table-container {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .disposal-table {
            width: 100%;
            border-collapse: collapse;
        }

        .disposal-table th {
            background: #f7fafc;
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: #2d3748;
            border-bottom: 2px solid #e2e8f0;
            font-size: 14px;
        }

        .disposal-table td {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            color: #4a5568;
            font-size: 14px;
        }

        .disposal-table tr:hover {
            background: #f7fafc;
        }

        .disposal-number {
            font-weight: 700;
            color: #3182ce;
            font-size: 14px;
        }

        .medicine-info strong {
            display: block;
            color: #2d3748;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .medicine-info small {
            color: #718096;
            font-size: 13px;
        }

        /* ✅ CRITICAL FIX: Show quantity as disposed amount (not negative) */
        .quantity-disposed {
            color: #c53030;
            font-weight: 700;
            font-size: 16px;
        }

        .quantity-units {
            color: #718096;
            font-size: 13px;
            font-weight: 400;
        }

        .badge-reason {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .badge-expired {
            background: #fed7d7;
            color: #742a2a;
        }

        .badge-near-expiry {
            background: #feebc8;
            color: #7c2d12;
        }

        .badge-damaged {
            background: #e2e8f0;
            color: #2d3748;
        }

        .badge-contaminated {
            background: #fef5e7;
            color: #7c2d12;
        }

        .badge-other {
            background: #f0f0f0;
            color: #4a5568;
        }

        .method-badge {
            display: inline-block;
            padding: 4px 10px;
            background: #edf2f7;
            color: #4a5568;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .loss-amount {
            color: #c53030;
            font-weight: 700;
            font-size: 15px;
        }

        .date-info {
            color: #718096;
            font-size: 13px;
        }

        .date-info strong {
            display: block;
            color: #2d3748;
            margin-bottom: 2px;
        }

        .disposed-by-info {
            font-size: 14px;
            color: #4a5568;
        }

        .btn-view {
            background: #3182ce;
            color: white;
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-view:hover {
            background: #2c5282;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state svg {
            color: #cbd5e0;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #4a5568;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #718096;
        }

        .pagination {
            padding: 20px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .pagination a {
            padding: 8px 12px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            text-decoration: none;
            color: #4a5568;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }

        .pagination .active {
            background: #3182ce;
            color: white;
            border-color: #3182ce;
        }
    </style>
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>🗑️ Medicine Disposal Records</h1>
            <a href="{{ route('pharmacist.disposals.create') }}" class="btn-create">
                ➕ Record New Disposal
            </a>
        </div>

        <!-- Success Message -->
        @if(session('success'))
        <div class="alert alert-success">
            <strong>✓</strong> {{ session('success') }}
        </div>
        @endif

        <!-- Disposals Table -->
        <div class="disposal-table-container">
            @if($disposals->count() > 0)
            <table class="disposal-table">
                <thead>
                    <tr>
                        <th>Disposal #</th>
                        <th>Medicine</th>
                        <th>Quantity Disposed</th>
                        <th>Batch Number</th>
                        <th>Reason</th>
                        <th>Method</th>
                        <th>Estimated Loss</th>
                        <th>Disposed By</th>
                        <th>Disposal Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($disposals as $disposal)
                    <tr>
                        <td>
                            <span class="disposal-number">{{ $disposal->disposal_number }}</span>
                        </td>
                        
                        <td>
                            <div class="medicine-info">
                                <strong>{{ $disposal->medicine->medicine_name }}</strong>
                                <small>{{ $disposal->medicine->form }} {{ $disposal->medicine->strength }}</small>
                            </div>
                        </td>

                        <!-- ✅ CRITICAL FIX: Show positive quantity -->
                        <td>
                            <span class="quantity-disposed">{{ $disposal->quantity_disposed }}</span>
                            <span class="quantity-units">units</span>
                        </td>

                        <td>{{ $disposal->batch_number ?? 'N/A' }}</td>

                        <td>
                            @php
                                $badgeClass = match($disposal->reason) {
                                    'Expired' => 'badge-expired',
                                    'Near Expiry' => 'badge-near-expiry',
                                    'Damaged' => 'badge-damaged',
                                    'Contaminated' => 'badge-contaminated',
                                    default => 'badge-other'
                                };
                            @endphp
                            <span class="badge-reason {{ $badgeClass }}">{{ $disposal->reason }}</span>
                        </td>

                        <td>
                            <span class="method-badge">{{ $disposal->disposal_method }}</span>
                        </td>

                        <td>
                            <span class="loss-amount">RM {{ number_format($disposal->estimated_loss, 2) }}</span>
                        </td>

                        <td>
                            <span class="disposed-by-info">{{ $disposal->disposedBy->user->name }}</span>
                        </td>

                        <td>
                            <div class="date-info">
                                <strong>{{ $disposal->disposed_at->format('M d, Y') }}</strong>
                                {{ $disposal->disposed_at->diffForHumans() }}
                            </div>
                        </td>

                        <td>
                            <a href="{{ route('pharmacist.disposals.show', $disposal->disposal_id) }}" class="btn-view">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                {{ $disposals->links() }}
            </div>
            @else
            <div class="empty-state">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M3 6h18"></path>
                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                </svg>
                <h3>No Disposal Records</h3>
                <p>You haven't recorded any medicine disposals yet.</p>
            </div>
            @endif
        </div>
    </div>
</body>
</html>