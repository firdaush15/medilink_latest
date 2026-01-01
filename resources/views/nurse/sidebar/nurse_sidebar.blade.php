<div class="sidebar">
    <h2>NURSE DASHBOARD</h2>
    <div class="logo">
        <img src="{{ asset('assets/logo.png') }}" alt="MediLink Logo">
    </div>
    
    <a href="{{ route('nurse.dashboard') }}" class="{{ request()->routeIs('nurse.dashboard') ? 'active' : '' }}">
        Dashboard
    </a>
    
    <!-- My Work Dropdown -->
    <div class="dropdown-menu">
        @php
            $nurse = auth()->user()->nurse;
            
            // ✅ Get pending tasks (from doctors)
            $pendingTasks = \App\Models\StaffTask::where('assigned_to_id', auth()->id())
                ->where('assigned_to_type', 'nurse')
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            
            // ✅ Get unread alerts
            $unreadAlerts = \App\Models\StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->where('is_read', false)
                ->count();
            
            // ✅ Get critical/urgent items
            $urgentItems = \App\Models\StaffTask::where('assigned_to_id', auth()->id())
                ->where('assigned_to_type', 'nurse')
                ->whereIn('priority', ['Critical', 'Urgent'])
                ->whereIn('status', ['pending', 'in_progress'])
                ->count();
            
            $urgentItems += \App\Models\StaffAlert::where('recipient_id', auth()->id())
                ->where('recipient_type', 'nurse')
                ->where('priority', 'Critical')
                ->where('is_acknowledged', false)
                ->count();
            
            $totalWork = $pendingTasks + $unreadAlerts;
        @endphp
        
        <a href="{{ route('nurse.work-dashboard') }}" 
           class="dropdown-toggle {{ request()->routeIs('nurse.work-dashboard') || request()->routeIs('nurse.tasks*') || request()->routeIs('nurse.alerts*') ? 'active' : '' }}"
           onclick="toggleDropdown(event)">
            <span style="display: flex; align-items: center; gap: 8px;">
                🎯 My Work
                @if($urgentItems > 0)
                    <span class="urgent-indicator" title="{{ $urgentItems }} urgent items">🔥</span>
                @endif
            </span>
            @if($totalWork > 0)
                <span class="notification-badge" id="totalWorkBadge">{{ $totalWork }}</span>
            @endif
            <span class="dropdown-arrow">▼</span>
        </a>
        
        <div class="dropdown-content {{ request()->routeIs('nurse.work-dashboard') || request()->routeIs('nurse.tasks*') || request()->routeIs('nurse.alerts*') ? 'show' : '' }}">
            <a href="{{ route('nurse.work-dashboard') }}" class="{{ request()->routeIs('nurse.work-dashboard') ? 'active' : '' }}">
                <span style="display: flex; align-items: center; gap: 6px;">
                    📋 Active Work
                    @if($urgentItems > 0)
                        <span style="color: #ff4444; font-size: 11px; font-weight: bold;">({{ $urgentItems }} urgent)</span>
                    @endif
                </span>
                @if($totalWork > 0)
                    <span class="notification-badge-small" id="activeWorkBadge">{{ $totalWork }}</span>
                @endif
            </a>
            
            <a href="{{ route('nurse.tasks') }}" class="{{ request()->routeIs('nurse.tasks*') ? 'active' : '' }}">
                <span style="display: flex; align-items: center; gap: 6px;">
                    ✓ Tasks
                    <span style="font-size: 10px; color: #a0aec0;">(from doctors)</span>
                </span>
                @if($pendingTasks > 0)
                    <span class="notification-badge-small task-badge" id="tasksBadge">{{ $pendingTasks }}</span>
                @endif
            </a>
            
            <a href="{{ route('nurse.alerts') }}" class="{{ request()->routeIs('nurse.alerts*') ? 'active' : '' }}">
                <span>🔔 Alerts</span>
                @if($unreadAlerts > 0)
                    <span class="notification-badge-small alert-badge" id="alertsBadge">{{ $unreadAlerts }}</span>
                @endif
            </a>
        </div>
    </div>
    
    <!-- My Patient Queue -->
    <a href="{{ route('nurse.queue-management') }}" class="{{ request()->routeIs('nurse.queue-management*') ? 'active' : '' }}">
        📋 My Patient Queue
        @php
            $myAssignedWaiting = \App\Models\Appointment::whereDate('appointment_date', today())
                ->where('assigned_nurse_id', $nurse->nurse_id ?? null)
                ->whereIn('status', ['checked_in', 'vitals_pending'])
                ->count();
        @endphp
        @if($myAssignedWaiting > 0)
            <span class="notification-badge" style="background: #ff9800;">{{ $myAssignedWaiting }}</span>
        @endif
    </a>
    
    <a href="{{ route('nurse.appointments') }}" class="{{ request()->routeIs('nurse.appointments*') ? 'active' : '' }}">
        Appointments & Check-In
    </a>
    
    <a href="{{ route('nurse.patients') }}" class="{{ request()->routeIs('nurse.patients*') ? 'active' : '' }}">
        Patient Vitals & Records
    </a>
    
    <a href="{{ route('nurse.vitals-analytics') }}" class="{{ request()->routeIs('nurse.vitals-analytics*') ? 'active' : '' }}">
        Vitals Analytics
    </a>

    {{-- Team & Schedule --}}
    <a href="{{ route('nurse.team-schedule') }}" class="{{ request()->routeIs('nurse.team-schedule') ? 'active' : '' }}">
        <span style="display: flex; align-items: center; justify-content: space-between;">
            <span>👥 My Team & Schedule</span>
            @php
                $nursePendingLeaves = \App\Models\LeaveRequest::where('user_id', auth()->id())
                    ->where('status', 'pending')
                    ->count();
            @endphp
            @if($nursePendingLeaves > 0)
                <span class="notification-badge" style="background: #ff9800;">{{ $nursePendingLeaves }}</span>
            @endif
        </span>
    </a>
    
    <a href="{{ route('nurse.reports-documentation') }}" class="{{ request()->routeIs('nurse.reports-documentation*') ? 'active' : '' }}">
        Reports & Documentation
    </a>
    
    <!-- Messages -->
    @if(Route::has('nurse.messages'))
    <a href="{{ route('nurse.messages') }}" class="{{ request()->routeIs('nurse.messages') ? 'active' : '' }}">
        Messages
    </a>
    @else
    <a href="#" class="disabled" title="Coming Soon">
        Messages
    </a>
    @endif
    
    <!-- Settings -->
    @if(Route::has('nurse.settings'))
    <a href="{{ route('nurse.settings') }}" class="{{ request()->routeIs('nurse.settings') ? 'active' : '' }}">
        Settings
    </a>
    @else
    <a href="#" class="disabled" title="Coming Soon">
        Settings
    </a>
    @endif
    
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-btn">Logout</button>
    </form>
