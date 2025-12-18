<!--nurse_tasks.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Tasks - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css', 'resources/css/nurse/nurse_tasks.css'])
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <!-- Flash Messages -->
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <!-- Page Header -->
        <div class="page-header">
            <div class="header-left">
                <h1>Doctor-Assigned Tasks</h1>
                <p>View and complete preparation tasks from doctors</p>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="stats-row">
            <div class="stat-mini orange">
                <div class="stat-mini-icon">⏰</div>
                <div class="stat-mini-content">
                    <h3>{{ $counts['today'] }}</h3>
                    <p>Due Today</p>
                </div>
            </div>

            <div class="stat-mini red">
                <div class="stat-mini-icon">🔥</div>
                <div class="stat-mini-content">
                    <h3>{{ $counts['urgent'] }}</h3>
                    <p>Urgent Tasks</p>
                </div>
            </div>

            <div class="stat-mini blue">
                <div class="stat-mini-icon">📋</div>
                <div class="stat-mini-content">
                    <h3>{{ $counts['in_progress'] }}</h3>
                    <p>In Progress</p>
                </div>
            </div>

            <div class="stat-mini green">
                <div class="stat-mini-icon">✅</div>
                <div class="stat-mini-content">
                    <h3>{{ $counts['completed_week'] }}</h3>
                    <p>Completed This Week</p>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="{{ route('nurse.tasks', ['filter' => 'all']) }}" 
               class="filter-tab {{ $filter == 'all' ? 'active' : '' }}">
                All Active<span class="count-badge">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('nurse.tasks', ['filter' => 'urgent']) }}" 
               class="filter-tab {{ $filter == 'urgent' ? 'active' : '' }}">
                🔥 Urgent<span class="count-badge">{{ $counts['urgent'] }}</span>
            </a>
            <a href="{{ route('nurse.tasks', ['filter' => 'today']) }}" 
               class="filter-tab {{ $filter == 'today' ? 'active' : '' }}">
                ⏰ Due Today<span class="count-badge">{{ $counts['today'] }}</span>
            </a>
            <a href="{{ route('nurse.tasks', ['filter' => 'overdue']) }}" 
               class="filter-tab {{ $filter == 'overdue' ? 'active' : '' }}">
                ⚠️ Overdue<span class="count-badge">{{ $counts['overdue'] }}</span>
            </a>
            <a href="{{ route('nurse.tasks', ['filter' => 'pending']) }}" 
               class="filter-tab {{ $filter == 'pending' ? 'active' : '' }}">
                Pending<span class="count-badge">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('nurse.tasks', ['filter' => 'in_progress']) }}" 
               class="filter-tab {{ $filter == 'in_progress' ? 'active' : '' }}">
                In Progress<span class="count-badge">{{ $counts['in_progress'] }}</span>
            </a>
            <a href="{{ route('nurse.tasks', ['filter' => 'completed']) }}" 
               class="filter-tab {{ $filter == 'completed' ? 'active' : '' }}">
                Completed<span class="count-badge">{{ $counts['completed_week'] }}</span>
            </a>
        </div>

        <!-- Tasks List -->
        @if($tasks->count() > 0)
            <div class="tasks-list">
                @foreach($tasks as $task)
                <div class="task-card {{ strtolower($task->priority) }} {{ $task->is_overdue ? 'overdue' : '' }}">
                    <div class="task-header">
                        <div>
                            <h3 class="task-title">{{ $task->task_title }}</h3>
                            <span class="priority-badge priority-{{ strtolower($task->priority) }}">
                                {{ $task->priority }}
                            </span>
                            <span class="badge badge-{{ $task->status == 'Completed' ? 'success' : ($task->status == 'In Progress' ? 'primary' : 'secondary') }}">
                                {{ $task->status }}
                            </span>
                            @if($task->is_overdue && $task->status != 'Completed')
                            <span class="badge badge-danger">OVERDUE</span>
                            @endif
                        </div>
                    </div>

                    @if($task->task_description)
                    <p style="margin: 12px 0; color: #666; line-height: 1.6;">
                        {{ $task->task_description }}
                    </p>
                    @endif

                    <div class="task-details">
                        @if($task->patient)
                        <div class="task-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <strong>Patient:</strong> {{ $task->patient->user->name }}
                        </div>
                        @endif

                        @if($task->doctor)
                        <div class="task-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                            </svg>
                            <strong>Assigned by:</strong> Dr. {{ $task->doctor->user->name }}
                        </div>
                        @endif

                        <div class="task-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <polyline points="12 6 12 12 16 14"/>
                            </svg>
                            <strong>Due:</strong> 
                            <span class="{{ $task->is_overdue ? 'text-danger' : '' }}">
                                {{ $task->time_remaining }}
                            </span>
                        </div>

                        <div class="task-detail-item">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                            </svg>
                            <strong>Type:</strong> {{ $task->task_type }}
                        </div>
                    </div>

                    @if($task->completion_notes && $task->status == 'Completed')
                    <div style="margin-top: 12px; padding: 12px; background: #e8f5e9; border-radius: 8px;">
                        <strong>Completion Notes:</strong>
                        <p style="margin: 4px 0 0 0; color: #2e7d32;">{{ $task->completion_notes }}</p>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="task-actions">
                        @if($task->status == 'Pending')
                        <form method="POST" action="{{ route('nurse.tasks.update-status', $task->task_id) }}" style="display: inline;">
                            @csrf
                            <input type="hidden" name="status" value="In Progress">
                            <button type="submit" class="btn btn-primary btn-sm">
                                Start Task
                            </button>
                        </form>
                        @endif

                        @if($task->status == 'In Progress' || $task->status == 'Pending')
                        <button class="btn btn-success btn-sm" onclick="openCompleteModal({{ $task->task_id }}, '{{ $task->task_title }}')">
                            ✓ Complete Task
                        </button>
                        @endif

                        @if($task->patient)
                        <a href="{{ route('nurse.patients.show', $task->patient_id) }}" class="btn btn-outline btn-sm">
                            View Patient
                        </a>
                        @endif

                        @if($task->appointment)
                        <a href="{{ route('nurse.appointments') }}" class="btn btn-outline btn-sm">
                            View Appointment
                        </a>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="pagination-wrapper">
                {{ $tasks->appends(request()->query())->links() }}
            </div>
        @else
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <polyline points="9 11 12 14 22 4"/>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                </svg>
                <h3>No Tasks Found</h3>
                <p>
                    @if($filter == 'completed')
                    No completed tasks in the last 7 days.
                    @elseif($filter == 'overdue')
                    Great job! No overdue tasks.
                    @else
                    No {{ $filter }} tasks at the moment.
                    @endif
                </p>
                <a href="{{ route('nurse.tasks') }}" class="btn btn-primary">View All Tasks</a>
            </div>
        @endif
    </div>

    <!-- Complete Task Modal -->
    <div id="completeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Complete Task</h2>
                <button class="modal-close" onclick="closeCompleteModal()">×</button>
            </div>
            <form id="completeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p id="completeTaskTitle" style="margin-bottom: 20px; font-weight: 500;"></p>
                    <div class="form-group">
                        <label>Completion Notes (Optional)</label>
                        <textarea name="completion_notes" rows="4" class="form-control" placeholder="Add any notes about how the task was completed..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeCompleteModal()">Cancel</button>
                    <button type="submit" class="btn btn-success">Mark as Complete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCompleteModal(taskId, taskTitle) {
            document.getElementById('completeTaskTitle').textContent = taskTitle;
            document.getElementById('completeForm').action = `/nurse/tasks/${taskId}/complete`;
            document.getElementById('completeModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeCompleteModal() {
            document.getElementById('completeModal').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('completeForm').reset();
        }

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeCompleteModal();
            }
        });

        // Close on outside click
        document.getElementById('completeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeCompleteModal();
            }
        });
    </script>
</body>
</html>