{{-- resources/views/doctor/doctor_alertInbox.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink | Alerts & Notifications</title>
    @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/doctor_alerts.css'])
</head>
<body>

@include('doctor.sidebar.doctor_sidebar')

<div class="main">
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="alert alert-success">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        {{ session('success') }}
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>🔔 Alerts & Notifications</h1>
            <p>Stay updated with important messages from your team</p>
        </div>
        <a href="{{ route('doctor.alerts.outbox') }}" class="btn-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            Send Alert
        </a>
    </div>

    <!-- Alert Statistics -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">📬</div>
            <div class="stat-content">
                <div class="stat-value">{{ $counts['total'] }}</div>
                <div class="stat-label">Total Alerts</div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon">✉️</div>
            <div class="stat-content">
                <div class="stat-value">{{ $counts['unread'] }}</div>
                <div class="stat-label">Unread</div>
            </div>
        </div>

        <div class="stat-card red">
            <div class="stat-icon">🚨</div>
            <div class="stat-content">
                <div class="stat-value">{{ $counts['critical'] }}</div>
                <div class="stat-label">Critical</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-value">{{ $counts['today'] }}</div>
                <div class="stat-label">Today</div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-tabs">
            <a href="{{ route('doctor.alerts.inbox', ['filter' => 'all']) }}" 
               class="filter-tab {{ $filter == 'all' ? 'active' : '' }}">
                All
            </a>
            <a href="{{ route('doctor.alerts.inbox', ['filter' => 'unread']) }}" 
               class="filter-tab {{ $filter == 'unread' ? 'active' : '' }}">
                Unread ({{ $counts['unread'] }})
            </a>
            <a href="{{ route('doctor.alerts.inbox', ['filter' => 'critical']) }}" 
               class="filter-tab {{ $filter == 'critical' ? 'active' : '' }}">
                Critical ({{ $counts['critical'] }})
            </a>
            <a href="{{ route('doctor.alerts.inbox', ['filter' => 'pending']) }}" 
               class="filter-tab {{ $filter == 'pending' ? 'active' : '' }}">
                Pending
            </a>
            <a href="{{ route('doctor.alerts.inbox', ['filter' => 'today']) }}" 
               class="filter-tab {{ $filter == 'today' ? 'active' : '' }}">
                Today
            </a>
        </div>

        <div class="filter-actions">
            <form method="GET" class="search-form">
                <input type="hidden" name="filter" value="{{ $filter }}">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search alerts..." class="search-input">
                <button type="submit" class="search-btn">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </form>

            <form action="{{ route('doctor.alerts.mark-all-read') }}" method="POST" style="display: inline;">
                @csrf
                <button type="submit" class="btn-secondary">
                    Mark All as Read
                </button>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="alerts-container">
        @forelse($alerts as $alert)
        <div class="alert-item {{ !$alert->is_read ? 'unread' : '' }} priority-{{ strtolower($alert->priority) }}">
            <div class="alert-priority-indicator priority-{{ strtolower($alert->priority) }}"></div>
            
            <div class="alert-icon {{ strtolower($alert->priority) }}">
                @switch($alert->priority)
                    @case('Critical')
                        🚨
                        @break
                    @case('Urgent')
                        ⚡
                        @break
                    @case('High')
                        ⚠️
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
                
                <div class="alert-message">{{ $alert->alert_message }}</div>
                
                <div class="alert-meta">
                    <span class="priority-badge priority-{{ strtolower($alert->priority) }}">
                        {{ $alert->priority }}
                    </span>
                    
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        From: {{ $alert->sender->name }} ({{ ucfirst($alert->sender_type) }})
                    </span>
                    
                    @if($alert->patient)
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                        </svg>
                        Patient: {{ $alert->patient->user->name }}
                    </span>
                    @endif
                    
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Type: {{ $alert->alert_type }}
                    </span>

                    @if($alert->is_acknowledged)
                    <span class="status-badge acknowledged">
                        ✓ Acknowledged
                    </span>
                    @endif
                </div>
            </div>
            
            <div class="alert-actions">
                @if(!$alert->is_read)
                <form action="{{ route('doctor.alerts.mark-read', $alert->alert_id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="icon-btn" title="Mark as Read">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </button>
                </form>
                @endif
                
                @if(!$alert->is_acknowledged && in_array($alert->priority, ['Critical', 'Urgent']))
                <form action="{{ route('doctor.alerts.acknowledge', $alert->alert_id) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="icon-btn" title="Acknowledge">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                            <polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                    </button>
                </form>
                @endif
                
                @if($alert->action_url)
                <button onclick="window.location='{{ $alert->action_url }}'" class="icon-btn" title="View Details">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
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
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
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
    <div class="pagination-container">
        {{ $alerts->appends(request()->query())->links() }}
    </div>
    @endif
</div>

</body>
</html>