</div>

<style>
.logout-btn {
    width: 100%;
    text-align: left;
    background: none;
    border: none;
    color: white;
    padding: 12px 25px;
    font-size: 15px;
    cursor: pointer;
    font-family: "Poppins", sans-serif;
    transition: all 0.2s ease;
}

.logout-btn:hover {
    background-color: #1b3b5f;
    border-left: 4px solid #00aaff;
}

.notification-badge {
    background-color: #ff4444;
    color: white;
    border-radius: 50%;
    padding: 2px 7px;
    font-size: 11px;
    font-weight: bold;
    margin-left: 8px;
    display: inline-block;
    min-width: 20px;
    text-align: center;
    animation: pulse-badge 2s infinite;
}

.notification-badge-small {
    background-color: #ff4444;
    color: white;
    border-radius: 10px;
    padding: 2px 6px;
    font-size: 10px;
    font-weight: bold;
    margin-left: auto;
    display: inline-block;
    min-width: 18px;
    text-align: center;
}

.notification-badge-small.task-badge {
    background-color: #667eea;
    box-shadow: 0 0 8px rgba(102, 126, 234, 0.4);
}

.notification-badge-small.alert-badge {
    background-color: #ff9800;
    box-shadow: 0 0 8px rgba(255, 152, 0, 0.4);
}

.urgent-indicator {
    animation: pulse-fire 1.5s infinite;
    display: inline-block;
    font-size: 14px;
}

@keyframes pulse-badge {
    0%, 100% {
        opacity: 1;
        transform: scale(1);
    }
    50% {
        opacity: 0.8;
        transform: scale(1.05);
    }
}

@keyframes pulse-fire {
    0%, 100% {
        transform: scale(1);
        opacity: 1;
    }
    50% {
        transform: scale(1.2);
        opacity: 0.8;
    }
}

.sidebar a.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    position: relative;
}

.sidebar a.disabled:hover {
    background-color: transparent;
    border-left: none;
}

.sidebar a.disabled::after {
    content: '(Soon)';
    font-size: 11px;
    margin-left: 8px;
    opacity: 0.7;
}

.dropdown-menu {
    position: relative;
}

.dropdown-toggle {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    position: relative;
}

.dropdown-arrow {
    font-size: 10px;
    margin-left: 8px;
    transition: transform 0.3s ease;
}

.dropdown-toggle.active .dropdown-arrow,
.dropdown-menu:hover .dropdown-arrow {
    transform: rotate(180deg);
}

.dropdown-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease;
    background: rgba(0, 0, 0, 0.2);
}

.dropdown-content.show {
    max-height: 250px;
}

