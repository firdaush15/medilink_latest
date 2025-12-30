<!-- resources/views/pharmacist/pharmacist_alerts.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alerts & Notifications - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_alerts.css'])
</head>
<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <h1>🔔 Alerts & Notifications</h1>
            <div class="user-info">
                <span>{{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Alert Summary -->
        <div class="alert-summary">
            <div class="summary-card critical">
                <div class="summary-icon">🚨</div>
                <div class="summary-info">
                    <h3>{{ $alerts->where('priority', 'Critical')->where('is_read', false)->count() }}</h3>
                    <p>Critical</p>
                </div>
            </div>

            <div class="summary-card high">
                <div class="summary-icon">⚡</div>
                <div class="summary-info">
                    <h3>{{ $alerts->where('priority', 'Urgent')->where('is_read', false)->count() }}</h3>
                    <p>Urgent</p>
                </div>
            </div>

            <div class="summary-card medium">
                <div class="summary-icon">⚠️</div>
                <div class="summary-info">
                    <h3>{{ $alerts->where('priority', 'High')->where('is_read', false)->count() }}</h3>
                    <p>High Priority</p>
                </div>
            </div>

            <div class="summary-card resolved">
                <div class="summary-icon">✅</div>
                <div class="summary-info">
                    <h3>{{ $alerts->where('is_acknowledged', true)->count() }}</h3>
                    <p>Resolved Today</p>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <button class="tab-btn active" data-filter="all">
                All <span class="badge">{{ $alerts->total() }}</span>
            </button>
            <button class="tab-btn" data-filter="unread">
                Unread <span class="badge">{{ $alerts->where('is_read', false)->count() }}</span>
            </button>
            <button class="tab-btn" data-filter="critical">
                Critical <span class="badge">{{ $alerts->where('priority', 'Critical')->count() }}</span>
            </button>
            <button class="tab-btn" data-filter="urgent">
                Urgent <span class="badge">{{ $alerts->where('priority', 'Urgent')->count() }}</span>
            </button>
            <button class="tab-btn" data-filter="resolved">
                Resolved <span class="badge">{{ $alerts->where('is_acknowledged', true)->count() }}</span>
            </button>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <button onclick="markAllAsRead()" class="btn btn-primary">
                ✓ Mark All Read
            </button>
            <button onclick="filterByType('Low Stock')" class="btn btn-secondary">
                📦 Low Stock
            </button>
            <button onclick="filterByType('Expiring Soon')" class="btn btn-secondary">
                ⏰ Expiring Soon
            </button>
            <button onclick="filterByType('Expired Medicine')" class="btn btn-secondary">
                🚫 Expired
            </button>
            <button onclick="filterByType('Pending Verification')" class="btn btn-secondary">
                ✋ Pending
            </button>
        </div>

        <!-- Alerts List -->
        <div class="alerts-container">
            @forelse($alerts as $alert)
            <div class="alert-card {{ strtolower($alert->priority) }} {{ $alert->is_read ? 'read' : 'unread' }} {{ $alert->is_acknowledged ? 'resolved' : '' }}" 
                 data-alert-id="{{ $alert->alert_id }}"
                 data-priority="{{ strtolower($alert->priority) }}"
                 data-type="{{ $alert->alert_type }}"
                 data-status="{{ $alert->is_acknowledged ? 'resolved' : ($alert->is_read ? 'read' : 'unread') }}">
                
                <div class="alert-header">
                    <div class="alert-left">
                        <span class="alert-icon">
                            @switch($alert->alert_type)
                                @case('Low Stock')
                                    📦
                                    @break
                                @case('Expiring Soon')
                                    ⏰
                                    @break
                                @case('Expired Medicine')
                                    🚫
                                    @break
                                @case('Drug Interaction')
                                    ⚠️
                                    @break
                                @case('Allergy Warning')
                                    🚨
                                    @break
                                @case('Pending Verification')
                                    ✋
                                    @break
                                @case('Restock Needed')
                                    📋
                                    @break
                                @default
                                    🔔
                            @endswitch
                        </span>
                        <div class="alert-title-section">
                            <h3 class="alert-title">{{ $alert->alert_title }}</h3>
                            <span class="alert-type">{{ $alert->alert_type }}</span>
                        </div>
                    </div>
                    <div class="alert-right">
                        <span class="priority-badge {{ strtolower($alert->priority) }}">
                            {{ $alert->priority }}
                        </span>
                        @if(!$alert->is_read)
                        <span class="unread-indicator"></span>
                        @endif
                    </div>
                </div>

                <div class="alert-body">
                    <p class="alert-message">{{ $alert->alert_message }}</p>
                    
                    @if($alert->medicine)
                    <div class="alert-details">
                        <strong>Medicine:</strong> {{ $alert->medicine->medicine_name }}
                        @if($alert->alert_type == 'Low Stock')
                        <br><strong>Current Stock:</strong> {{ $alert->medicine->quantity_in_stock }} units
                        <br><strong>Reorder Level:</strong> {{ $alert->medicine->reorder_level }} units
                        @endif
                        @if($alert->alert_type == 'Expiring Soon' || $alert->alert_type == 'Expired Medicine')
                        <br><strong>Expiry Date:</strong> {{ $alert->medicine->expiry_date->format('M d, Y') }}
                        <br><strong>Days Until Expiry:</strong> {{ $alert->medicine->getDaysUntilExpiry() }} days
                        @endif
                    </div>
                    @endif

                    <div class="alert-meta">
                        <span class="alert-time">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            {{ $alert->created_at->diffForHumans() }}
                        </span>
                        @if($alert->is_acknowledged)
                        <span class="resolved-badge">
                            ✓ Resolved {{ $alert->acknowledged_at->diffForHumans() }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="alert-footer">
                    <div class="alert-actions">
                        @if($alert->action_url)
                        <a href="{{ $alert->action_url }}" class="btn-action btn-primary">
                            View Details
                        </a>
                        @endif

                        @if(!$alert->is_read && !$alert->is_acknowledged)
                        <button onclick="markAsRead({{ $alert->alert_id }})" class="btn-action btn-secondary">
                            Mark Read
                        </button>
                        @endif

                        @if(!$alert->is_acknowledged)
                        <button onclick="resolveAlert({{ $alert->alert_id }})" class="btn-action btn-success">
                            ✓ Resolve
                        </button>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <h3>No Alerts</h3>
                <p>You're all caught up! No alerts at the moment.</p>
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

    <script>
        // ========================================
        // FILTER FUNCTIONALITY
        // ========================================
        const tabBtns = document.querySelectorAll('.tab-btn');
        const alertCards = document.querySelectorAll('.alert-card');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                tabBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const filter = this.dataset.filter;
                alertCards.forEach(card => {
                    const status = card.dataset.status;
                    const priority = card.dataset.priority;

                    if (filter === 'all') {
                        card.style.display = 'block';
                    } else if (filter === 'unread' && status === 'unread') {
                        card.style.display = 'block';
                    } else if (filter === 'critical' && priority === 'critical') {
                        card.style.display = 'block';
                    } else if (filter === 'urgent' && priority === 'urgent') {
                        card.style.display = 'block';
                    } else if (filter === 'resolved' && status === 'resolved') {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // ========================================
        // FILTER BY TYPE
        // ========================================
        function filterByType(type) {
            alertCards.forEach(card => {
                const cardType = card.dataset.type;
                card.style.display = cardType === type ? 'block' : 'none';
            });

            tabBtns.forEach(b => b.classList.remove('active'));
        }

        // ========================================
        // MARK AS READ
        // ========================================
        function markAsRead(alertId) {
            fetch(`/pharmacist/alerts/${alertId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const card = document.querySelector(`[data-alert-id="${alertId}"]`);
                    card.classList.remove('unread');
                    card.classList.add('read');
                    card.dataset.status = 'read';
                    
                    const unreadIndicator = card.querySelector('.unread-indicator');
                    if (unreadIndicator) unreadIndicator.remove();
                    
                    const markReadBtn = card.querySelector('.btn-secondary');
                    if (markReadBtn) markReadBtn.remove();

                    updateBadgeCounts();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // ========================================
        // MARK ALL AS READ
        // ========================================
        function markAllAsRead() {
            if (!confirm('Mark all alerts as read?')) return;

            const unreadCards = document.querySelectorAll('.alert-card.unread');
            const alertIds = Array.from(unreadCards).map(card => card.dataset.alertId);

            alertIds.forEach(id => markAsRead(id));
        }

        // ========================================
        // RESOLVE ALERT
        // ========================================
        function resolveAlert(alertId) {
            if (!confirm('Mark this alert as resolved?')) return;

            fetch(`/pharmacist/alerts/${alertId}/resolve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
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

        // ========================================
        // UPDATE BADGE COUNTS
        // ========================================
        function updateBadgeCounts() {
            const unreadCount = document.querySelectorAll('.alert-card.unread').length;
            const unreadBadge = document.querySelector('[data-filter="unread"] .badge');
            if (unreadBadge) {
                unreadBadge.textContent = unreadCount;
            }
        }
    </script>
</body>
</html>