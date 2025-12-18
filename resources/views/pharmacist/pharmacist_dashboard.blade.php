<!--pharmacist_dashboard.blade.php -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pharmacist Dashboard - MediLink</title>
    @vite(['resources/css/pharmacist/pharmacist_sidebar.css', 'resources/css/pharmacist/pharmacist_dashboard.css'])
</head>

<body>
    @include('pharmacist.sidebar.pharmacist_sidebar')

    <div class="main-content">
        <div class="top-bar">
            <h1>Pharmacist Dashboard</h1>
            <div class="user-info">
                <span>Welcome, {{ auth()->user()->name }}</span>
                <img src="{{ auth()->user()->profile_photo ?? asset('assets/default-avatar.png') }}" alt="Profile" class="profile-pic">
            </div>
        </div>

        <!-- Critical Alerts Section -->
        @if($criticalAlerts->count() > 0)
        <div class="critical-alerts-banner">
            <div class="alert-icon">⚠️</div>
            <div class="alert-content">
                <strong>{{ $criticalAlerts->count() }} Critical Alert(s) Require Immediate Attention</strong>
                <div class="alert-list">
                    @foreach($criticalAlerts as $alert)
                    <div class="alert-item">
                        <span>{{ $alert->alert_title }}</span>
                        <a href="{{ $alert->action_url }}" class="view-btn">View</a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        <!-- Prescription Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card pending">
                <div class="stat-icon">📋</div>
                <div class="stat-info">
                    <h3>{{ $pendingPrescriptions }}</h3>
                    <p>Pending Verification</p>
                </div>
                <a href="{{ route('pharmacist.prescriptions') }}?status=pending" class="stat-link">View All →</a>
            </div>

            <div class="stat-card verified">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>{{ $verifiedPrescriptions }}</h3>
                    <p>Ready to Dispense</p>
                </div>
                <a href="{{ route('pharmacist.prescriptions') }}?status=verified" class="stat-link">View All →</a>
            </div>

            <div class="stat-card dispensed">
                <div class="stat-icon">💊</div>
                <div class="stat-info">
                    <h3>{{ $dispensedToday }}</h3>
                    <p>Dispensed Today</p>
                </div>
                <a href="{{ route('pharmacist.prescriptions') }}?date=today" class="stat-link">View All →</a>
            </div>

            <div class="stat-card revenue">
                <div class="stat-icon">💰</div>
                <div class="stat-info">
                    <h3>RM {{ number_format($todayRevenue, 2) }}</h3>
                    <p>Today's Revenue</p>
                </div>
                <a href="{{ route('pharmacist.reports') }}" class="stat-link">View Report →</a>
            </div>
        </div>

        <!-- Inventory Status Section -->
        <div class="section-title">
            <span>📦 Inventory Status</span>
            <a href="{{ route('pharmacist.inventory') }}" class="section-link">View Full Inventory →</a>
        </div>

        <div class="stats-grid inventory-stats">
            <div class="stat-card low-stock">
                <div class="stat-icon">⚠️</div>
                <div class="stat-info">
                    <h3>{{ $lowStockCount }}</h3>
                    <p>Low Stock Items</p>
                    <span class="status-indicator warning">Reorder Soon</span>
                </div>
                @if($lowStockCount > 0)
                <a href="{{ route('pharmacist.inventory') }}?status=low" class="stat-link">View Items →</a>
                @endif
            </div>

            <div class="stat-card out-stock">
                <div class="stat-icon">❌</div>
                <div class="stat-info">
                    <h3>{{ $outOfStockCount }}</h3>
                    <p>Out of Stock</p>
                    <span class="status-indicator critical">URGENT: Restock Now</span>
                </div>
                @if($outOfStockCount > 0)
                <a href="{{ route('pharmacist.inventory') }}?status=Out of Stock" class="stat-link">View Items →</a>
                @endif
            </div>

            <div class="stat-card expiring">
                <div class="stat-icon">⏰</div>
                <div class="stat-info">
                    <h3>{{ $expiringCount }}</h3>
                    <p>Expiring Soon</p>
                    <span class="status-indicator warning">Within 30 Days</span>
                </div>
                @if($expiringCount > 0)
                <a href="{{ route('pharmacist.inventory') }}?status=expiring" class="stat-link">View Items →</a>
                @endif
            </div>

            <div class="stat-card expired">
                <div class="stat-icon">🚫</div>
                <div class="stat-info">
                    <h3>{{ $expiredCount }}</h3>
                    <p>Expired Items</p>
                    <span class="status-indicator danger">Remove from Stock</span>
                </div>
                @if($expiredCount > 0)
                <a href="{{ route('pharmacist.inventory') }}?status=Expired" class="stat-link">View Items →</a>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section-title">Quick Actions</div>
        <div class="quick-actions">
            <a href="{{ route('pharmacist.prescriptions') }}" class="action-card">
                <div class="action-icon">🔍</div>
                <div class="action-title">Verify Prescription</div>
                <div class="action-desc">Check and verify pending prescriptions</div>
            </a>

            <a href="{{ route('pharmacist.prescriptions') }}?status=verified" class="action-card">
                <div class="action-icon">💊</div>
                <div class="action-title">Dispense Medication</div>
                <div class="action-desc">Dispense verified prescriptions</div>
            </a>

            <a href="{{ route('pharmacist.inventory.create') }}" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-title">Add Medicine</div>
                <div class="action-desc">Add new medicine to inventory</div>
            </a>

            <a href="{{ route('pharmacist.inventory') }}" class="action-card">
                <div class="action-icon">📊</div>
                <div class="action-title">Manage Stock</div>
                <div class="action-desc">Update inventory levels</div>
            </a>
        </div>

        <!-- Recent Prescriptions -->
        <div class="section-title">Recent Prescriptions Requiring Action</div>
        <div class="prescriptions-table">
            @if($recentPrescriptions->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient Name</th>
                        <th>Doctor</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentPrescriptions as $dispensing)
                    <tr>
                        <td>#{{ $dispensing->prescription_id }}</td>
                        <td>{{ $dispensing->patient->user->name }}</td>
                        <td>Dr. {{ $dispensing->prescription->doctor->user->name }}</td>
                        <td>{{ $dispensing->prescription->prescribed_date->format('M d, Y') }}</td>
                        <td>
                            <span class="status-badge {{ strtolower($dispensing->verification_status) }}">
                                {{ $dispensing->verification_status }}
                            </span>
                        </td>
                        <td>
                            @if($dispensing->prescription->items->contains(function($item) {
                            return strpos(strtolower($item->medicine_name), 'urgent') !== false;
                            }))
                            <span class="priority-badge high">High</span>
                            @else
                            <span class="priority-badge normal">Normal</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pharmacist.prescriptions.show', $dispensing->prescription_id) }}" class="action-btn">
                                @if($dispensing->verification_status == 'Pending')
                                Verify
                                @elseif($dispensing->verification_status == 'Verified')
                                Dispense
                                @else
                                View
                                @endif
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <div class="empty-icon">✨</div>
                <p>All caught up! No pending prescriptions at the moment.</p>
            </div>
            @endif
        </div>

        <!-- Inventory Alerts -->
        <div class="section-title">Inventory Alerts</div>
        <div class="inventory-alerts">
            <div class="alert-grid">
                @forelse($inventoryAlerts as $alert)
                <div class="inventory-alert-card {{ strtolower(str_replace(' ', '-', $alert->alert_type)) }}">
                    <div class="alert-header">
                        <span class="alert-type">{{ $alert->alert_type }}</span>
                        <span class="alert-priority {{ strtolower($alert->priority) }}">{{ $alert->priority }}</span>
                    </div>
                    <div class="alert-body">
                        <h4>{{ $alert->medicine->medicine_name }}</h4>
                        <p>{{ $alert->alert_message }}</p>
                    </div>
                    <div class="alert-footer">
                        @if($alert->action_url)
                        <a href="{{ $alert->action_url }}" class="alert-action">Take Action</a>
                        @endif
                        <button onclick="markAlertRead({{ $alert->alert_id }})" class="mark-read-btn">Mark Read</button>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <div class="empty-icon">✅</div>
                    <p>No inventory alerts. Everything is in good stock!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <script>
        function markAlertRead(alertId) {
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
                        location.reload();
                    }
                });
        }
    </script>
</body>

</html>