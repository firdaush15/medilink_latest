<!--nurse_reportsDocumentation.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Reports & Documentation - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e1e8ed;
        }

        .header-left h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .header-left p {
            color: #718096;
            font-size: 15px;
        }

        .quick-actions-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .action-btn.primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .action-btn.primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
        }

        .stats-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 32px;
            border-radius: 16px;
            margin-bottom: 32px;
            box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
        }

        .stats-dashboard h3 {
            font-size: 24px;
            margin-bottom: 24px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .stat-box {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stat-label {
            font-size: 13px;
            opacity: 0.9;
        }

        .report-categories {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }

        .category-card {
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }

        .category-card:hover {
            border-color: #667eea;
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .category-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .category-name {
            font-size: 20px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .category-desc {
            color: #718096;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .category-count {
            display: inline-block;
            padding: 6px 12px;
            background: #e8f5e9;
            color: #2e7d32;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .reports-section {
            background: white;
            padding: 28px;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 24px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .section-header h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 8px;
            background: #f8f9fa;
            color: #666;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            font-size: 13px;
            text-decoration: none;
        }

        .filter-tab:hover {
            background: #e9ecef;
        }

        .filter-tab.active {
            background: #667eea;
            color: white;
        }

        .report-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px;
            border: 2px solid #e1e8ed;
            border-radius: 12px;
            margin-bottom: 12px;
            transition: all 0.3s;
        }

        .report-item:hover {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .report-type-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }

        .report-type-icon.incident {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
        }

        .report-type-icon.clinical_note {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .report-type-icon.follow_up {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .report-type-icon.referral {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .report-info {
            flex: 1;
        }

        .report-title {
            font-size: 16px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 6px;
        }

        .report-meta {
            display: flex;
            gap: 16px;
            color: #718096;
            font-size: 13px;
        }

        .report-actions {
            display: flex;
            gap: 8px;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            border: 2px solid #e1e8ed;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-btn:hover {
            border-color: #667eea;
            background: #f8f9fe;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1000;
            overflow-y: auto;
        }

        .modal.active {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 28px;
            border-bottom: 2px solid #e1e8ed;
        }

        .modal-header h2 {
            font-size: 24px;
            font-weight: 700;
            color: #1a202c;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 28px;
            color: #718096;
            cursor: pointer;
            transition: all 0.3s;
        }

        .modal-close:hover {
            color: #1a202c;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e8ed;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            padding: 24px 28px;
            border-top: 2px solid #e1e8ed;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-outline {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state svg {
            opacity: 0.3;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #718096;
            font-size: 14px;
        }

        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .alert-success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #4caf50;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #f44336;
        }

        .priority-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .priority-critical {
            background: #ffebee;
            color: #c62828;
        }

        .priority-major {
            background: #fff3e0;
            color: #f57c00;
        }

        .priority-moderate {
            background: #e3f2fd;
            color: #1976d2;
        }

        .priority-minor {
            background: #f5f5f5;
            color: #757575;
        }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="page-header">
            <div class="header-left">
                <h1>Clinical Notes & Reports</h1>
                <p>Document patient observations and clinic incidents</p>
            </div>
            <div class="quick-actions-bar">
                <button class="action-btn primary" onclick="openReportModal('incident')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    Report Incident
                </button>
                <button class="action-btn primary" onclick="openReportModal('clinical_note')">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    Clinical Note
                </button>
                <button class="action-btn primary" onclick="openReportModal('follow_up')" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    Follow-up
                </button>
                <button class="action-btn primary" onclick="openReportModal('referral')" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Referral
                </button>
            </div>
        </div>

        <div class="stats-dashboard">
            <h3>📊 Your Documentation Summary (This Month)</h3>
            <div class="stats-grid">
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['total_reports'] }}</div>
                    <div class="stat-label">Total Reports</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['incident_reports'] }}</div>
                    <div class="stat-label">Incident Reports</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['clinical_notes'] }}</div>
                    <div class="stat-label">Clinical Notes</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $stats['documentation_rate'] }}%</div>
                    <div class="stat-label">Documentation Rate</div>
                </div>
            </div>
        </div>

        <div class="report-categories">
            @foreach($categories as $key => $category)
            <div class="category-card" onclick="filterReports('{{ $key }}')">
                <div class="category-icon">{{ $category['icon'] }}</div>
                <div class="category-name">{{ $category['name'] }}</div>
                <div class="category-desc">{{ $category['description'] }}</div>
                <span class="category-count">{{ $category['count'] }} this month</span>
            </div>
            @endforeach
        </div>

        <div class="reports-section">
            <div class="section-header">
                <h2>📋 Recent Reports</h2>
                <div class="filter-tabs">
                    <a href="{{ route('nurse.reports-documentation', ['filter' => 'all']) }}" 
                       class="filter-tab {{ $filter == 'all' ? 'active' : '' }}">All</a>
                    <a href="{{ route('nurse.reports-documentation', ['filter' => 'incident']) }}" 
                       class="filter-tab {{ $filter == 'incident' ? 'active' : '' }}">Incidents</a>
                    <a href="{{ route('nurse.reports-documentation', ['filter' => 'clinical_note']) }}" 
                       class="filter-tab {{ $filter == 'clinical_note' ? 'active' : '' }}">Clinical Notes</a>
                    <a href="{{ route('nurse.reports-documentation', ['filter' => 'referral']) }}" 
                       class="filter-tab {{ $filter == 'referral' ? 'active' : '' }}">Referrals</a>
                </div>
            </div>

            @if($recentReports->count() > 0)
                @foreach($recentReports as $report)
                <div class="report-item">
                    <div class="report-type-icon {{ $report->report_type }}">
                        {{ $categories[$report->report_type]['icon'] ?? '📄' }}
                    </div>
                    <div class="report-info">
                        <div class="report-title">
                            {{ ucwords(str_replace('_', ' ', $report->report_type)) }} - {{ $report->patient->user->name }}
                        </div>
                        <div class="report-meta">
                            <span>📅 {{ $report->event_datetime->format('M d, Y - h:i A') }}</span>
                            <span>🆔 {{ $report->report_number }}</span>
                            <span>👤 {{ $report->patient->patient_id }}</span>
                            @if($report->severity)
                            <span class="priority-badge priority-{{ $report->severity }}">
                                {{ ucfirst($report->severity) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="report-actions">
                        <button class="icon-btn" title="View Report" onclick="window.location='{{ route('nurse.reports.show', $report->report_id) }}'">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach

                <div style="margin-top: 24px;">
                    {{ $recentReports->links() }}
                </div>
            @else
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <h3>No Reports Found</h3>
                    <p>No reports available for the selected filter.</p>
                </div>
            @endif
        </div>
    </div>

    <div id="reportModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h2 id="modalTitle">Create Report</h2>
                    <p id="modalDescription" style="font-size: 14px; color: #718096; margin-top: 8px;"></p>
                </div>
                <button class="modal-close" onclick="closeReportModal()">×</button>
            </div>
            <form id="reportForm" method="POST" action="{{ route('nurse.reports.store') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="report_type" id="reportType">
                    
                    <input type="hidden" name="report_category" id="reportCategory">

                    <div class="form-group">
                        <label>Patient *</label>
                        <select name="patient_id" class="form-control" required>
                            <option value="">Select patient...</option>
                            @foreach($patients as $patient)
                            <option value="{{ $patient->patient_id }}">
                                {{ $patient->user->name }} - PT-{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Date & Time *</label>
                        <input type="datetime-local" name="event_datetime" class="form-control" required value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>

                    <div class="form-group" id="severityField">
                        <label>Severity/Priority</label>
                        <select name="severity" class="form-control">
                            <option value="minor">Minor</option>
                            <option value="moderate">Moderate</option>
                            <option value="major">Major</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="description">Description *</label>
                        <textarea name="description" id="description" class="form-control" required placeholder="Describe the incident, observation, or clinical note..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="actions">Actions Taken</label>
                        <textarea name="actions_taken" id="actions" class="form-control" placeholder="What immediate actions were performed?"></textarea>
                    </div>

                    <div class="form-group" id="followupField">
                        <label>Follow-up Required?</label>
                        <select name="followup_required" class="form-control">
                            <option value="no">No</option>
                            <option value="yes">Yes</option>
                        </select>
                    </div>

                    <div class="form-group" id="doctorField">
                        <label>Doctor Notified?</label>
                        <select name="physician_notified" class="form-control">
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Additional Notes</label>
                        <textarea name="additional_notes" class="form-control" placeholder="Any other relevant information..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" onclick="closeReportModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openReportModal(type) {
            document.getElementById('reportType').value = type;
            document.getElementById('reportCategory').value = type;
            
            const titles = {
                'incident': '🚨 Report Clinical Incident',
                'clinical_note': '📋 Create Clinical Note',
                'follow_up': '📞 Create Follow-up Note',
                'referral': '🏥 Create Referral Note'
            };
            
            const descriptions = {
                'incident': 'Document any adverse events, falls, allergic reactions, or safety concerns during patient visit.',
                'clinical_note': 'Record pre-consultation observations, patient concerns, and initial assessments before doctor sees patient.',
                'follow_up': 'Document post-visit follow-up calls, patient check-ins, and ongoing care notes.',
                'referral': 'Document patient referrals to specialists or emergency care facilities.'
            };
            
            document.getElementById('modalTitle').textContent = titles[type] || 'Create Report';
            document.getElementById('modalDescription').textContent = descriptions[type] || '';
            
            // Show/hide and customize fields based on type
            const severityField = document.getElementById('severityField');
            const descriptionLabel = document.querySelector('label[for="description"]');
            const descriptionPlaceholder = document.getElementById('description');
            const actionsLabel = document.querySelector('label[for="actions"]');
            const actionsPlaceholder = document.getElementById('actions');
            const followupField = document.getElementById('followupField');
            const doctorField = document.getElementById('doctorField');
            
            // Reset all fields to visible first
            severityField.style.display = 'block';
            followupField.style.display = 'block';
            doctorField.style.display = 'block';
            
            if (type === 'incident') {
                severityField.style.display = 'block';
                descriptionLabel.textContent = 'What happened? *';
                descriptionPlaceholder.placeholder = 'Describe the incident in detail: what happened, when, where, who was involved...';
                actionsLabel.textContent = 'Immediate Actions Taken *';
                actionsPlaceholder.placeholder = 'What did you do immediately after the incident?';
                followupField.style.display = 'block';
                doctorField.style.display = 'block';
            } else if (type === 'clinical_note') {
                severityField.style.display = 'none';
                descriptionLabel.textContent = 'Patient Observations *';
                descriptionPlaceholder.placeholder = 'Patient complaints, visible symptoms, behavior, initial impressions...';
                actionsLabel.textContent = 'Initial Assessment';
                actionsPlaceholder.placeholder = 'Your preliminary assessment and any immediate care provided...';
                followupField.style.display = 'none';
                doctorField.style.display = 'none';
            } else if (type === 'follow_up') {
                severityField.style.display = 'none';
                descriptionLabel.textContent = 'Follow-up Discussion *';
                descriptionPlaceholder.placeholder = 'What did you discuss with the patient? How are they feeling?';
                actionsLabel.textContent = 'Advice Given';
                actionsPlaceholder.placeholder = 'What recommendations or instructions did you provide?';
                followupField.style.display = 'block';
                doctorField.style.display = 'none';
            } else if (type === 'referral') {
                severityField.style.display = 'none';
                descriptionLabel.textContent = 'Reason for Referral *';
                descriptionPlaceholder.placeholder = 'Why is this patient being referred? What specialist or facility?';
                actionsLabel.textContent = 'Referral Information';
                actionsPlaceholder.placeholder = 'Specialist name, facility, appointment date if known...';
                followupField.style.display = 'block';
                doctorField.style.display = 'block';
            }
            
            document.getElementById('reportModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeReportModal() {
            document.getElementById('reportModal').classList.remove('active');
            document.body.style.overflow = 'auto';
            document.getElementById('reportForm').reset();
        }

        function filterReports(category) {
            window.location.href = '{{ route("nurse.reports-documentation") }}?filter=' + category;
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeReportModal();
        });

        document.getElementById('reportModal').addEventListener('click', function(e) {
            if (e.target === this) closeReportModal();
        });
    </script>
</body>
</html>