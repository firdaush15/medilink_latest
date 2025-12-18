<!--nurse_work_dashboard.blade.php - UNIFIED ALERTS & TASKS-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Work Dashboard - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css', 'resources/css/nurse/nurse_workDashboard.css'])
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <!-- Page Header -->
        <div class="page-header">
            <h1>🎯 My Work Dashboard</h1>
            <p>All your tasks and alerts in one place</p>
        </div>

        <!-- Quick Stats -->
        <div class="quick-stats">
            <div class="stat-card critical">
                <div class="stat-value">{{ $counts['critical_alerts'] }}</div>
                <div class="stat-label">Critical Alerts</div>
            </div>
            <div class="stat-card urgent">
                <div class="stat-value">{{ $counts['urgent_tasks'] }}</div>
                <div class="stat-label">Urgent Tasks</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ $counts['pending_tasks'] }}</div>
                <div class="stat-label">Pending Tasks</div>
            </div>
            <div class="stat-card success">
                <div class="stat-value">{{ $counts['completed_today'] }}</div>
                <div class="stat-label">Completed Today</div>
            </div>
        </div>

        <!-- Two-Column Layout -->
        <div class="work-layout">
            <!-- Main Work Area -->
            <div class="main-work">
                <div class="work-header">
                    <h2>Active Work Items</h2>
                    <div class="tab-filters">
                        <button class="tab-filter active" data-filter="all">All</button>
                        <button class="tab-filter" data-filter="tasks">Tasks Only</button>
                        <button class="tab-filter" data-filter="alerts">Alerts Only</button>
                    </div>
                </div>

                <!-- Combined Work Items -->
                @forelse($workItems as $item)
                <div class="work-item {{ $item['priority_class'] }}" data-type="{{ $item['type'] }}">
                    <div class="work-item-header">
                        <div>
                            <span class="work-type type-{{ $item['type'] }}">
                                {{ $item['type'] == 'task' ? '📋 TASK' : '🔔 ALERT' }}
                            </span>
                            <div class="work-title">{{ $item['title'] }}</div>
                        </div>
                        <div style="font-size: 12px; color: #a0aec0;">
                            {{ $item['time_ago'] }}
                        </div>
                    </div>

                    <div class="work-message">
                        {{ $item['message'] }}
                    </div>

                    <div class="work-meta">
                        @if($item['patient'])
                        <span>👤 {{ $item['patient'] }}</span>
                        @endif
                        @if($item['doctor'])
                        <span>👨‍⚕️ {{ $item['doctor'] }}</span>
                        @endif
                        @if($item['priority'])
                        <span class="priority-dot {{ strtolower($item['priority']) }}"></span>
                        <span>{{ $item['priority'] }} Priority</span>
                        @endif
                        @if($item['due_time'])
                        <span>⏰ Due: {{ $item['due_time'] }}</span>
                        @endif
                    </div>

                    <div class="work-actions">
                        @if($item['type'] == 'task')
                            @if($item['status'] == 'Pending')
                            <form action="{{ route('nurse.tasks.start', $item['id']) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary">Start Task</button>
                            </form>
                            @endif
                            
                            @if(in_array($item['status'], ['Pending', 'In Progress']))
                            <button class="btn btn-success" onclick="completeTask({{ $item['id'] }}, '{{ $item['title'] }}')">
                                ✓ Complete
                            </button>
                            @endif
                            
                            <a href="{{ route('nurse.patients.show', $item['patient_id']) }}" class="btn btn-outline">
                                View Patient
                            </a>
                        @else
                            @if($item['action_url'])
                            <a href="{{ $item['action_url'] }}" class="btn btn-primary">Take Action</a>
                            @endif
                            
                            @if(!$item['is_read'])
                            <form action="{{ route('nurse.alerts.mark-read', $item['id']) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-outline">Mark as Read</button>
                            </form>
                            @endif
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polyline points="9 11 12 14 22 4"/>
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                    </svg>
                    <h3>All Caught Up!</h3>
                    <p>No active work items at the moment.</p>
                </div>
                @endforelse
            </div>

            <!-- Quick Alerts Sidebar -->
            <div class="alerts-sidebar">
                <div class="sidebar-header">
                    <h3>🔔 Recent Alerts</h3>
                </div>
                
                @foreach($recentAlerts as $alert)
                <div class="alert-item {{ !$alert->is_read ? 'unread' : '' }}">
                    <div class="alert-title-small">
                        <span class="priority-dot {{ strtolower($alert->priority) }}"></span>
                        {{ $alert->alert_title }}
                    </div>
                    <div class="alert-message-small">
                        {{ Str::limit($alert->alert_message, 80) }}
                    </div>
                    <div class="alert-time-small">
                        {{ $alert->created_at->diffForHumans() }}
                    </div>
                </div>
                @endforeach
                
                <div style="padding: 16px; text-align: center;">
                    <a href="{{ route('nurse.alerts') }}" style="color: #667eea; font-size: 13px; font-weight: 600;">
                        View All Alerts →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Task Modal -->
    <div id="completeModal" class="modal" style="display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; width: 90%; max-width: 500px; padding: 0;">
            <div style="padding: 24px; border-bottom: 1px solid #e1e8ed;">
                <h2 style="margin: 0; font-size: 20px;">Complete Task</h2>
            </div>
            <form id="completeForm" method="POST">
                @csrf
                <div style="padding: 24px;">
                    <p id="taskTitle" style="margin-bottom: 20px; font-weight: 600;"></p>
                    <label style="display: block; font-weight: 600; margin-bottom: 8px;">Completion Notes</label>
                    <textarea name="completion_notes" rows="4" style="width: 100%; padding: 12px; border: 1px solid #e1e8ed; border-radius: 8px; font-family: inherit;"></textarea>
                </div>
                <div style="padding: 16px 24px; border-top: 1px solid #e1e8ed; display: flex; gap: 12px; justify-content: flex-end;">
                    <button type="button" class="btn btn-outline" onclick="closeCompleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Complete Task</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Filter tabs
        document.querySelectorAll('.tab-filter').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-filter').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                document.querySelectorAll('.work-item').forEach(item => {
                    if (filter === 'all') {
                        item.style.display = 'block';
                    } else if (filter === 'tasks' && item.dataset.type === 'task') {
                        item.style.display = 'block';
                    } else if (filter === 'alerts' && item.dataset.type === 'alert') {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        function completeTask(taskId, title) {
            document.getElementById('taskTitle').textContent = title;
            document.getElementById('completeForm').action = `/nurse/tasks/${taskId}/complete`;
            document.getElementById('completeModal').style.display = 'flex';
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').style.display = 'none';
        }

        // Auto-refresh every 30 seconds
        setInterval(() => location.reload(), 30000);
    </script>
</body>
</html>