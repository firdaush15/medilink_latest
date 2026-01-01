<!--nurse_reportDetail.blade.php-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Details - {{ $report->report_number }}</title>
    @vite(['resources/css/nurse/nurse_sidebar.css'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            color: #2c3e50;
        }

        .main-content {
            margin-left: 280px;
            padding: 32px;
        }

        .report-header {
            background: white;
            padding: 32px;
            border-radius: 16px;
            margin-bottom: 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .report-title-section {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 24px;
        }

        .report-title {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 8px;
        }

        .report-number {
            font-size: 16px;
            color: #667eea;
            font-weight: 600;
        }

        .report-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .badge-critical { background: #ffebee; color: #c62828; }
        .badge-major { background: #fff3e0; color: #f57c00; }
        .badge-moderate { background: #e3f2fd; color: #1976d2; }
        .badge-minor { background: #f5f5f5; color: #757575; }

        .report-meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #e1e8ed;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .meta-icon {
            width: 48px;
            height: 48px;
            background: #f0f4ff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
        }

        .meta-content {
            flex: 1;
        }

        .meta-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }

        .meta-value {
            font-size: 15px;
            color: #1a202c;
            font-weight: 600;
            margin-top: 2px;
        }

        .report-section {
            background: white;
            padding: 28px;
            border-radius: 16px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-content {
            color: #495057;
            line-height: 1.8;
            font-size: 15px;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
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

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .info-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 600;
        }

        .info-badge.success {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .info-badge.warning {
            background: #fff3e0;
            color: #f57c00;
        }

        .info-badge.info {
            background: #e3f2fd;
            color: #1976d2;
        }

        @media print {
            .main-content {
                margin-left: 0;
            }
            .action-buttons {
                display: none;
            }
        }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <div class="report-header">
            <div class="report-title-section">
                <div>
                    <div class="report-number">{{ $report->report_number }}</div>
                    <h1 class="report-title">{{ ucwords(str_replace('_', ' ', $report->report_type)) }} Report</h1>
                </div>
                @if($report->severity && $report->report_type === 'incident')
                <span class="report-badge badge-{{ $report->severity }}">
                    {{ ucfirst($report->severity) }} Priority
                </span>
                @endif
            </div>

            <div class="report-meta-grid">
                <div class="meta-item">
                    <div class="meta-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">Patient</div>
                        <div class="meta-value">{{ $report->patient->user->name }}</div>
                    </div>
                </div>

                <div class="meta-item">
                    <div class="meta-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <polyline points="12 6 12 12 16 14"/>
                        </svg>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">Date & Time</div>
                        <div class="meta-value">{{ $report->event_datetime->format('M d, Y h:i A') }}</div>
                    </div>
                </div>

                <div class="meta-item">
                    <div class="meta-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                            <circle cx="9" cy="7" r="4"/>
                        </svg>
                    </div>
                    <div class="meta-content">
                        <div class="meta-label">Reported By</div>
                        <div class="meta-value">{{ $report->nurse->user->name }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-section">
            <h2 class="section-title">
                📋 Description
            </h2>
            <div class="section-content">
                {{ $report->description }}
            </div>
        </div>

        @if($report->actions_taken)
        <div class="report-section">
            <h2 class="section-title">
                ⚡ Actions Taken
            </h2>
            <div class="section-content">
                {{ $report->actions_taken }}
            </div>
        </div>
        @endif

        <div class="report-section">
            <h2 class="section-title">
                ℹ️ Report Information
            </h2>
            <div class="section-content">
                <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
                    @if($report->followup_required)
                    <span class="info-badge warning">⚠️ Follow-up Required</span>
                    @endif

                    @if($report->physician_notified)
                    <span class="info-badge success">✅ Doctor Notified</span>
                    @else
                    <span class="info-badge info">📋 Doctor Not Yet Notified</span>
                    @endif
                </div>

                @if($report->additional_notes)
                <div style="margin-top: 16px;">
                    <strong>Additional Notes:</strong>
                    <p style="margin-top: 8px;">{{ $report->additional_notes }}</p>
                </div>
                @endif

                <p style="margin-top: 20px; font-size: 13px; color: #718096; border-top: 1px solid #e1e8ed; padding-top: 16px;">
                    📅 Report created: {{ $report->created_at->format('M d, Y h:i A') }}
                </p>
            </div>
        </div>

        <div class="action-buttons">
            <a href="{{ route('nurse.reports-documentation') }}" class="btn btn-outline">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="19" y1="12" x2="5" y2="12"/>
                    <polyline points="12 19 5 12 12 5"/>
                </svg>
                Back to Reports
            </a>
            <button class="btn btn-primary" onclick="window.print()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 6 2 18 2 18 9"/>
                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                    <rect x="6" y="14" width="12" height="8"/>
                </svg>
                Print Report
            </button>
        </div>
    </div>
</body>
</html>