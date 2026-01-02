<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alerts & Notifications - Receptionist</title>
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
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
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
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-card .value {
            color: #2d3748;
            font-size: 2rem;
            font-weight: 700;
        }

        .stat-card.unread {
            border-left: 4px solid #f59e0b;
        }

        .stat-card.urgent {
            border-left: 4px solid #ef4444;
        }

        .stat-card.cancellations {
            border-left: 4px solid #8b5cf6;
        }

        .stat-card.all {
            border-left: 4px solid #3b82f6;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: white;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 0.75rem 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            color: #4a5568;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tab:hover {
            border-color: #667eea;
            color: #667eea;
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
        }

        .filter-tab .badge {
            background: rgba(0, 0, 0, 0.1);
            color: inherit;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        /* Actions Bar */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

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

        /* Alerts List */
        .alerts-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .alert-item {
            padding: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
            transition: background 0.2s;
            position: relative;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .alert-item:hover {
            background: #f7fafc;
        }

        .alert-item.unread {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
        }

        .alert-item.unread::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: #f59e0b;
        }

        .alert-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 0.75rem;
        }

        .alert-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .alert-title h3 {
            color: #2d3748;
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .priority-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
        }

        .priority-Critical {
            background: #fee2e2;
            color: #991b1b;
        }

        .priority-Urgent {
            background: #fef3c7;
            color: #92400e;
        }

        .priority-High {
            background: #dbeafe;
            color: #1e40af;
        }

        .priority-Normal {
            background: #e5e7eb;
            color: #374151;
        }

        .alert-time {
            font-size: 0.85rem;
            color: #9ca3af;
            white-space: nowrap;
        }

        .alert-message {
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .alert-meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.85rem;
            color: #718096;
            margin-bottom: 1rem;
        }

        .alert-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .alert-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
        }

        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .btn-view:hover {
            background: #2563eb;
        }

        .btn-mark-read {
            background: #10b981;
            color: white;
        }

        .btn-mark-read:hover {
            background: #059669;
        }

        .btn-delete {
            background: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background: #dc2626;
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

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            color: #4a5568;
            text-decoration: none;
            transition: all 0.2s;
        }

        .pagination a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pagination .active span {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: transparent;
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

            .filter-tabs {
                flex-direction: column;
            }

            .alert-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .alert-actions {
                flex-direction: column;
            }

            .alert-actions .btn {
                width: 100%;
                justify-content: center;
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
                <h1>
                    🔔 Alerts & Notifications
                </h1>
                <p>Stay updated with appointment cancellations, patient updates, and system notifications</p>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card unread">
                    <h3>Unread Alerts</h3>
                    <div class="value">{{ $counts['unread'] }}</div>
                </div>
                <div class="stat-card urgent">
                    <h3>Urgent</h3>
                    <div class="value">{{ $counts['urgent'] }}</div>
                </div>
                <div class="stat-card cancellations">
                    <h3>Cancellations</h3>
                    <div class="value">{{ $counts['cancellations'] }}</div>
                </div>
                <div class="stat-card all">
                    <h3>Total Alerts</h3>
                    <div class="value">{{ $counts['all'] }}</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <a href="{{ route('receptionist.alerts.index', ['filter' => 'all']) }}"
                    class="filter-tab {{ $filter === 'all' ? 'active' : '' }}">
                    📋 All Alerts
                    <span class="badge">{{ $counts['all'] }}</span>
                </a>
                <a href="{{ route('receptionist.alerts.index', ['filter' => 'unread']) }}"
                    class="filter-tab {{ $filter === 'unread' ? 'active' : '' }}">
                    🔴 Unread
                    <span class="badge">{{ $counts['unread'] }}</span>
                </a>
                <a href="{{ route('receptionist.alerts.index', ['filter' => 'urgent']) }}"
                    class="filter-tab {{ $filter === 'urgent' ? 'active' : '' }}">
                    ⚠️ Urgent
                    <span class="badge">{{ $counts['urgent'] }}</span>
                </a>
                <a href="{{ route('receptionist.alerts.index', ['filter' => 'cancellations']) }}"
                    class="filter-tab {{ $filter === 'cancellations' ? 'active' : '' }}">
                    ❌ Cancellations
                    <span class="badge">{{ $counts['cancellations'] }}</span>
                </a>
            </div>

            <!-- Actions Bar -->
            <div class="actions-bar">
                <div>
                    <h3 style="color: #2d3748; font-size: 1.25rem;">
                        @if($filter === 'all') All Alerts
                        @elseif($filter === 'unread') Unread Alerts
                        @elseif($filter === 'urgent') Urgent Alerts
                        @elseif($filter === 'cancellations') Appointment Cancellations
                        @endif
                    </h3>
                </div>
                @if($counts['unread'] > 0)
                <button onclick="markAllRead()" class="btn btn-primary">
                    ✓ Mark All as Read
                </button>
                @endif
            </div>

            <!-- Alerts List -->
            <div class="alerts-container">
                @forelse($alerts as $alert)
                <div class="alert-item {{ !$alert->is_read ? 'unread' : '' }}" id="alert-{{ $alert->alert_id }}">
                    <div class="alert-header">
                        <div class="alert-title">
                            <h3>{{ $alert->alert_title }}</h3>
                            <span class="priority-badge priority-{{ $alert->priority }}">
                                {{ $alert->priority }}
                            </span>
                        </div>
                        <span class="alert-time">{{ $alert->created_at->diffForHumans() }}</span>
                    </div>

                    <p class="alert-message">{{ $alert->alert_message }}</p>

                    <div class="alert-meta">
                        <span>
                            📤 From: <strong>{{ $alert->sender->name }}</strong>
                        </span>
                        <span>
                            📁 Type: <strong>{{ $alert->alert_type }}</strong>
                        </span>
                        @if($alert->patient)
                        <span>
                            👤 Patient: <strong>{{ $alert->patient->user->name }}</strong>
                        </span>
                        @endif
                        {{-- ✅ ADDED: Show doctor info through appointment --}}
                        @if($alert->appointment && $alert->appointment->doctor)
                        <span>
                            👨‍⚕️ Doctor: <strong>{{ $alert->appointment->doctor->user->name }}</strong>
                        </span>
                        @endif
                    </div>

                    <div class="alert-actions">
                        @if($alert->action_url)
                        <a href="{{ $alert->action_url }}" class="btn btn-view">
                            👁️ View Details
                        </a>
                        @endif

                        @if(!$alert->is_read)
                        <button onclick="markAsRead({{ $alert->alert_id }})" class="btn btn-mark-read">
                            ✓ Mark as Read
                        </button>
                        @endif

                        <button onclick="deleteAlert({{ $alert->alert_id }})" class="btn btn-delete">
                            🗑️ Delete
                        </button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <h3>No alerts found</h3>
                    <p>You're all caught up! No alerts match this filter.</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if($alerts->hasPages())
            <div class="pagination">
                {{ $alerts->links() }}
            </div>
            @endif
        </div>
    </div>

    <script>
        // Mark single alert as read
        function markAsRead(alertId) {
            fetch(`/receptionist/alerts/${alertId}/mark-read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const alertItem = document.getElementById(`alert-${alertId}`);
                        alertItem.classList.remove('unread');
                        alertItem.querySelector('.btn-mark-read')?.remove();

                        // Update unread count
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Mark all alerts as read
        function markAllRead() {
            if (!confirm('Mark all alerts as read?')) return;

            fetch('/receptionist/alerts/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Delete alert
        function deleteAlert(alertId) {
            if (!confirm('Delete this alert? This action cannot be undone.')) return;

            fetch(`/receptionist/alerts/${alertId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById(`alert-${alertId}`).remove();
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Auto-refresh every 2 minutes
        setInterval(() => {
            fetch('/receptionist/alerts/unread-count')
                .then(response => response.json())
                .then(data => {
                    // Update navbar badge if exists
                    const badge = document.querySelector('.notification-badge');
                    if (badge && data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'block';
                    }
                });
        }, 120000);
    </script>
</body>

</html>