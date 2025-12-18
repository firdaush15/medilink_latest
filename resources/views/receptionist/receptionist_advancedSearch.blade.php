<!--receptionist_advancedSearch.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advanced Patient Search - MediLink</title>
    @vite(['resources/css/receptionist/receptionist_sidebar.css', 'resources/css/receptionist/receptionist_appointments.css'])
    <style>
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .filters-panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .filter-group label {
            font-weight: 600;
            font-size: 14px;
            color: #333;
        }
        .filter-group input,
        .filter-group select {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .quick-filters {
            display: flex;
            gap: 10px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .quick-filter-btn {
            padding: 8px 16px;
            border: 2px solid #ddd;
            border-radius: 20px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        .quick-filter-btn:hover {
            border-color: #2196F3;
            background: #e3f2fd;
        }
        .quick-filter-btn.active {
            border-color: #2196F3;
            background: #2196F3;
            color: white;
        }
        .search-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .btn-search {
            flex: 1;
            padding: 12px;
            background: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }
        .btn-search:hover {
            background: #1976D2;
        }
        .btn-reset {
            padding: 12px 30px;
            background: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }
        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .results-count {
            font-size: 18px;
            color: #666;
        }
        .export-btn {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .patient-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 20px;
            align-items: center;
        }
        .patient-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        .patient-details h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .patient-meta {
            display: flex;
            gap: 20px;
            color: #666;
            font-size: 14px;
        }
        .patient-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .flag-badge {
            background: #ff9800;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .patient-actions {
            display: flex;
            gap: 10px;
        }
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-view {
            background: #2196F3;
            color: white;
        }
        .btn-book {
            background: #4CAF50;
            color: white;
        }
        .btn-history {
            background: #ff9800;
            color: white;
        }
        .no-results {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
        }
        .no-results-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    @include('receptionist.sidebar.receptionist_sidebar')

    <div class="main-content">
        <div class="search-header">
            <h1>🔍 Advanced Patient Search</h1>
            <p>Search and filter patients with powerful criteria</p>
        </div>

        <!-- Filters Panel -->
        <div class="filters-panel">
            <h2 style="margin-bottom: 15px;">Search Filters</h2>
            
            <!-- Quick Filters -->
            <div class="quick-filters">
                <button type="button" class="quick-filter-btn" onclick="applyQuickFilter('today')">
                    📅 Today's Patients
                </button>
                <button type="button" class="quick-filter-btn" onclick="applyQuickFilter('flagged')">
                    ⚠️ Flagged Patients
                </button>
                <button type="button" class="quick-filter-btn" onclick="applyQuickFilter('frequent')">
                    ⭐ Frequent Visitors
                </button>
                <button type="button" class="quick-filter-btn" onclick="applyQuickFilter('no-shows')">
                    🚫 Multiple No-Shows
                </button>
                <button type="button" class="quick-filter-btn" onclick="applyQuickFilter('new')">
                    🆕 New Patients (30 days)
                </button>
            </div>

            <!-- Advanced Filters Form -->
            <form method="GET" action="{{ route('receptionist.search.advanced') }}" id="search-form">
                <div class="filter-grid">
                    <!-- Text Search -->
                    <div class="filter-group">
                        <label>🔎 Search Query</label>
                        <input type="text" 
                               name="search" 
                               placeholder="Name, ID, Phone, Email..." 
                               value="{{ request('search') }}">
                    </div>

                    <!-- Gender Filter -->
                    <div class="filter-group">
                        <label>⚧ Gender</label>
                        <select name="gender">
                            <option value="">All Genders</option>
                            <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ request('gender') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Age Range -->
                    <div class="filter-group">
                        <label>🎂 Min Age</label>
                        <input type="number" name="age_min" placeholder="e.g., 18" value="{{ request('age_min') }}" min="0" max="120">
                    </div>

                    <div class="filter-group">
                        <label>🎂 Max Age</label>
                        <input type="number" name="age_max" placeholder="e.g., 65" value="{{ request('age_max') }}" min="0" max="120">
                    </div>

                    <!-- Flagged Status -->
                    <div class="filter-group">
                        <label>⚠️ Flagged Status</label>
                        <select name="flagged">
                            <option value="">All Patients</option>
                            <option value="1" {{ request('flagged') == '1' ? 'selected' : '' }}>Flagged Only</option>
                            <option value="0" {{ request('flagged') == '0' ? 'selected' : '' }}>Not Flagged</option>
                        </select>
                    </div>

                    <!-- No-Shows Minimum -->
                    <div class="filter-group">
                        <label>🚫 Min No-Shows</label>
                        <input type="number" name="no_shows_min" placeholder="e.g., 3" value="{{ request('no_shows_min') }}" min="0">
                    </div>

                    <!-- Registration Date Range -->
                    <div class="filter-group">
                        <label>📅 Registered After</label>
                        <input type="date" name="registered_after" value="{{ request('registered_after') }}">
                    </div>

                    <div class="filter-group">
                        <label>📅 Registered Before</label>
                        <input type="date" name="registered_before" value="{{ request('registered_before') }}">
                    </div>
                </div>

                <div class="search-actions">
                    <button type="submit" class="btn-search">🔍 Search Patients</button>
                    <button type="button" class="btn-reset" onclick="resetFilters()">↺ Reset Filters</button>
                </div>
            </form>
        </div>

        <!-- Results Section -->
        @if(isset($results))
        <div class="results-header">
            <div class="results-count">
                <strong>{{ $results->total() }}</strong> patient(s) found
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="export-btn" onclick="exportResults()">📥 Export to Excel</button>
            </div>
        </div>

        @if($results->count() > 0)
            @foreach($results as $patient)
            <div class="patient-card">
                <div class="patient-avatar">
                    {{ strtoupper(substr($patient->user->name, 0, 1)) }}
                </div>
                
                <div class="patient-details">
                    <h3>
                        {{ $patient->user->name }}
                        @if($patient->is_flagged)
                            <span class="flag-badge">⚠️ FLAGGED</span>
                        @endif
                    </h3>
                    <div class="patient-meta">
                        <span>🆔 P{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}</span>
                        <span>📞 {{ $patient->phone_number }}</span>
                        <span>🎂 {{ $patient->age }} years</span>
                        <span>⚧ {{ $patient->gender }}</span>
                    </div>
                    <div class="patient-meta" style="margin-top: 8px; font-size: 13px;">
                        <span>📧 {{ $patient->user->email }}</span>
                        @if($patient->last_visit_date)
                            <span>🏥 Last visit: {{ \Carbon\Carbon::parse($patient->last_visit_date)->diffForHumans() }}</span>
                        @endif
                        @if($patient->no_show_count > 0)
                            <span style="color: #ff9800;">🚫 {{ $patient->no_show_count }} no-show(s)</span>
                        @endif
                    </div>
                </div>
                
                <div class="patient-actions">
                    <button class="action-btn btn-history" onclick="openPatientHistory({{ $patient->patient_id }})">
                        📋 History
                    </button>
                    <a href="{{ route('receptionist.appointments.create', ['patient_id' => $patient->patient_id]) }}" class="action-btn btn-book">
                        📅 Book
                    </a>
                </div>
            </div>
            @endforeach

            <!-- Pagination -->
            <div style="margin-top: 30px;">
                {{ $results->links() }}
            </div>
        @else
            <div class="no-results">
                <div class="no-results-icon">🔍</div>
                <h3>No Patients Found</h3>
                <p>Try adjusting your search filters or criteria</p>
            </div>
        @endif
        @endif
    </div>

<!-- ========================================
     PATIENT VISIT HISTORY MODAL (INLINE)
     ======================================== -->
<div id="patient-history-modal" class="modal-overlay" style="display: none;">
    <div class="modal-container">
        <div class="modal-header">
            <h2>📋 Patient Visit History</h2>
            <span class="modal-close" onclick="closePatientHistory()">&times;</span>
        </div>
        
        <div id="patient-history-content" class="modal-body">
            <div class="loading-spinner">Loading patient history...</div>
        </div>
    </div>
</div>

<style>
/* Patient History Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
}

.modal-container {
    background-color: #ffffff;
    margin: 3% auto;
    padding: 0;
    border-radius: 12px;
    width: 90%;
    max-width: 950px;
    max-height: 85vh;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from { transform: translateY(-50px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 25px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 22px;
}

.modal-close {
    color: white;
    font-size: 32px;
    font-weight: bold;
    cursor: pointer;
    line-height: 1;
    transition: all 0.3s;
}

.modal-close:hover {
    transform: rotate(90deg);
    opacity: 0.8;
}

.modal-body {
    padding: 30px;
    max-height: calc(85vh - 90px);
    overflow-y: auto;
}

.loading-spinner {
    text-align: center;
    padding: 60px 20px;
    color: #666;
    font-size: 16px;
}

.loading-spinner::before {
    content: "⏳";
    font-size: 48px;
    display: block;
    margin-bottom: 15px;
    animation: spin 2s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.history-header {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 25px;
    border-radius: 10px;
    margin-bottom: 25px;
}

.history-header h3 {
    margin: 0 0 10px 0;
    font-size: 24px;
    color: #333;
}

.history-header p {
    margin: 5px 0;
    color: #666;
    font-size: 14px;
}

.history-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.stat-box {
    text-align: center;
    padding: 20px 15px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s;
}

.stat-box:hover {
    transform: translateY(-5px);
}

.stat-number {
    font-size: 28px;
    font-weight: bold;
    color: #2196F3;
    margin-bottom: 8px;
}

.stat-label {
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.flag-warning {
    background: #fff3cd;
    border: 2px solid #ffc107;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.flag-warning strong {
    color: #856404;
}

.visit-timeline {
    margin-top: 25px;
}

.visit-timeline h4 {
    font-size: 18px;
    margin-bottom: 20px;
    color: #333;
}

.visit-item {
    border-left: 4px solid #2196F3;
    padding-left: 25px;
    margin-bottom: 25px;
    position: relative;
    padding-bottom: 10px;
}

.visit-item::before {
    content: '';
    width: 16px;
    height: 16px;
    background: #2196F3;
    border: 3px solid white;
    border-radius: 50%;
    position: absolute;
    left: -10px;
    top: 5px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.visit-date {
    font-weight: bold;
    font-size: 16px;
    color: #333;
    margin-bottom: 5px;
}

.visit-doctor {
    color: #666;
    font-size: 14px;
    margin-bottom: 10px;
}

.visit-details {
    margin-top: 10px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 3px solid #2196F3;
}

.visit-details p {
    margin: 8px 0;
    font-size: 14px;
    color: #555;
}

.visit-details strong {
    color: #333;
}

.badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
}

.badge.completed {
    background: #d4edda;
    color: #155724;
}

.badge.cancelled {
    background: #f8d7da;
    color: #721c24;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #999;
}

.empty-state-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.modal-body::-webkit-scrollbar {
    width: 8px;
}

.modal-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.modal-body::-webkit-scrollbar-thumb:hover {
    background: #555;
}

@media (max-width: 768px) {
    .modal-container {
        width: 95%;
        margin: 10px auto;
    }
    
    .history-stats {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
// ========================================
// PATIENT HISTORY MODAL FUNCTIONS
// ========================================
function openPatientHistory(patientId) {
    const modal = document.getElementById('patient-history-modal');
    const content = document.getElementById('patient-history-content');
    
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    content.innerHTML = '<div class="loading-spinner">Loading patient history...</div>';
    
    fetch(`/receptionist/patients/${patientId}/history`)
        .then(response => {
            if (!response.ok) throw new Error('Failed to fetch history');
            return response.json();
        })
        .then(data => {
            content.innerHTML = renderPatientHistory(data);
        })
        .catch(error => {
            content.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">⚠️</div>
                    <h3>Failed to Load Patient History</h3>
                    <p style="color: #666;">${error.message}</p>
                </div>
            `;
            console.error('Error loading patient history:', error);
        });
}

function closePatientHistory() {
    const modal = document.getElementById('patient-history-modal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function renderPatientHistory(data) {
    let html = `
        <div class="history-header">
            <h3>${data.patient.name}</h3>
            <p><strong>Patient ID:</strong> P${String(data.patient.id).padStart(4, '0')}</p>
            <p><strong>Phone:</strong> ${data.patient.phone} | <strong>Email:</strong> ${data.patient.email}</p>
            
            ${data.patient.is_flagged ? `
                <div class="flag-warning">
                    <span style="font-size: 24px;">⚠️</span>
                    <div>
                        <strong>Flagged Patient</strong>
                        <div style="font-size: 13px; margin-top: 3px;">${data.patient.flag_reason || 'Reason not specified'}</div>
                    </div>
                </div>
            ` : ''}
            
            <div class="history-stats">
                <div class="stat-box">
                    <div class="stat-number">${data.stats.total_visits}</div>
                    <div class="stat-label">Total Visits</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">${data.stats.no_shows}</div>
                    <div class="stat-label">No-Shows</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">${data.stats.late_arrivals}</div>
                    <div class="stat-label">Late Arrivals</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number">RM ${parseFloat(data.stats.total_paid).toFixed(2)}</div>
                    <div class="stat-label">Total Paid</div>
                </div>
            </div>
        </div>
        
        <div class="visit-timeline">
            <h4>📅 Recent Visits (Last 10)</h4>
    `;
    
    if (data.visits && data.visits.length > 0) {
        data.visits.forEach(visit => {
            html += `
                <div class="visit-item">
                    <div class="visit-date">${visit.date}</div>
                    <div class="visit-doctor">👨‍⚕️ Dr. ${visit.doctor} - ${visit.specialization}</div>
                    <div class="visit-details">
                        <p><strong>Status:</strong> <span class="badge ${visit.status.toLowerCase()}">${visit.status}</span></p>
                        <p><strong>Reason:</strong> ${visit.reason || 'General consultation'}</p>
                        ${visit.payment_collected ? `<p><strong>💳 Payment:</strong> RM ${parseFloat(visit.payment_amount).toFixed(2)}</p>` : '<p><strong>💳 Payment:</strong> Not collected</p>'}
                    </div>
                </div>
            `;
        });
    } else {
        html += `
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No previous visits found for this patient.</p>
            </div>
        `;
    }
    
    html += '</div>';
    return html;
}

window.addEventListener('click', function(event) {
    const modal = document.getElementById('patient-history-modal');
    if (event.target === modal) {
        closePatientHistory();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closePatientHistory();
    }
});
</script>

    <script>
        function applyQuickFilter(type) {
            const form = document.getElementById('search-form');
            
            // Reset form
            form.reset();
            
            // Apply specific filter
            switch(type) {
                case 'today':
                    // This would need backend logic
                    alert('Filter: Show patients with appointments today');
                    break;
                case 'flagged':
                    form.querySelector('[name="flagged"]').value = '1';
                    break;
                case 'frequent':
                    // This would need backend logic for visit count
                    alert('Filter: Show patients with 5+ visits');
                    break;
                case 'no-shows':
                    form.querySelector('[name="no_shows_min"]').value = '3';
                    break;
                case 'new':
                    const thirtyDaysAgo = new Date();
                    thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                    form.querySelector('[name="registered_after"]').value = thirtyDaysAgo.toISOString().split('T')[0];
                    break;
            }
            
            // Highlight active button
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
            
            // Submit form
            form.submit();
        }

        function resetFilters() {
            document.getElementById('search-form').reset();
            document.querySelectorAll('.quick-filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
        }

        function exportResults() {
            const form = document.getElementById('search-form');
            const exportForm = form.cloneNode(true);
            exportForm.action = "{{ route('receptionist.search.advanced') }}/export";
            exportForm.method = "POST";
            exportForm.style.display = 'none';
            
            // Add CSRF token
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            exportForm.appendChild(csrf);
            
            document.body.appendChild(exportForm);
            exportForm.submit();
            document.body.removeChild(exportForm);
        }

        // Auto-submit on quick filter click
        document.querySelectorAll('.quick-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Visual feedback
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>