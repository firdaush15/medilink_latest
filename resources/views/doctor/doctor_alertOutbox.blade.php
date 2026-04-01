{{-- resources/views/doctor/doctor_alertOutbox.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediLink | Sent Alerts</title>
    @vite(['resources/css/sidebar.css', 'resources/css/doctor/doctor_alerts.css'])
</head>
<body>

@include('doctor.sidebar.doctor_sidebar')

<div class="main">

    {{-- Flash Messages --}}
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

    {{-- Page Header --}}
    <div class="page-header">
        <div class="header-left">
            <h1>📤 Sent Alerts</h1>
            <p>A history of all alerts you have sent to your team</p>
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
        </div>
    </div>

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card blue">
            <div class="stat-icon">📨</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['total_alerts'] }}</div>
                <div class="stat-label">Total Sent</div>
            </div>
        </div>
        <div class="stat-card green">
            <div class="stat-icon">📅</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['today'] }}</div>
                <div class="stat-label">Sent Today</div>
            </div>
        </div>
        <div class="stat-card orange">
            <div class="stat-icon">✓✓</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['read'] }}</div>
                <div class="stat-label">Read by Recipient</div>
            </div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon">✅</div>
            <div class="stat-content">
                <div class="stat-value">{{ $stats['acknowledged'] }}</div>
                <div class="stat-label">Acknowledged</div>
            </div>
        </div>
    </div>

    {{-- Sent Alerts List --}}
    <div class="alerts-container">
        @forelse($sentAlerts as $alert)
        <div class="alert-item sent priority-{{ strtolower($alert->priority) }}">
            <div class="alert-priority-indicator priority-{{ strtolower($alert->priority) }}"></div>

            <div class="alert-icon {{ strtolower($alert->priority) }}">
                @switch($alert->priority)
                    @case('Critical') 🚨 @break
                    @case('Urgent')   ⚡ @break
                    @case('High')     ⚠️ @break
                    @default          ℹ️
                @endswitch
            </div>

            <div class="alert-content">
                <div class="alert-header">
                    <div class="alert-title">{{ $alert->alert_title }}</div>
                    <div class="alert-time">{{ $alert->created_at->format('M d, Y g:i A') }}</div>
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
                        To: {{ $alert->recipient->name }} ({{ ucfirst($alert->recipient_type) }})
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

                    <span class="meta-item">🏷️ {{ $alert->alert_type }}</span>

                    @if($alert->is_acknowledged)
                        <span class="status-badge acknowledged">✅ Acknowledged</span>
                    @elseif($alert->is_read)
                        <span class="status-badge read">✓✓ Read</span>
                    @else
                        <span class="status-badge sent">✓ Delivered</span>
                    @endif
                </div>
            </div>

            <div class="alert-actions">
                <form action="{{ route('doctor.alerts.destroy', $alert->alert_id) }}"
                      method="POST"
                      onsubmit="return confirm('Delete this alert?')"
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
            <h3>No Alerts Sent Yet</h3>
            <p>Use the "Send Alert" button to notify your team about something important.</p>
        </div>
        @endforelse
    </div>

    @if($sentAlerts->hasPages())
    <div class="pagination-container">
        {{ $sentAlerts->links() }}
    </div>
    @endif

</div>

{{-- Send Alert Modal --}}
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
                        <option value="Prescription Change">Prescription Change</option>
                        <option value="Lab Results">Lab Results</option>
                        <option value="Patient Update">Patient Update</option>
                        <option value="Reschedule Request">Reschedule Request</option>
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
                <textarea name="alert_message" rows="4" required placeholder="Describe what action is needed..."></textarea>
            </div>

            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeModal('newAlertModal')">Cancel</button>
                <button type="submit" class="btn-primary">Send Alert</button>
            </div>
        </form>
    </div>
</div>

<style>
.status-badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
}
.status-badge.sent         { background: #e3f2fd; color: #1565c0; }
.status-badge.read         { background: #e8f5e9; color: #2e7d32; }
.status-badge.acknowledged { background: #f3e5f5; color: #6a1b9a; }

.stat-card.purple { border-left: 4px solid #9c27b0; }
.stat-card.purple .stat-icon { background: linear-gradient(135deg, #9c27b0, #673ab7); }
</style>

<script>
const recipients = {
    nurse:        @json($nurses),
    pharmacist:   @json($pharmacists),
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

function updateAlertRecipientList() {
    const type   = document.getElementById('alertRecipientType').value;
    const select = document.getElementById('alertRecipientId');
    select.innerHTML = '<option value="">Choose a recipient</option>';
    if (type && recipients[type]) {
        recipients[type].forEach(r => {
            select.innerHTML += `<option value="${r.id}">${r.name}</option>`;
        });
    }
}

window.onclick = function (event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
    }
};
</script>
@vite(['resources/js/sidebar.js'])
</body>
</html>