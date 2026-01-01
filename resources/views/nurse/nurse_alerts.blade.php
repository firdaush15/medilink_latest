<!--nurse_alerts.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alerts & Notifications - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e1e8ed;
        }

        .header-left h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .header-left p {
            color: #718096;
            font-size: 15px;
        }

        /* Alert Statistics */
        .alert-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .stat-card.critical {
            border-left-color: #f44336;
        }

        .stat-card.warning {
            border-left-color: #ff9800;
        }

        .stat-card.info {
            border-left-color: #2196f3;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .stat-label {
            color: #718096;
            font-size: 14px;
            font-weight: 600;
        }

        /* Filter Tabs */
        .filter-section {
            background: white;
            padding: 20px 28px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .filter-tabs {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 10px 20px;
            border-radius: 10px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 14px;
            text-decoration: none;
        }

        .filter-tab:hover {
            background: #e9ecef;
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .bulk-actions {
            margin-left: auto;
            display: flex;
            gap: 12px;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .action-btn.outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        /* Alerts List */
        .alerts-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .alert-item {
            display: flex;
            align-items: start;
            gap: 20px;
            padding: 24px 28px;
            border-bottom: 1px solid #e1e8ed;
            transition: all 0.3s;
            cursor: pointer;
        }

        .alert-item:last-child {
            border-bottom: none;
        }

        .alert-item:hover {
            background: #f8f9fe;
        }

        .alert-item.unread {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
        }

        .alert-item.critical {
            border-left: 4px solid #f44336;
            background: #fff5f5;
        }

        .alert-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .alert-icon.critical {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        }

        .alert-icon.warning {
            background: linear-gradient(135deg, #ffa726 0%, #fb8c00 100%);
        }

        .alert-icon.info {
            background: linear-gradient(135deg, #42a5f5 0%, #1e88e5 100%);
        }

        .alert-icon.success {
            background: linear-gradient(135deg, #66bb6a 0%, #43a047 100%);
        }

        .alert-content {
            flex: 1;
        }

        .alert-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .alert-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a202c;
            flex: 1;
        }

        .alert-time {
            font-size: 13px;
            color: #718096;
        }

        .alert-message {
            color: #4a5568;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 12px;
        }

        .alert-meta {
            display: flex;
            gap: 16px;
            align-items: center;
            flex-wrap: wrap;
        }

        .priority-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .priority-critical {
            background: #ffebee;
            color: #c62828;
        }

        .priority-urgent {
            background: #fff3e0;
            color: #f57c00;
        }

        .priority-normal {
            background: #e3f2fd;
            color: #1976d2;
        }

        .alert-actions {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 2px solid #e1e8ed;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .icon-btn.danger:hover {
            border-color: #f44336;
            background: #fff5f5;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
        }

        .empty-state svg {
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #718096;
            font-size: 14px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>🔔 Alerts & Notifications</h1>
                <p>Stay updated with critical patient information and urgent tasks</p>
            </div>
        </div>

        <!-- Alert Statistics -->
        <div class="alert-stats">
            <div class="stat-card critical">
                <div class="stat-value">{{ $counts['critical'] }}</div>
                <div class="stat-label">Critical Alerts</div>
            </div>
            <div class="stat-card warning">
                <div class="stat-value">{{ $counts['unread'] }}</div>
                <div class="stat-label">Unread Alerts</div>
            </div>
            <div class="stat-card info">
                <div class="stat-value">{{ $counts['today'] }}</div>
                <div class="stat-label">Today's Alerts</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $counts['total'] }}</div>
                <div class="stat-label">Total Alerts</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div class="filter-tabs">
                    <a href="{{ route('nurse.alerts', ['filter' => 'all']) }}" 
                       class="filter-tab {{ $filter == 'all' ? 'active' : '' }}">
                        All Alerts
                    </a>
                    <a href="{{ route('nurse.alerts', ['filter' => 'unread']) }}" 
                       class="filter-tab {{ $filter == 'unread' ? 'active' : '' }}">
                        Unread ({{ $counts['unread'] }})
                    </a>
                    <a href="{{ route('nurse.alerts', ['filter' => 'critical']) }}" 
                       class="filter-tab {{ $filter == 'critical' ? 'active' : '' }}">
                        Critical ({{ $counts['critical'] }})
                    </a>
                    <a href="{{ route('nurse.alerts', ['filter' => 'acknowledged']) }}" 
                       class="filter-tab {{ $filter == 'acknowledged' ? 'active' : '' }}">
                        Acknowledged
                    </a>
                </div>
                <div class="bulk-actions">
                    <form action="{{ route('nurse.alerts.mark-all-read') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="action-btn outline">
                            Mark All as Read
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alerts List -->
        <div class="alerts-container">
            @forelse($alerts as $alert)
            <div class="alert-item {{ !$alert->is_read ? 'unread' : '' }} {{ $alert->priority == 'Critical' ? 'critical' : '' }}">
                <div class="alert-icon {{ strtolower($alert->priority) }}">
                    @switch($alert->alert_type)
                        @case('Critical Vitals')
                            🚨
                            @break
                        @case('Medication Due')
                            💊
                            @break
                        @case('Task Assigned')
                            📋
                            @break
                        @case('Patient Request')
                            🔔
                            @break
                        @default
                            ℹ️
                    @endswitch
                </div>
                
                <div class="alert-content">
                    <div class="alert-header">
                        <div class="alert-title">{{ $alert->alert_title }}</div>
                        <div class="alert-time">{{ $alert->created_at->diffForHumans() }}</div>
                    </div>
                    
                    <div class="alert-message">
                        {{ $alert->alert_message }}
                    </div>
                    
                    <div class="alert-meta">
                        <span class="priority-badge priority-{{ strtolower($alert->priority) }}">
                            {{ $alert->priority }}
                        </span>
                        
                        @if($alert->patient)
                        <span style="color: #718096; font-size: 13px;">
                            👤 {{ $alert->patient->user->name }}
                        </span>
                        @endif
                        
                        @if($alert->alert_type)
                        <span style="color: #718096; font-size: 13px;">
                            🏷️ {{ $alert->alert_type }}
                        </span>
                        @endif

                        @if($alert->is_acknowledged)
                        <span style="color: #4caf50; font-size: 13px;">
                            ✅ Acknowledged
                        </span>
                        @endif
                    </div>
                </div>
                
                <div class="alert-actions">
                    @if(!$alert->is_read)
                    <form action="{{ route('nurse.alerts.mark-read', $alert->alert_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="icon-btn" title="Mark as Read">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                    
                    @if(!$alert->is_acknowledged && $alert->priority == 'Critical')
                    <form action="{{ route('nurse.alerts.acknowledge', $alert->alert_id) }}" method="POST">
                        @csrf
                        <button type="submit" class="icon-btn" title="Acknowledge">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                                <polyline points="22 4 12 14.01 9 11.01"/>
                            </svg>
                        </button>
                    </form>
                    @endif
                    
                    @if($alert->action_url)
                    <button onclick="window.location='{{ $alert->action_url }}'" class="icon-btn" title="View Details">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                            <polyline points="15 3 21 3 21 9"/>
                            <line x1="10" y1="14" x2="21" y2="3"/>
                        </svg>
                    </button>
                    @endif
                </div>
            </div>
            @empty
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <h3>No Alerts Found</h3>
                <p>You're all caught up! No alerts matching the selected filter.</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($alerts->hasPages())
        <div style="margin-top: 24px;">
            {{ $alerts->links() }}
        </div>
        @endif
    </div>

    <script>
        // Auto-refresh alerts every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>