.dropdown-content a {
    padding: 10px 25px 10px 40px;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.dropdown-content a:hover {
    background-color: rgba(27, 59, 95, 0.5);
}

.dropdown-content a.active {
    background-color: #1b3b5f;
    border-left: 4px solid #00aaff;
}

.update-indicator {
    position: fixed;
    bottom: 20px;
    left: 300px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 10px 18px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    opacity: 0;
    transition: opacity 0.3s, transform 0.3s;
    z-index: 9999;
    transform: translateY(20px);
}

.update-indicator.show {
    opacity: 1;
    transform: translateY(0);
}

.update-indicator.task-update {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.update-indicator.alert-update {
    background: linear-gradient(135deg, #ff9800 0%, #ff6b6b 100%);
}
</style>

<div id="updateIndicator" class="update-indicator">
    <span id="updateMessage">✓ New work item assigned</span>
</div>

<script>
function toggleDropdown(event) {
    event.preventDefault();
    const dropdown = event.currentTarget.parentElement;
    const content = dropdown.querySelector('.dropdown-content');
    const arrow = dropdown.querySelector('.dropdown-arrow');
    
    content.classList.toggle('show');
    arrow.style.transform = content.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-expand dropdown if on work pages
    const isWorkPage = {{ request()->routeIs('nurse.work-dashboard') || request()->routeIs('nurse.tasks*') || request()->routeIs('nurse.alerts*') ? 'true' : 'false' }};
    if (isWorkPage) {
        const dropdown = document.querySelector('.dropdown-content');
        const arrow = document.querySelector('.dropdown-arrow');
        if (dropdown) {
            dropdown.classList.add('show');
            if (arrow) arrow.style.transform = 'rotate(180deg)';
        }
    }
    
    // Start realtime updates
    startRealtimeUpdates();
});

function startRealtimeUpdates() {
    // Update every 15 seconds
    setInterval(async () => {
        try {
            const response = await fetch('/nurse/work/refresh-counts');
            if (!response.ok) return;
            
            const data = await response.json();
            
            // Store previous counts
            const prevTasks = parseInt(document.getElementById('tasksBadge')?.textContent || '0');
            const prevAlerts = parseInt(document.getElementById('alertsBadge')?.textContent || '0');
            const prevTotal = prevTasks + prevAlerts;
            
            const newTotal = data.pending_tasks + data.unread_alerts;
            
            // Update all badges
            updateBadge('totalWorkBadge', newTotal);
            updateBadge('activeWorkBadge', newTotal);
            updateBadge('tasksBadge', data.pending_tasks);
            updateBadge('alertsBadge', data.unread_alerts);
            
            // Show notification if new items
            if (newTotal > prevTotal) {
                const newTasks = data.pending_tasks > prevTasks;
                const newAlerts = data.unread_alerts > prevAlerts;
                
                if (newTasks && newAlerts) {
                    showUpdateNotification('📋 New task and alert received', 'task-update');
                } else if (newTasks) {
                    showUpdateNotification('📋 New task assigned by doctor', 'task-update');
                } else if (newAlerts) {
                    showUpdateNotification('🔔 New alert received', 'alert-update');
                }
                
                // Play notification sound (optional)
                playNotificationSound();
            }
            
        } catch (error) {
            console.error('Failed to refresh work counts:', error);
        }
    }, 15000); // 15 seconds
}

function updateBadge(badgeId, count) {
    const badge = document.getElementById(badgeId);
    if (!badge) return;
    
    const oldCount = parseInt(badge.textContent || '0');
    
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
        
        // Animate if count increased
        if (count > oldCount) {
            badge.style.animation = 'none';
            setTimeout(() => {
                badge.style.animation = 'pulse-badge 0.6s ease-out';
            }, 10);
        }
    } else {
        badge.style.display = 'none';
    }
}

function showUpdateNotification(message, type = '') {
    const indicator = document.getElementById('updateIndicator');
    const messageEl = document.getElementById('updateMessage');
    
    messageEl.textContent = message;
    indicator.className = 'update-indicator show ' + type;
    
    // Hide after 4 seconds
    setTimeout(() => {
        indicator.classList.remove('show');
    }, 4000);
}

function playNotificationSound() {
    // Optional: Add notification sound
    // const audio = new Audio('/sounds/notification.mp3');
    // audio.volume = 0.3;
    // audio.play().catch(e => console.log('Audio play failed:', e));
}

// ✅ Listen for visibility change - refresh immediately when tab becomes active
document.addEventListener('visibilitychange', function() {
    if (!document.hidden) {
        // Tab became visible - refresh counts immediately
        fetch('/nurse/work/refresh-counts')
            .then(response => response.json())
            .then(data => {
                const newTotal = data.pending_tasks + data.unread_alerts;
                updateBadge('totalWorkBadge', newTotal);
                updateBadge('activeWorkBadge', newTotal);
                updateBadge('tasksBadge', data.pending_tasks);
                updateBadge('alertsBadge', data.unread_alerts);
            })
            .catch(error => console.error('Failed to refresh on tab focus:', error));
    }
});
</script>