<!--nurse_vitalSignsTrendsAnalytics.blade.php - Improved UX Version-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Vital Signs Analytics - MediLink</title>
    @vite(['resources/css/nurse/nurse_sidebar.css'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; color: #2c3e50; }
        .main-content { margin-left: 280px; padding: 32px; min-height: 100vh; }
        
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; padding-bottom: 24px; border-bottom: 2px solid #e1e8ed; }
        .header-left h1 { font-size: 32px; font-weight: 700; color: #1a202c; margin-bottom: 8px; }
        .header-left p { color: #718096; font-size: 15px; }
        
        .patient-selector { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .patient-selector-grid { display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 16px; align-items: end; }
        
        .form-group label { display: block; font-weight: 600; color: #2c3e50; margin-bottom: 8px; font-size: 14px; }
        .form-control { width: 100%; padding: 12px 16px; border: 2px solid #e1e8ed; border-radius: 10px; font-size: 14px; transition: all 0.3s; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        .btn { padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s; border: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4); }
        
        .patient-info-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 28px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3); }
        .patient-name { font-size: 28px; font-weight: 700; margin-bottom: 8px; }
        .patient-details { display: flex; gap: 24px; opacity: 0.95; font-size: 14px; }
        
        .alert-badge { padding: 8px 16px; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); border-radius: 20px; font-size: 14px; font-weight: 600; }
        .alert-badge.critical { background: #f44336; }
        
        .quick-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        .stat-box { background: white; padding: 20px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); text-align: center; transition: all 0.3s; }
        .stat-box:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,0.12); }
        .stat-icon { font-size: 32px; margin-bottom: 12px; }
        .stat-value { font-size: 28px; font-weight: 700; color: #1a202c; margin-bottom: 4px; }
        .stat-value.normal { color: #4caf50; }
        .stat-value.warning { color: #ff9800; }
        .stat-value.critical { color: #f44336; }
        .stat-label { color: #718096; font-size: 13px; }
        .stat-trend { font-size: 12px; margin-top: 8px; font-weight: 600; }
        .stat-trend.up { color: #f44336; }
        .stat-trend.down { color: #4caf50; }
        .stat-trend.stable { color: #2196f3; }
        
        .ai-insights { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 28px; border-radius: 16px; margin-bottom: 24px; box-shadow: 0 8px 24px rgba(245, 87, 108, 0.3); }
        .ai-insights h3 { display: flex; align-items: center; gap: 12px; font-size: 20px; margin-bottom: 20px; }
        .insight-item { background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 16px; border-radius: 12px; margin-bottom: 12px; border-left: 4px solid white; }
        .insight-item:last-child { margin-bottom: 0; }
        .insight-item.warning { border-left-color: #ffc107; }
        .insight-item.critical { border-left-color: #f44336; background: rgba(244, 67, 54, 0.2); }
        
        .chart-container { background: white; padding: 28px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .chart-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .chart-header h3 { font-size: 18px; font-weight: 700; color: #1a202c; }
        
        .comparison-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
        .comparison-card { background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .comparison-header { font-size: 14px; color: #718096; margin-bottom: 16px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .comparison-values { display: flex; justify-content: space-between; align-items: center; }
        .comparison-value { text-align: center; }
        .comparison-value-label { font-size: 11px; color: #718096; margin-bottom: 4px; }
        .comparison-value-number { font-size: 24px; font-weight: 700; color: #1a202c; }
        
        .vitals-table-container { background: white; padding: 28px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 24px; }
        .vitals-table { width: 100%; border-collapse: collapse; }
        .vitals-table thead { background: #f8f9fa; }
        .vitals-table th { padding: 16px; text-align: left; font-weight: 700; color: #2c3e50; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px; }
        .vitals-table td { padding: 16px; border-top: 1px solid #e1e8ed; font-size: 14px; color: #495057; }
        .vitals-table tr:hover { background: #f8f9fa; }
        .vital-value-cell { font-weight: 600; }
        .vital-value-cell.normal { color: #4caf50; }
        .vital-value-cell.warning { color: #ff9800; }
        .vital-value-cell.critical { color: #f44336; background: #ffebee; padding: 4px 8px; border-radius: 6px; }
        
        .time-badge { display: inline-block; padding: 4px 12px; background: #e3f2fd; color: #1976d2; border-radius: 12px; font-size: 12px; font-weight: 600; }
        
        .empty-state { text-align: center; padding: 60px 20px; background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .empty-state svg { opacity: 0.3; margin-bottom: 20px; }
        .empty-state h3 { font-size: 20px; color: #1a202c; margin-bottom: 8px; }
        .empty-state p { color: #718096; font-size: 14px; }

        /* ✅ NEW: Patient card hover styles */
        .patient-card { display: block; padding: 20px; border: 2px solid #e1e8ed; border-radius: 12px; text-decoration: none; transition: all 0.3s; }
        .patient-card:hover { border-color: #667eea; background: #f8f9fe; transform: translateY(-2px); }

        .doctor-badge {
        display: inline-block;
        padding: 4px 10px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .history-context-banner {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 16px 24px;
        border-radius: 12px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .appointment-history-card {
        background: #f8f9fa;
        padding: 12px;
        border-radius: 8px;
        margin-bottom: 8px;
        border-left: 3px solid #667eea;
    }
    </style>
</head>
<body>
    @include('nurse.sidebar.nurse_sidebar')

    <div class="main-content">
        <div class="page-header">
            <div class="header-left">
                <h1>Vital Signs Trends & Analytics</h1>
                <p>Advanced monitoring and predictive insights for patient vitals</p>
            </div>
        </div>

        <!-- Patient Selector -->
        <div class="patient-selector">
            <form method="GET" action="{{ route('nurse.vitals-analytics') }}">
                <div class="patient-selector-grid">
                    <div class="form-group">
                        <label>Select Patient</label>
                        <select name="patient_id" class="form-control" onchange="this.form.submit()">
                            <option value="">Choose a patient...</option>
                            @foreach($patientsWithVitals as $p)
                            <option value="{{ $p->patient_id }}" {{ $selectedPatientId == $p->patient_id ? 'selected' : '' }}>
                                {{ $p->user->name }} (PT-{{ str_pad($p->patient_id, 4, '0', STR_PAD_LEFT) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Time Range</label>
                        <select name="time_range" class="form-control" {{ !$selectedPatientId ? 'disabled' : '' }}>
                            <option value="24h" {{ $timeRange == '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                            <option value="48h" {{ $timeRange == '48h' ? 'selected' : '' }}>Last 48 Hours</option>
                            <option value="7d" {{ $timeRange == '7d' ? 'selected' : '' }}>Last 7 Days</option>
                            <option value="30d" {{ $timeRange == '30d' ? 'selected' : '' }}>Last 30 Days</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Vital Type</label>
                        <select name="vital_type" class="form-control" {{ !$selectedPatientId ? 'disabled' : '' }}>
                            <option value="all" {{ $vitalType == 'all' ? 'selected' : '' }}>All Vitals</option>
                            <option value="temperature" {{ $vitalType == 'temperature' ? 'selected' : '' }}>Temperature</option>
                            <option value="bp" {{ $vitalType == 'bp' ? 'selected' : '' }}>Blood Pressure</option>
                            <option value="hr" {{ $vitalType == 'hr' ? 'selected' : '' }}>Heart Rate</option>
                            <option value="spo2" {{ $vitalType == 'spo2' ? 'selected' : '' }}>Oxygen Saturation</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" {{ !$selectedPatientId ? 'disabled' : '' }}>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"/>
                            <path d="m21 21-4.35-4.35"/>
                        </svg>
                        Analyze
                    </button>
                </div>
            </form>
        </div>

        @if(!$selectedPatientId)
            <!-- ✅ NEW: Overview Dashboard when no patient selected -->
            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 24px;">
                <div class="stat-box" style="padding: 32px;">
                    <div class="stat-icon">👥</div>
                    <div class="stat-value">{{ $overviewStats['total_patients'] }}</div>
                    <div class="stat-label">Patients with Recent Vitals</div>
                    <p style="font-size: 12px; color: #718096; margin-top: 12px;">Last 30 days</p>
                </div>
                <div class="stat-box" style="padding: 32px;">
                    <div class="stat-icon">🚨</div>
                    <div class="stat-value critical">{{ $overviewStats['critical_patients'] }}</div>
                    <div class="stat-label">Critical Vitals (24h)</div>
                    <p style="font-size: 12px; color: #718096; margin-top: 12px;">Requires attention</p>
                </div>
                <div class="stat-box" style="padding: 32px;">
                    <div class="stat-icon">📊</div>
                    <div class="stat-value normal">{{ $overviewStats['recent_readings'] }}</div>
                    <div class="stat-label">Readings Today</div>
                    <p style="font-size: 12px; color: #718096; margin-top: 12px;">Recorded vitals</p>
                </div>
            </div>

            <!-- Welcome Message with Instructions -->
            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 48px; border-radius: 16px; text-align: center; margin-bottom: 24px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin: 0 auto 20px;">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <h2 style="font-size: 28px; margin-bottom: 16px;">Welcome to Vital Signs Analytics</h2>
                <p style="font-size: 16px; opacity: 0.95; max-width: 600px; margin: 0 auto 24px;">
                    Select a patient from the dropdown above to view detailed vital signs trends, AI-powered insights, and comprehensive analytics.
                </p>
                <div style="display: flex; gap: 16px; justify-content: center; font-size: 14px; margin-top: 24px;">
                    <div style="background: rgba(255,255,255,0.15); padding: 12px 20px; border-radius: 8px;">
                        📈 Real-time trending
                    </div>
                    <div style="background: rgba(255,255,255,0.15); padding: 12px 20px; border-radius: 8px;">
                        🤖 AI-powered insights
                    </div>
                    <div style="background: rgba(255,255,255,0.15); padding: 12px 20px; border-radius: 8px;">
                        📊 Historical comparisons
                    </div>
                </div>
            </div>

            <!-- Recent Patients Quick Access -->
            @if($patientsWithVitals->count() > 0)
            <div style="background: white; padding: 28px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
                <h3 style="font-size: 18px; font-weight: 700; margin-bottom: 20px; color: #1a202c;">
                    🔍 Quick Access - Recent Patients
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
                    @foreach($patientsWithVitals->take(6) as $p)
                    <a href="{{ route('nurse.vitals-analytics', ['patient_id' => $p->patient_id]) }}" class="patient-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
                            <div style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 16px;">
                                {{ strtoupper(substr($p->user->name, 0, 1)) }}
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 700; color: #1a202c; font-size: 15px;">{{ $p->user->name }}</div>
                                <div style="font-size: 13px; color: #718096;">PT-{{ str_pad($p->patient_id, 4, '0', STR_PAD_LEFT) }}</div>
                            </div>
                        </div>
                        <div style="font-size: 12px; color: #667eea; font-weight: 600;">
                            View Analytics →
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

        @elseif($patient && $vitalRecords->count() > 0)
    <!-- Patient Info Card -->
    <div class="patient-info-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="patient-name">{{ $patient->user->name }}</div>
                <div class="patient-details">
                    <span>👤 {{ $patient->gender }}, {{ $patient->age }} years</span>
                    <span>🆔 PT-{{ str_pad($patient->patient_id, 4, '0', STR_PAD_LEFT) }}</span>
                    @if($patient->appointments->first())
                    <span>📅 Last visit: {{ $patient->appointments->first()->appointment_date->format('M d, Y') }}</span>
                    @endif
                </div>
            </div>
            @if($criticalReadingsCount > 0)
            <div class="alert-badge critical">
                ⚠️ {{ $criticalReadingsCount }} Critical Reading{{ $criticalReadingsCount > 1 ? 's' : '' }}
            </div>
            @endif
        </div>
    </div>

    <!-- ✅ NEW: Cross-Doctor History Context Banner -->
    @if($patient->appointments->count() > 1)
    <div class="history-context-banner">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        <div>
            <strong>📊 Complete Medical History Visible</strong>
            <p style="margin: 0; font-size: 13px; opacity: 0.9;">
                This patient has seen multiple doctors. You're viewing complete vital history from all visits for continuity of care.
            </p>
        </div>
    </div>
    @endif

    <!-- ✅ NEW: Appointment History Summary -->
    @if($patient->appointments->count() > 0)
    <div style="background: white; padding: 24px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); margin-bottom: 24px;">
        <h3 style="font-size: 16px; font-weight: 700; margin-bottom: 16px; color: #1a202c;">
            📅 Recent Appointment History (Last 30 Days)
        </h3>
        @foreach($patient->appointments->take(5) as $appt)
        <div class="appointment-history-card">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong>{{ $appt->appointment_date->format('M d, Y') }}</strong> at {{ $appt->appointment_time->format('h:i A') }}
                    <span class="doctor-badge">👨‍⚕️ Dr. {{ $appt->doctor->user->name }}</span>
                </div>
                <div style="font-size: 12px; color: #718096;">
                    {{ $appt->getCurrentStageDisplay() }}
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- ✅ Rest of content continues below (AI insights, quick stats, etc.) -->

            <!-- AI Insights -->
            @if(count($aiInsights) > 0)
            <div class="ai-insights">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                        <path d="M2 17l10 5 10-5"/>
                        <path d="M2 12l10 5 10-5"/>
                    </svg>
                    AI-Powered Clinical Insights
                </h3>
                @foreach($aiInsights as $insight)
                <div class="insight-item {{ $insight['type'] }}">
                    <strong>{{ $insight['icon'] }} {{ $insight['title'] }}:</strong> {{ $insight['message'] }}
                </div>
                @endforeach
            </div>
            @endif

            <!-- Quick Stats -->
            @if($latestVital)
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon">🌡️</div>
                    <div class="stat-value {{ $latestVital->temperature > 38 ? 'warning' : 'normal' }}">
                        {{ $latestVital->temperature ?? 'N/A' }}°C
                    </div>
                    <div class="stat-label">Current Temperature</div>
                    <div class="stat-trend stable">→ {{ $latestVital->temperature > 36.1 && $latestVital->temperature < 37.5 ? 'Normal' : 'Monitor' }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">💓</div>
                    <div class="stat-value {{ $latestVital->heart_rate > 100 ? 'warning' : 'normal' }}">
                        {{ $latestVital->heart_rate ?? 'N/A' }} bpm
                    </div>
                    <div class="stat-label">Current Heart Rate</div>
                    <div class="stat-trend {{ $latestVital->heart_rate > 100 ? 'up' : 'stable' }}">
                        {{ $latestVital->heart_rate > 100 ? '↑ Elevated' : '→ Normal' }}
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">🩺</div>
                    <div class="stat-value normal">{{ $latestVital->blood_pressure ?? 'N/A' }}</div>
                    <div class="stat-label">Current Blood Pressure</div>
                    <div class="stat-trend stable">→ Within Range</div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon">💨</div>
                    <div class="stat-value {{ $latestVital->oxygen_saturation < 95 ? 'critical' : 'normal' }}">
                        {{ $latestVital->oxygen_saturation ?? 'N/A' }}%
                    </div>
                    <div class="stat-label">Current SpO2</div>
                    <div class="stat-trend {{ $latestVital->oxygen_saturation < 95 ? 'down' : 'stable' }}">
                        {{ $latestVital->oxygen_saturation < 95 ? '↓ Low' : '→ Normal' }}
                    </div>
                </div>
            </div>
            @endif

            <!-- Charts -->
            <div class="chart-container">
                <div class="chart-header">
                    <h3>📈 Temperature Trend</h3>
                </div>
                <canvas id="temperatureChart" style="max-height: 300px;"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h3>💓 Heart Rate & Blood Pressure Trend</h3>
                </div>
                <canvas id="vitalSignsChart" style="max-height: 300px;"></canvas>
            </div>

            <div class="chart-container">
                <div class="chart-header">
                    <h3>💨 Oxygen Saturation Trend</h3>
                </div>
                <canvas id="oxygenChart" style="max-height: 300px;"></canvas>
            </div>

            <!-- Comparison Grid -->
            @if(isset($vitalStats['temperature']))
            <div class="comparison-grid">
                <div class="comparison-card">
                    <div class="comparison-header">Temperature Range</div>
                    <div class="comparison-values">
                        <div class="comparison-value">
                            <div class="comparison-value-label">MIN</div>
                            <div class="comparison-value-number" style="color: #2196f3;">{{ $vitalStats['temperature']['min'] }}°C</div>
                        </div>
                        <div class="comparison-value">
                            <div class="comparison-value-label">AVG</div>
                            <div class="comparison-value-number">{{ $vitalStats['temperature']['avg'] }}°C</div>
                        </div>
                        <div class="comparison-value">
                            <div class="comparison-value-label">MAX</div>
                            <div class="comparison-value-number" style="color: #f44336;">{{ $vitalStats['temperature']['max'] }}°C</div>
                        </div>
                    </div>
                </div>

                <div class="comparison-card">
                    <div class="comparison-header">Heart Rate Range</div>
                    <div class="comparison-values">
                        <div class="comparison-value">
                            <div class="comparison-value-label">MIN</div>
                            <div class="comparison-value-number" style="color: #2196f3;">{{ $vitalStats['heart_rate']['min'] }} bpm</div>
                        </div>
                        <div class="comparison-value">
                            <div class="comparison-value-label">AVG</div>
                            <div class="comparison-value-number">{{ $vitalStats['heart_rate']['avg'] }} bpm</div>
                        </div>
                        <div class="comparison-value">
                            <div class="comparison-value-label">MAX</div>
                            <div class="comparison-value-number" style="color: #f44336;">{{ $vitalStats['heart_rate']['max'] }} bpm</div>
                        </div>
                    </div>
                </div>

                <div class="comparison-card">
                    <div class="comparison-header">SpO2 Range</div>
                    <div class="comparison-values">
                        <div class="comparison-value">
                            <div class="comparison-value-label">MIN</div>
                            <div class="comparison-value-number" style="color: #f44336;">{{ $vitalStats['oxygen_saturation']['min'] }}%</div>
                        </div>
                        <div class="comparison-value">
                            <div class="comparison-value-label">AVG</div>
                            <div class="comparison-value-number">{{ $vitalStats['oxygen_saturation']['avg'] }}%</div>
                        </div>
                        <div class="comparison-value">
                            <div class="comparison-value-label">MAX</div>
                            <div class="comparison-value-number" style="color: #4caf50;">{{ $vitalStats['oxygen_saturation']['max'] }}%</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Vitals History Table -->
<div class="vitals-table-container">
    <div class="chart-header">
        <h3>📋 Detailed Vital Signs History</h3>
        <p style="font-size: 13px; color: #718096; margin-top: 4px;">
            Showing complete history from all doctors for continuity of care
        </p>
    </div>
    <table class="vitals-table">
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Temperature</th>
                <th>Heart Rate</th>
                <th>Blood Pressure</th>
                <th>SpO2</th>
                <th>Respiratory Rate</th>
                <th>Recorded By</th>
                <th>Context</th> <!-- ✅ NEW COLUMN -->
            </tr>
        </thead>
        <tbody>
            @foreach($vitalRecords->sortByDesc('recorded_at')->take(15) as $vital)
            @php
                // Find which appointment this vital was recorded under
                $relatedAppointment = $patient->appointments->first(function($appt) use ($vital) {
                    return $vital->appointment_id == $appt->appointment_id;
                });
            @endphp
            <tr>
                <td><span class="time-badge">{{ $vital->recorded_at->format('M d, h:i A') }}</span></td>
                <td class="vital-value-cell {{ $vital->temperature > 38 ? 'warning' : 'normal' }}">
                    {{ $vital->temperature ?? 'N/A' }}{{ $vital->temperature ? '°C' : '' }}
                </td>
                <td class="vital-value-cell {{ $vital->heart_rate > 100 ? 'warning' : 'normal' }}">
                    {{ $vital->heart_rate ?? 'N/A' }}{{ $vital->heart_rate ? ' bpm' : '' }}
                </td>
                <td class="vital-value-cell normal">{{ $vital->blood_pressure ?? 'N/A' }}</td>
                <td class="vital-value-cell {{ $vital->oxygen_saturation < 95 ? 'critical' : 'normal' }}">
                    {{ $vital->oxygen_saturation ?? 'N/A' }}{{ $vital->oxygen_saturation ? '%' : '' }}
                </td>
                <td class="vital-value-cell normal">{{ $vital->respiratory_rate ?? 'N/A' }}{{ $vital->respiratory_rate ? '/min' : '' }}</td>
                <td>{{ $vital->nurse->user->name ?? 'N/A' }}</td>
                <td>
                    @if($relatedAppointment)
                        <span class="doctor-badge" style="font-size: 10px;">
                            Dr. {{ $relatedAppointment->doctor->user->name }}
                        </span>
                    @else
                        <span style="font-size: 11px; color: #718096;">Previous visit</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

            <!-- Chart.js Script -->
            <script>
                const chartData = @json($chartData);

                // Temperature Chart
                new Chart(document.getElementById('temperatureChart'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: chartData.temperature,
                            borderColor: '#ff6b6b',
                            backgroundColor: 'rgba(255, 107, 107, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        scales: {
                            y: { beginAtZero: false, min: 35, max: 40 }
                        }
                    }
                });

                // Vital Signs Chart
                new Chart(document.getElementById('vitalSignsChart'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Heart Rate (bpm)',
                            data: chartData.heart_rate,
                            borderColor: '#e74c3c',
                            yAxisID: 'y',
                            tension: 0.4
                        }, {
                            label: 'Systolic BP',
                            data: chartData.blood_pressure_sys,
                            borderColor: '#3498db',
                            yAxisID: 'y1',
                            tension: 0.4
                        }, {
                            label: 'Diastolic BP',
                            data: chartData.blood_pressure_dia,
                            borderColor: '#9b59b6',
                            yAxisID: 'y1',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { type: 'linear', display: true, position: 'left', title: { display: true, text: 'Heart Rate (bpm)' } },
                            y1: { type: 'linear', display: true, position: 'right', title: { display: true, text: 'BP (mmHg)' }, grid: { drawOnChartArea: false } }
                        }
                    }
                });

                // Oxygen Chart
                new Chart(document.getElementById('oxygenChart'), {
                    type: 'line',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'SpO2 (%)',
                            data: chartData.oxygen_saturation,
                            borderColor: '#f44336',
                            backgroundColor: 'rgba(244, 67, 54, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: { beginAtZero: false, min: 85, max: 100 }
                        }
                    }
                });
            </script>

        @elseif($patient)
            <div class="empty-state">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
                </svg>
                <h3>No Vital Records Found</h3>
                <p>No vital signs data available for this patient in the selected time range.</p>
            </div>
        @endif
    </div>
</body>
</html>