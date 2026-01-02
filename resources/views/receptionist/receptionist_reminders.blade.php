<!--receptionist_reminders.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Reminders - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_appointments.css'])
    <style>
        .reminders-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin: 30px 0;
        }
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            color: #2196F3;
            margin: 10px 0;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .bulk-actions {
            display: flex;
            gap: 10px;
        }
        .filter-tabs {
            display: flex;
            gap: 10px;
        }
        .tab-btn {
            padding: 10px 20px;
            border: 2px solid #ddd;
            border-radius: 5px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        .tab-btn:hover {
            border-color: #2196F3;
        }
        .tab-btn.active {
            background: #2196F3;
            color: white;
            border-color: #2196F3;
        }
        .reminders-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .reminder-row {
            display: grid;
            grid-template-columns: 50px 1fr 150px 150px 120px 150px 150px;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
        }
        .reminder-row:hover {
            background: #f9f9f9;
        }
        .reminder-row.header {
            background: #f5f5f5;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-sent {
            background: #d4edda;
            color: #155724;
        }
        .status-failed {
            background: #f8d7da;
            color: #721c24;
        }
        .type-badge {
            padding: 4px 10px;
            border-radius: 10px;
            font-size: 12px;
        }
        .type-sms {
            background: #e3f2fd;
            color: #1976D2;
        }
        .type-email {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        .action-btns {
            display: flex;
            gap: 5px;
        }
        .btn-icon {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-send {
            background: #4CAF50;
            color: white;
        }
        .btn-cancel {
            background: #f44336;
            color: white;
        }
        .btn-retry {
            background: #ff9800;
            color: white;
        }
        .pagination-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="reminders-header">
            <h1>📨 Appointment Reminders Management</h1>
            <p>Automated SMS and Email reminders for upcoming appointments</p>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            <span class="icon">✓</span>
            <span>{{ session('success') }}</span>
        </div>
        @endif

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">⏳</div>
                <div class="stat-number">{{ $stats['pending'] }}</div>
                <div class="stat-label">Pending</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-number">{{ $stats['sent'] }}</div>
                <div class="stat-label">Sent Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">❌</div>
                <div class="stat-number">{{ $stats['failed'] }}</div>
                <div class="stat-label">Failed Today</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-number">{{ $stats['sent'] > 0 ? number_format(($stats['sent'] / ($stats['sent'] + $stats['failed'])) * 100, 1) : 0 }}%</div>
                <div class="stat-label">Success Rate</div>
            </div>
        </div>

        <!-- Actions Bar -->
        <div class="actions-bar">
            <div class="bulk-actions">
                <button class="btn btn-primary" onclick="sendAllPending()">
                    📤 Send All Pending
                </button>
                <button class="btn" style="background: #ff9800; color: white;" onclick="retryFailed()">
                    🔄 Retry Failed
                </button>
            </div>
            
            <div class="filter-tabs">
                <button class="tab-btn active" data-status="all">All</button>
                <button class="tab-btn" data-status="pending">Pending</button>
                <button class="tab-btn" data-status="sent">Sent</button>
                <button class="tab-btn" data-status="failed">Failed</button>
            </div>
        </div>

        <!-- Reminders Table -->
        <div class="reminders-table">
            <div class="reminder-row header">
                <div>#</div>
                <div>Patient & Appointment</div>
                <div>Type</div>
                <div>Scheduled For</div>
                <div>Status</div>
                <div>Sent At</div>
                <div>Actions</div>
            </div>

            @forelse($reminders as $reminder)
            <div class="reminder-row" data-status="{{ $reminder->status }}">
                <div>
                    <input type="checkbox" class="reminder-check" value="{{ $reminder->reminder_id }}">
                </div>
                <div>
                    <strong>{{ $reminder->appointment->patient->user->name }}</strong>
                    <div style="font-size: 13px; color: #666; margin-top: 5px;">
                        Dr. {{ $reminder->appointment->doctor->user->name }} •
                        {{ \Carbon\Carbon::parse($reminder->appointment->appointment_time)->format('M d, Y h:i A') }}
                    </div>
                    <div style="font-size: 12px; color: #999; margin-top: 3px;">
                        → {{ $reminder->recipient }}
                    </div>
                </div>
                <div>
                    <span class="type-badge type-{{ $reminder->reminder_type }}">
                        {{ $reminder->reminder_type === 'sms' ? '📱 SMS' : '📧 Email' }}
                    </span>
                </div>
                <div>
                    <div style="font-size: 14px;">
                        {{ $reminder->scheduled_for->format('M d, Y') }}
                    </div>
                    <div style="font-size: 12px; color: #666;">
                        {{ $reminder->scheduled_for->format('h:i A') }}
                    </div>
                </div>
                <div>
                    <span class="status-badge status-{{ $reminder->status }}">
                        {{ ucfirst($reminder->status) }}
                    </span>
                </div>
                <div>
                    @if($reminder->sent_at)
                        <div style="font-size: 14px;">
                            {{ $reminder->sent_at->format('M d, Y') }}
                        </div>
                        <div style="font-size: 12px; color: #666;">
                            {{ $reminder->sent_at->format('h:i A') }}
                        </div>
                    @else
                        <span style="color: #999;">Not sent</span>
                    @endif
                </div>
                <div class="action-btns">
                    @if($reminder->status === 'pending')
                        <form method="POST" action="{{ route('receptionist.reminders.send', $reminder->reminder_id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-icon btn-send" title="Send Now">
                                ▶️
                            </button>
                        </form>
                        <button class="btn-icon btn-cancel" title="Cancel" onclick="cancelReminder({{ $reminder->reminder_id }})">
                            ❌
                        </button>
                    @elseif($reminder->status === 'failed')
                        <form method="POST" action="{{ route('receptionist.reminders.send', $reminder->reminder_id) }}" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn-icon btn-retry" title="Retry">
                                🔄
                            </button>
                        </form>
                    @else
                        <button class="btn-icon" style="background: #e0e0e0; cursor: default;" disabled>
                            ✓
                        </button>
                    @endif
                </div>
            </div>
            @empty
            <div style="padding: 60px; text-align: center; color: #999;">
                <div style="font-size: 48px; margin-bottom: 20px;">📭</div>
                <p>No reminders found</p>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="pagination-container">
            {{ $reminders->links() }}
        </div>

        <!-- Info Panel -->
        <div style="background: #e3f2fd; padding: 20px; border-radius: 10px; margin-top: 30px;">
            <h3 style="margin-top: 0;">📚 How Reminders Work</h3>
            <ul style="line-height: 1.8;">
                <li><strong>Automatic Creation:</strong> Reminders are automatically created when appointments are booked</li>
                <li><strong>Timing:</strong> Sent 24 hours before the scheduled appointment time</li>
                <li><strong>SMS Format:</strong> "Reminder: Your appointment with Dr. [Name] is tomorrow at [Time]. MediLink Hospital."</li>
                <li><strong>Email Format:</strong> Professional email with appointment details and clinic information</li>
                <li><strong>Manual Send:</strong> You can manually send reminders before scheduled time using the ▶️ button</li>
                <li><strong>Retry:</strong> Failed reminders can be retried manually</li>
            </ul>
        </div>
    </div>

    <script>
        // Filter tabs
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const status = this.dataset.status;
                document.querySelectorAll('.reminder-row:not(.header)').forEach(row => {
                    if (status === 'all' || row.dataset.status === status) {
                        row.style.display = 'grid';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        });

        function sendAllPending() {
            if (!confirm('Send all pending reminders now?')) return;
            
            // Collect all pending reminder IDs
            const pendingIds = [];
            document.querySelectorAll('.reminder-row[data-status="pending"]').forEach(row => {
                const checkbox = row.querySelector('.reminder-check');
                if (checkbox) pendingIds.push(checkbox.value);
            });
            
            if (pendingIds.length === 0) {
                alert('No pending reminders to send');
                return;
            }
            
            // In production, make AJAX request to bulk send endpoint
            alert(`Sending ${pendingIds.length} reminders... (Mock action)`);
            location.reload();
        }

        function retryFailed() {
            if (!confirm('Retry all failed reminders?')) return;
            
            const failedIds = [];
            document.querySelectorAll('.reminder-row[data-status="failed"]').forEach(row => {
                const checkbox = row.querySelector('.reminder-check');
                if (checkbox) failedIds.push(checkbox.value);
            });
            
            if (failedIds.length === 0) {
                alert('No failed reminders to retry');
                return;
            }
            
            alert(`Retrying ${failedIds.length} reminders... (Mock action)`);
            location.reload();
        }

        function cancelReminder(id) {
            if (!confirm('Cancel this reminder?')) return;
            
            // In production, make DELETE request
            alert('Reminder cancelled (Mock action)');
            location.reload();
        }

        // Auto-refresh every 2 minutes to show newly sent reminders
        setTimeout(function() {
            location.reload();
        }, 120000);
    </script>
</body>
</html>