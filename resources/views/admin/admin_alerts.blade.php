{{-- resources/views/admin/admin_alerts.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink – Alerts & Notifications</title>
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_alerts.css'])
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">

    {{-- Flash --}}
    @if(session('success'))
        <div class="flash flash-success">✓ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="flash flash-error">✕ {{ session('error') }}</div>
    @endif

    {{-- ══ PAGE HEADER ══ --}}
    <div class="alerts-header">
        <div>
            <h1>🔔 Alerts &amp; Notifications</h1>
            <p>All alerts sent to you from doctors, nurses, pharmacists and receptionists</p>
        </div>
        @if($counts['unread'] > 0)
            <form action="{{ route('admin.alerts.mark-all-read') }}" method="POST">
                @csrf
                <button type="submit" class="btn-mark-all">✓ Mark All as Read</button>
            </form>
        @endif
    </div>

    {{-- ══ KPI STRIP ══ --}}
    <div class="kpi-strip">
        <div class="kpi-chip kpi-blue">
            <span class="kpi-val">{{ $counts['total'] }}</span>
            <span class="kpi-lbl">Total</span>
        </div>
        <div class="kpi-chip kpi-orange">
            <span class="kpi-val">{{ $counts['unread'] }}</span>
            <span class="kpi-lbl">Unread</span>
        </div>
        <div class="kpi-chip kpi-red">
            <span class="kpi-val">{{ $counts['critical'] }}</span>
            <span class="kpi-lbl">Critical</span>
        </div>
        <div class="kpi-chip kpi-yellow">
            <span class="kpi-val">{{ $counts['urgent'] }}</span>
            <span class="kpi-lbl">Urgent</span>
        </div>
        <div class="kpi-chip kpi-purple">
            <span class="kpi-val">{{ $counts['pending'] }}</span>
            <span class="kpi-lbl">Pending Action</span>
        </div>
        <div class="kpi-chip kpi-green">
            <span class="kpi-val">{{ $counts['today'] }}</span>
            <span class="kpi-lbl">Today</span>
        </div>
    </div>

    {{-- ══ FILTER + SEARCH ══ --}}
    <div class="filter-bar">
        <div class="filter-tabs">
            @foreach([
                'all'          => 'All',
                'unread'       => 'Unread ('.$counts['unread'].')',
                'critical'     => 'Critical ('.$counts['critical'].')',
                'urgent'       => 'Urgent ('.$counts['urgent'].')',
                'pending'      => 'Pending ('.$counts['pending'].')',
                'acknowledged' => 'Acknowledged ('.$counts['acknowledged'].')',
                'today'        => 'Today ('.$counts['today'].')',
            ] as $key => $label)
                <a href="{{ route('admin.alerts.index', ['filter' => $key, 'search' => $search]) }}"
                   class="filter-tab {{ $filter === $key ? 'active' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.alerts.index') }}" class="search-form">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Search alerts…" class="search-input">
            <button type="submit" class="search-btn">🔍</button>
        </form>
    </div>

    {{-- ══ ALERTS LIST ══ --}}
    <div class="alerts-list">
        @forelse($alerts as $alert)
            <div class="alert-card
                        priority-{{ strtolower($alert->priority) }}
                        {{ $alert->is_read ? '' : 'unread' }}
                        {{ $alert->is_acknowledged ? 'acked' : '' }}">

                {{-- Left colour bar --}}
                <div class="alert-bar priority-bar-{{ strtolower($alert->priority) }}"></div>

                {{-- Icon --}}
                <div class="alert-icon-wrap priority-icon-{{ strtolower($alert->priority) }}">
                    @switch($alert->priority)
                        @case('Critical') 🚨 @break
                        @case('Urgent')   ⚡ @break
                        @case('High')     ⚠️ @break
                        @default          ℹ️
                    @endswitch
                </div>

                {{-- Content --}}
                <div class="alert-body">
                    <div class="alert-top">
                        <span class="alert-title">{{ $alert->alert_title }}</span>
                        <span class="alert-time">{{ $alert->created_at->diffForHumans() }}</span>
                    </div>

                    <p class="alert-message">{{ $alert->alert_message }}</p>

                    <div class="alert-meta">
                        <span class="priority-pill priority-pill-{{ strtolower($alert->priority) }}">
                            {{ $alert->priority }}
                        </span>

                        <span class="meta-chip">
                            👤 {{ $alert->sender->name ?? 'System' }}
                            ({{ ucfirst($alert->sender_type) }})
                        </span>

                        <span class="meta-chip">🏷️ {{ $alert->alert_type }}</span>

                        @if($alert->patient)
                            <span class="meta-chip">
                                🧑‍🦱 {{ $alert->patient->user->name ?? '—' }}
                            </span>
                        @endif

                        @if($alert->is_acknowledged)
                            <span class="status-pill pill-acked">✅ Acknowledged</span>
                        @elseif($alert->is_read)
                            <span class="status-pill pill-read">✓ Read</span>
                        @else
                            <span class="status-pill pill-new">● New</span>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="alert-actions">
                    @if($alert->action_url)
                        <a href="{{ $alert->action_url }}" class="act-btn act-view" title="View">
                            🔗
                        </a>
                    @endif

                    @if(!$alert->is_read)
                        <form action="{{ route('admin.alerts.mark-read', $alert->alert_id) }}"
                              method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="act-btn act-read" title="Mark read">✓</button>
                        </form>
                    @endif

                    @if(!$alert->is_acknowledged && in_array($alert->priority, ['Critical', 'Urgent']))
                        <form action="{{ route('admin.alerts.acknowledge', $alert->alert_id) }}"
                              method="POST" class="inline-form">
                            @csrf
                            <button type="submit" class="act-btn act-ack" title="Acknowledge">✅</button>
                        </form>
                    @endif

                    <form action="{{ route('admin.alerts.destroy', $alert->alert_id) }}"
                          method="POST" class="inline-form"
                          onsubmit="return confirm('Delete this alert?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="act-btn act-delete" title="Delete">🗑️</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <div class="empty-icon">🔕</div>
                <h3>No alerts found</h3>
                <p>You're all caught up — nothing matching this filter.</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($alerts->hasPages())
        <div class="pagination">
            <p>
                Showing {{ $alerts->firstItem() }}–{{ $alerts->lastItem() }}
                of {{ $alerts->total() }}
            </p>
            <div class="pages">
                @if($alerts->onFirstPage())
                    <button disabled>&laquo; Prev</button>
                @else
                    <a href="{{ $alerts->previousPageUrl() }}"><button>&laquo; Prev</button></a>
                @endif

                @foreach($alerts->getUrlRange(1, $alerts->lastPage()) as $page => $url)
                    @if($page == $alerts->currentPage())
                        <button class="active">{{ $page }}</button>
                    @else
                        <a href="{{ $url }}"><button>{{ $page }}</button></a>
                    @endif
                @endforeach

                @if($alerts->hasMorePages())
                    <a href="{{ $alerts->nextPageUrl() }}"><button>Next &raquo;</button></a>
                @else
                    <button disabled>Next &raquo;</button>
                @endif
            </div>
        </div>
    @endif

</div>{{-- /.main --}}

@vite(['resources/js/sidebar.js'])
</body>
</html>