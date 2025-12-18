{{-- resources/views/doctor/doctor_alertOutbox.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink | Send Alerts & Tasks</title>
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

    @if(session('error'))
    <div class="alert alert-error">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
        {{ session('error') }}
    </div>
    @endif

    <!-- Page Header -->
    <div class="page-header">
        <div class="header-left">
            <h1>📤 Send Alerts & Assign Tasks</h1>
            <p>Send alerts or assign tasks to nurses, pharmacists, and other staff</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('doctor.alerts.inbox') }}" class="btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                </svg>
                View Inbox
            </a>
            <button class="btn-primary" onclick="openNewAlertModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="16"></line>
                    <line x1="8" y1="12" x2="16" y2="12"></line>
                </svg>
                Send Alert
            </button>
            <button class="btn-primary" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);" onclick="openNewTaskModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 11l3 3L22 4"></path>
                    <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                </svg>
                Assign Task
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">📨</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['total_alerts'] }}</div>
                <div class="stat-label">Alerts Sent</div>
            </div>
        </div>

        <div class="stat-card purple">
            <div class="stat-icon">📋</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['total_tasks'] }}</div>
                <div class="stat-label">Tasks Assigned</div>
            </div>
        </div>

        <div class="stat-card orange">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['pending_tasks'] }}</div>
                <div class="stat-label">Pending Tasks</div>
            </div>
        </div>

        <div class="stat-card green">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['completed_today'] }}</div>
                <div class="stat-label">Completed Today</div>
            </div>
        </div>
    </div>

    <!-- Quick Assign Templates -->
    <div class="quick-send-section">
        <h3>⚡ Quick Assign Tasks</h3>
        <p class="section-subtitle">Quickly assign common tasks to your team</p>
        
        <div class="quick-send-grid">
            <!-- To Nurse -->
            <div class="quick-send-category">
                <h4>👩‍⚕️ To Nurse</h4>
                <button class="quick-send-card" onclick="openQuickTask('Vital Signs Check', 'nurse')">
                    <div class="task-icon">🩺</div>
                    <div class="task-title">Vital Signs Check</div>
                </button>
                <button class="quick-send-card" onclick="openQuickTask('Prepare Patient', 'nurse')">
                    <div class="task-icon">🛏️</div>
                    <div class="task-title">Prepare Patient</div>
                </button>
                <button class="quick-send-card" onclick="openQuickTask('Collect Specimen', 'nurse')">
                    <div class="task-icon">🧪</div>
                    <div class="task-title">Collect Specimen</div>
                </button>
                <button class="quick-send-card" onclick="openQuickTask('Medication Reminder', 'nurse')">
                    <div class="task-icon">💊</div>
                    <div class="task-title">Med Reminder</div>
                </button>
            </div>

            <!-- To Pharmacist -->
            <div class="quick-send-category">
                <h4>💊 To Pharmacist</h4>
                <button class="quick-send-card" onclick="openQuickTask('Verify Prescription', 'pharmacist')">
                    <div class="task-icon">✅</div>
                    <div class="task-title">Verify Rx</div>
                </button>
                <button class="quick-send-card" onclick="openQuickTask('Check Stock', 'pharmacist')">
                    <div class="task-icon">📦</div>
                    <div class="task-title">Check Stock</div>
                </button>
            </div>
        </div>
    </div>

    <!-- View Tabs -->
    <div class="filter-section">
        <div class="filter-tabs">
            <button class="filter-tab {{ $view == 'all' ? 'active' : '' }}" onclick="window.location='{{ route('doctor.alerts.outbox', ['view' => 'all']) }}'">
                All Items
            </button>
            <button class="filter-tab {{ $view == 'alerts' ? 'active' : '' }}" onclick="window.location='{{ route('doctor.alerts.outbox', ['view' => 'alerts']) }}'">
                Alerts Only
            </button>
            <button class="filter-tab {{ $view == 'tasks' ? 'active' : '' }}" onclick="window.location='{{ route('doctor.alerts.outbox', ['view' => 'tasks']) }}'">
                Tasks Only
            </button>
        </div>
    </div>

    <!-- Combined List -->
    <div class="alerts-container">
        @forelse($items as $item)
        <div class="alert-item sent priority-{{ strtolower($item['priority']) }} item-type-{{ $item['type'] }}">
            <div class="alert-priority-indicator priority-{{ strtolower($item['priority']) }}"></div>
            
            <div class="alert-icon {{ strtolower($item['priority']) }}">
                @if($item['type'] == 'task')
                    📋
                @else
                    @switch($item['priority'])
                        @case('Critical') 🚨 @break
                        @case('Urgent') ⚡ @break
                        @case('High') ⚠️ @break
                        @default ℹ️
                    @endswitch
                @endif
            </div>
            
            <div class="alert-content">
                <div class="alert-header">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="item-type-badge type-{{ $item['type'] }}">
                            {{ $item['type'] == 'task' ? 'TASK' : 'ALERT' }}
                        </span>
                        <div class="alert-title">{{ $item['title'] }}</div>
                    </div>
                    <div class="alert-time">{{ $item['created_at'] }}</div>
                </div>
                
                <div class="alert-message">{{ $item['message'] }}</div>
                
                <div class="alert-meta">
                    <span class="priority-badge priority-{{ strtolower($item['priority']) }}">
                        {{ $item['priority'] }}
                    </span>
                    
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        To: {{ $item['recipient_name'] }} ({{ ucfirst($item['recipient_type']) }})
                    </span>
                    
                    @if($item['patient_name'])
                    <span class="meta-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="8.5" cy="7" r="4"></circle>
                        </svg>
                        Patient: {{ $item['patient_name'] }}
                    </span>
                    @endif

                    @if($item['type'] == 'task')
                        <span class="status-badge status-{{ strtolower($item['status']) }}">
                            {{ ucfirst($item['status']) }}
                        </span>
                        @if($item['due_at'])
                        <span class="meta-item">
                            ⏰ Due: {{ $item['due_at'] }}
                        </span>
                        @endif
                    @else
                        @if($item['is_read'])
                        <span class="status-badge read">✓✓ Read</span>
                        @else
                        <span class="status-badge sent">✓ Sent</span>
                        @endif
                    @endif
                </div>
            </div>
            
            <div class="alert-actions">
                <form action="{{ $item['type'] == 'task' ? route('doctor.tasks.destroy', $item['id']) : route('doctor.alerts.destroy', $item['id']) }}" 
                      method="POST" 
                      onsubmit="return confirm('Are you sure you want to delete this {{ $item['type'] }}?')"
                      style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="icon-btn danger" title="Delete">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="empty-state">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <h3>No Items Found</h3>
            <p>You haven't sent any {{ $view == 'all' ? 'alerts or tasks' : $view }} yet.</p>
        </div>
        @endforelse
    </div>

    @if($items->hasPages())
    <div class="pagination-container">
        {{ $items->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- New Alert Modal -->
<div id="newAlertModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('newAlertModal')">×</button>
        <h2>📤 Send Alert</h2>
        
        <form action="{{ route('doctor.alerts.send') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label>Send To <span class="required">*</span></label>
                <select name="recipient_type" id="alertRecipientType" onchange="updateAlertRecipientList()" required>
                    <option value="">Select recipient type</option>
                    <option value="nurse">👩‍⚕️ Nurse</option>
                    <option value="pharmacist">💊 Pharmacist</option>
                    <option value="receptionist">📋 Receptionist</option>
                </select>
            </div>

            <div class="form-group">
                <label>Select Recipient <span class="required">*</span></label>
                <select name="recipient_id" id="alertRecipientId" required>
                    <option value="">Choose a recipient</option>
                </select>
            </div>

            <div class="form-group">
                <label>Patient (Optional)</label>
                <select name="patient_id">
                    <option value="">Select a patient</option>
                    @foreach($patients as $patient)
                    <option value="{{ $patient->patient_id }}">{{ $patient->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Alert Type <span class="required">*</span></label>
                    <select name="alert_type" required>
                        <option value="Critical Vitals">Critical Vitals</option>
                        <option value="Medication Due">Medication Due</option>
                        <option value="Lab Results">Lab Results</option>
                        <option value="General Alert">General Alert</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Priority <span class="required">*</span></label>
                    <select name="priority" required>
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Alert Title <span class="required">*</span></label>
                <input type="text" name="alert_title" required placeholder="Brief title for the alert">
            </div>

            <div class="form-group">
                <label>Message <span class="required">*</span></label>
                <textarea name="alert_message" rows="4" required placeholder="Detailed alert message"></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('newAlertModal')">Cancel</button>
                <button type="submit" class="btn-primary">Send Alert</button>
            </div>
        </form>
    </div>
</div>

<!-- New Task Modal -->
<div id="newTaskModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('newTaskModal')">×</button>
        <h2>📋 Assign Task</h2>
        
        <form action="{{ route('doctor.tasks.assign') }}" method="POST">
            @csrf
            
            <div class="form-group">
                <label>Assign To <span class="required">*</span></label>
                <select name="assigned_to_type" id="taskRecipientType" onchange="updateTaskRecipientList()" required>
                    <option value="">Select staff type</option>
                    <option value="nurse">👩‍⚕️ Nurse</option>
                    <option value="pharmacist">💊 Pharmacist</option>
                    <option value="receptionist">📋 Receptionist</option>
                </select>
            </div>

            <div class="form-group">
                <label>Select Staff Member <span class="required">*</span></label>
                <select name="assigned_to_id" id="taskRecipientId" required>
                    <option value="">Choose a staff member</option>
                </select>
            </div>

            <div class="form-group">
                <label>Patient <span class="required">*</span></label>
                <select name="patient_id" required>
                    <option value="">Select a patient</option>
                    @foreach($patients as $patient)
                    <option value="{{ $patient->patient_id }}">{{ $patient->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Task Type <span class="required">*</span></label>
                    <select name="task_type" required>
                        <option value="Vital Signs Check">Vital Signs Check</option>
                        <option value="Prepare Patient">Prepare Patient</option>
                        <option value="Collect Specimen">Collect Specimen</option>
                        <option value="Medication Reminder">Medication Reminder</option>
                        <option value="Pre-Procedure Prep">Pre-Procedure Prep</option>
                        <option value="Post-Procedure Care">Post-Procedure Care</option>
                        <option value="Patient Education">Patient Education</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Priority <span class="required">*</span></label>
                    <select name="priority" required>
                        <option value="Normal">Normal</option>
                        <option value="High">High</option>
                        <option value="Urgent">Urgent</option>
                        <option value="Critical">Critical</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Task Title <span class="required">*</span></label>
                <input type="text" name="task_title" required placeholder="e.g., Check vital signs before surgery">
            </div>

            <div class="form-group">
                <label>Task Description</label>
                <textarea name="task_description" rows="4" placeholder="Detailed instructions..."></textarea>
            </div>

            <div class="form-group">
                <label>Due Date/Time</label>
                <input type="datetime-local" name="due_at">
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('newTaskModal')">Cancel</button>
                <button type="submit" class="btn-primary">Assign Task</button>
            </div>
        </form>
    </div>
</div>

<!-- Quick Task Modal -->
<div id="quickTaskModal" class="modal">
    <div class="modal-content">
        <button class="close-btn" onclick="closeModal('quickTaskModal')">×</button>
        <h2>⚡ Quick Assign Task</h2>
        
        <form action="{{ route('doctor.tasks.quick-assign') }}" method="POST">
            @csrf
            <input type="hidden" name="task_type" id="quickTaskType">
            <input type="hidden" name="assigned_to_type" id="quickTaskRecipientType">
            
            <div class="form-group">
                <label>Select Patient <span class="required">*</span></label>
                <select name="patient_id" required>
                    <option value="">Choose a patient</option>
                    @foreach($patients as $patient)
                    <option value="{{ $patient->patient_id }}">{{ $patient->user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Assign to <span class="required">*</span></label>
                <select name="assigned_to_id" id="quickTaskRecipientId" required>
                    <option value="">Choose a staff member</option>
                </select>
            </div>

            <div class="form-group">
                <label>Priority <span class="required">*</span></label>
                <select name="priority" required>
                    <option value="Normal">Normal</option>
                    <option value="High" selected>High</option>
                    <option value="Urgent">Urgent</option>
                    <option value="Critical">Critical</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('quickTaskModal')">Cancel</button>
                <button type="submit" class="btn-primary">Assign Task</button>
            </div>
        </form>
    </div>
</div>

<style>
.item-type-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
}

.type-task {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.type-alert {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}

.status-pending { background: #fff3e0; color: #f57c00; }
.status-in_progress { background: #e3f2fd; color: #1976d2; }
.status-completed { background: #e8f5e9; color: #2e7d32; }
.status-cancelled { background: #ffebee; color: #c62828; }
</style>

<script>
const recipients = {
    nurse: @json($nurses),
    pharmacist: @json($pharmacists),
    receptionist: @json($receptionists),
};

function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}

function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function openNewAlertModal() {
    openModal('newAlertModal');
}

function openNewTaskModal() {
    openModal('newTaskModal');
}

function updateAlertRecipientList() {
    const type = document.getElementById('alertRecipientType').value;
    const select = document.getElementById('alertRecipientId');
    select.innerHTML = '<option value="">Choose a recipient</option>';
    if (type && recipients[type]) {
        recipients[type].forEach(r => {
            select.innerHTML += `<option value="${r.id}">${r.name}</option>`;
        });
    }
}

function updateTaskRecipientList() {
    const type = document.getElementById('taskRecipientType').value;
    const select = document.getElementById('taskRecipientId');
    select.innerHTML = '<option value="">Choose a staff member</option>';
    if (type && recipients[type]) {
        recipients[type].forEach(r => {
            select.innerHTML += `<option value="${r.id}">${r.name}</option>`;
        });
    }
}

function openQuickTask(taskType, recipientType) {
    document.getElementById('quickTaskType').value = taskType;
    document.getElementById('quickTaskRecipientType').value = recipientType;
    
    const select = document.getElementById('quickTaskRecipientId');
    select.innerHTML = '<option value="">Choose a staff member</option>';
    if (recipients[recipientType]) {
        recipients[recipientType].forEach(r => {
            select.innerHTML += `<option value="${r.id}">${r.name}</option>`;
        });
    }
    
    openModal('quickTaskModal');
}

window.onclick = function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
}
</script>

</body>
</html>