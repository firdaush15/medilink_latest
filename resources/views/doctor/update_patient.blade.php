{{-- resources/views/doctor/update_patient.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | Update Patient Record</title>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/update_patient.css'])
  <style>
    /* ========================================
   ENCOUNTER TIMER STYLES
   ======================================== */
    .encounter-timer-bar {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 15px 30px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .timer-left {
      display: flex;
      align-items: center;
      gap: 20px;
    }

    .timer-display {
      font-size: 24px;
      font-weight: bold;
      font-family: 'Courier New', monospace;
      letter-spacing: 2px;
    }

    .timer-status {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      background: rgba(255, 255, 255, 0.2);
      padding: 6px 12px;
      border-radius: 20px;
    }

    .pulse-dot {
      width: 10px;
      height: 10px;
      background: #4ade80;
      border-radius: 50%;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {

      0%,
      100% {
        opacity: 1;
      }

      50% {
        opacity: 0.5;
      }
    }

    .timer-info {
      font-size: 13px;
      opacity: 0.9;
    }

    /* ========================================
   TEMPLATE SELECTOR STYLES
   ======================================== */
    .template-selector-section {
      background: linear-gradient(135deg, #f6f8fb 0%, #e9ecef 100%);
      border: 2px dashed #6c757d;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 25px;
    }

    .template-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 15px;
    }

    .template-header h3 {
      margin: 0;
      color: #2c3e50;
      font-size: 18px;
    }

    .template-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 12px;
      margin-top: 15px;
    }

    .template-card {
      background: white;
      border: 2px solid #e0e0e0;
      border-radius: 8px;
      padding: 15px;
      cursor: pointer;
      transition: all 0.3s ease;
      text-align: center;
    }

    .template-card:hover {
      border-color: #667eea;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
      transform: translateY(-2px);
    }

    .template-card.selected {
      border-color: #667eea;
      background: #f0f4ff;
    }

    .template-icon {
      font-size: 32px;
      margin-bottom: 8px;
    }

    .template-name {
      font-weight: 600;
      color: #2c3e50;
      margin-bottom: 5px;
    }

    .template-desc {
      font-size: 12px;
      color: #6c757d;
    }

    .template-actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }

    .btn-load-template {
      flex: 1;
      background: #667eea;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-load-template:hover {
      background: #5568d3;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-clear-template {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-clear-template:hover {
      background: #c0392b;
    }

    .btn-restore-draft {
      background: #f39c12;
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-restore-draft:hover {
      background: #e67e22;
    }

    .template-loaded-badge {
      display: inline-block;
      background: #4ade80;
      color: white;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 10px;
    }

    .draft-saved-badge {
      display: inline-block;
      background: #f39c12;
      color: white;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
      margin-left: 10px;
    }

    #recordDescription {
      min-height: 150px;
      max-height: 600px;
      resize: vertical;
      overflow-y: auto;
      transition: height 0.3s ease;
    }

    .char-count {
      text-align: right;
      font-size: 12px;
      color: #6c757d;
      margin-top: 5px;
    }

    /* ========================================
   MEDICINE AUTOCOMPLETE STYLES
   ======================================== */
    .medicine-suggestions {
      position: absolute;
      background: white;
      border: 2px solid #667eea;
      border-radius: 8px;
      max-height: 300px;
      overflow-y: auto;
      width: 100%;
      z-index: 1000;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      margin-top: 4px;
    }

    .suggestion-item {
      padding: 15px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
      transition: background-color 0.2s;
    }

    .suggestion-item:hover {
      background-color: #f0f4ff;
    }

    .suggestion-item:last-child {
      border-bottom: none;
    }

    .med-name {
      font-weight: 600;
      color: #2c3e50;
      display: block;
      margin-bottom: 5px;
    }

    .med-details {
      font-size: 13px;
      color: #6c757d;
      display: block;
      margin-bottom: 4px;
    }

    .med-stock {
      font-size: 12px;
      font-weight: 600;
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
    }

    .stock-good {
      background: #d4edda;
      color: #155724;
    }

    .stock-low {
      background: #fff3cd;
      color: #856404;
    }

    .stock-out {
      background: #f8d7da;
      color: #721c24;
    }

    .med-price {
      float: right;
      font-weight: 600;
      color: #2563eb;
      font-size: 14px;
    }

    /* ========================================
   PRESCRIPTION FORM STYLES
   ======================================== */
    .prescription-item {
      background: #f8f9fa;
      border: 2px solid #e0e8f0;
      border-radius: 12px;
      padding: 25px;
      margin-bottom: 20px;
      transition: all 0.3s ease;
      animation: slideIn 0.3s ease;
    }

    .prescription-item:hover {
      border-color: #667eea;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .calculation-display {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      border: 2px solid #3b82f6;
      border-radius: 12px;
      padding: 20px;
      margin-top: 15px;
    }

    .calc-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 0;
      font-size: 15px;
    }

    .calc-row:not(:last-child) {
      border-bottom: 1px solid #bfdbfe;
    }

    .calc-label {
      color: #1e40af;
      font-weight: 500;
    }

    .calc-value {
      font-weight: 700;
      color: #1e3a8a;
      font-size: 16px;
    }

    .calc-total {
      font-size: 20px;
      color: #1e3a8a;
      padding-top: 15px;
      margin-top: 10px;
      border-top: 2px solid #3b82f6;
    }

    .frequency-shortcuts {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
      margin-top: 8px;
    }

    .freq-chip {
      padding: 6px 12px;
      background: white;
      border: 2px solid #e0e8f0;
      border-radius: 20px;
      font-size: 13px;
      cursor: pointer;
      transition: all 0.2s;
    }

    .freq-chip:hover {
      border-color: #667eea;
      background: #f0f4ff;
      transform: translateY(-1px);
    }

    .help-text {
      font-size: 13px;
      color: #6c757d;
      margin-top: 5px;
      font-style: italic;
    }

    .symptom-row {
      animation: slideIn 0.3s ease;
    }

    /* ========================================
   DIAGNOSIS SEARCH STYLES
   ======================================== */
    .form-group {
      position: relative;
    }

    #diagnosisSearch {
      width: 100%;
      padding: 12px 16px;
      border: 2px solid #e0e8f0;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s ease;
      background: white;
    }

    #diagnosisSearch:focus {
      border-color: #667eea;
      outline: none;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    #diagnosisSearch::placeholder {
      color: #94a3b8;
    }

    #diagnosisSuggestions {
      position: absolute !important;
      top: 100% !important;
      left: 0 !important;
      right: 0 !important;
      background: white;
      border: 2px solid #667eea;
      border-radius: 8px;
      max-height: 400px;
      overflow-y: auto;
      width: 100% !important;
      z-index: 9999 !important;
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
      margin-top: 4px;
      display: none;
    }

    #diagnosisSuggestions:empty {
      display: none !important;
    }

    .diagnosis-suggestion-item {
      padding: 15px 18px;
      cursor: pointer;
      border-bottom: 1px solid #f0f0f0;
      transition: all 0.2s ease;
      background: white;
    }

    .diagnosis-suggestion-item:hover {
      background-color: #f0f4ff;
      border-left: 4px solid #667eea;
      padding-left: 14px;
    }

    .diagnosis-suggestion-item:last-child {
      border-bottom: none;
      border-radius: 0 0 6px 6px;
    }

    .diagnosis-suggestion-item:first-child {
      border-radius: 6px 6px 0 0;
    }

    .diagnosis-name {
      font-weight: 600;
      color: #2c3e50;
      display: block;
      margin-bottom: 6px;
      font-size: 15px;
      line-height: 1.4;
    }

    .diagnosis-code {
      font-family: 'Courier New', monospace;
      background: #e0e8f0;
      padding: 4px 10px;
      border-radius: 4px;
      font-size: 12px;
      color: #1e40af;
      display: inline-block;
      margin-right: 8px;
      font-weight: 600;
      letter-spacing: 0.5px;
    }

    .diagnosis-details {
      font-size: 13px;
      color: #6c757d;
      margin-top: 6px;
      line-height: 1.5;
    }

    .severity-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 12px;
      font-size: 11px;
      font-weight: 600;
      margin-left: 8px;
      text-transform: uppercase;
      letter-spacing: 0.3px;
    }

    .severity-Minor {
      background: #d4edda;
      color: #155724;
    }

    .severity-Moderate {
      background: #fff3cd;
      color: #856404;
    }

    .severity-Severe {
      background: #f8d7da;
      color: #721c24;
    }

    .severity-Critical {
      background: #721c24;
      color: white;
    }

    #diagnosisSuggestions>div[style*="padding"] {
      text-align: center;
      color: #6c757d;
      font-size: 14px;
      padding: 20px 15px !important;
    }

    #diagnosisSuggestions::-webkit-scrollbar {
      width: 8px;
    }

    #diagnosisSuggestions::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 0 8px 8px 0;
    }

    #diagnosisSuggestions::-webkit-scrollbar-thumb {
      background: #888;
      border-radius: 4px;
    }

    #diagnosisSuggestions::-webkit-scrollbar-thumb:hover {
      background: #555;
    }

    #selectedDiagnosisCard {
      background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
      border: 2px solid #3b82f6;
      border-radius: 12px;
      padding: 20px;
      margin-bottom: 20px;
      animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    #selectedDiagnosisCard h4 {
      margin: 0 0 15px 0;
      color: #1e40af;
      font-size: 16px;
      font-weight: 600;
    }

    #selectedDiagnosisCard>div {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 15px;
    }

    #selectedDiagnosisCard>div>div {
      font-size: 14px;
      line-height: 1.6;
    }

    #selectedDiagnosisCard strong {
      color: #1e3a8a;
      font-weight: 600;
    }

    #selectedDiagnosisCard span {
      color: #334155;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      #diagnosisSuggestions {
        max-height: 300px;
      }

      .diagnosis-suggestion-item {
        padding: 12px 15px;
      }

      #selectedDiagnosisCard>div {
        grid-template-columns: 1fr;
        gap: 10px;
      }

      #diagnosisSuggestions.medicine-suggestions {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        width: 100% !important;
        margin-top: 4px !important;
        z-index: 10000 !important;
      }
    }
  </style>
</head>

<body>

  @include('doctor.sidebar.doctor_sidebar')

  <div class="main">
    <!-- ========================================
         ENCOUNTER TIMER BAR
         ======================================== -->
    <div class="encounter-timer-bar">
      <div class="timer-left">
        <div>
          <div style="font-size: 12px; opacity: 0.9; margin-bottom: 5px;">⏱️ Consultation Duration</div>
          <div class="timer-display" id="encounterTimer">00:00:00</div>
        </div>
        <div class="timer-status">
          <div class="pulse-dot"></div>
          <span>In Progress</span>
        </div>
      </div>
      <div class="timer-info">
        <div>Started: <strong id="startTime">{{ now()->format('h:i A') }}</strong></div>
        <div style="font-size: 11px; opacity: 0.8; margin-top: 3px;">
          Patient: {{ $appointment->patient->user->name }}
        </div>
      </div>
    </div>

    <!-- Back Button -->
    <a href="{{ route('doctor.appointments') }}" class="back-btn">
      ← Back to Appointments
    </a>

    <!-- Patient Info Header -->
    <div class="patient-header">
      <div class="patient-info">
        <h1>{{ $appointment->patient->user->name }}</h1>
        <div class="patient-details">
          <span><strong>Age:</strong> {{ $appointment->patient->age ?? 'N/A' }}</span>
          <span><strong>Gender:</strong> {{ $appointment->patient->gender }}</span>
          <span><strong>Phone:</strong> {{ $appointment->patient->phone_number }}</span>
          <span><strong>Emergency Contact:</strong> {{ $appointment->patient->emergency_contact ?? 'N/A' }}</span>
        </div>
      </div>
      <div class="appointment-info">
        <p><strong>Appointment Date:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
        <p><strong>Reason:</strong> {{ $appointment->reason ?? 'General Consultation' }}</p>
      </div>
    </div>

    <!-- Tab Navigation -->
    <div class="tabs">
      <button class="tab-btn active" onclick="openTab('medical-records')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
          <line x1="16" y1="13" x2="8" y2="13"></line>
          <line x1="16" y1="17" x2="8" y2="17"></line>
          <polyline points="10 9 9 9 8 9"></polyline>
        </svg>
        Medical Records
      </button>
      <button class="tab-btn" onclick="openTab('prescriptions')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="9" y1="9" x2="15" y2="9"></line>
          <line x1="9" y1="15" x2="15" y2="15"></line>
        </svg>
        Prescriptions
      </button>
      <button class="tab-btn" onclick="openTab('allergies')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
          <line x1="12" y1="9" x2="12" y2="13"></line>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        Allergies
        @if($appointment->patient->activeAllergies->count() > 0)
        <span class="allergy-count-badge">{{ $appointment->patient->activeAllergies->count() }}</span>
        @endif
      </button>
      <button class="tab-btn" onclick="openTab('history')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <polyline points="12 6 12 12 16 14"></polyline>
        </svg>
        Patient History
      </button>
      <button class="tab-btn" onclick="openTab('diagnosis')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 11l3 3L22 4"></path>
          <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
        </svg>
        Diagnosis
      </button>
    </div>

    <!-- Tab Content: Medical Records -->
    <div id="medical-records" class="tab-content active">
      <div class="content-header">
        <h2>Add Medical Record</h2>
        <p>Document examination findings, test results, diagnoses, or upload lab reports</p>
      </div>

      <!-- ========================================
           CLINICAL TEMPLATES SELECTOR
           ======================================== -->
      <div class="template-selector-section">
        <div class="template-header">
          <h3>📋 Quick Start with Clinical Template</h3>
          <span id="templateLoadedBadge" style="display: none;" class="template-loaded-badge">
            ✓ Template Loaded
          </span>
          <span id="draftSavedBadge" style="display: none;" class="draft-saved-badge">
            💾 Draft Saved
          </span>
        </div>
        <p style="color: #6c757d; font-size: 14px; margin-bottom: 15px;">
          Select a template below to auto-fill documentation structure based on visit type
        </p>

        <div class="template-grid">
          <!-- Template 1: General Consultation -->
          <div class="template-card" onclick="selectTemplate('general', this)">
            <div class="template-icon">🩺</div>
            <div class="template-name">General Consultation</div>
            <div class="template-desc">Standard visit documentation</div>
          </div>

          <!-- Template 2: Follow-Up Visit -->
          <div class="template-card" onclick="selectTemplate('followup', this)">
            <div class="template-icon">🔄</div>
            <div class="template-name">Follow-Up Visit</div>
            <div class="template-desc">Review progress & adjust treatment</div>
          </div>

          <!-- Template 3: Chronic Disease Management -->
          <div class="template-card" onclick="selectTemplate('chronic', this)">
            <div class="template-icon">💊</div>
            <div class="template-name">Chronic Disease Mgmt</div>
            <div class="template-desc">Diabetes, HTN, heart disease</div>
          </div>

          <!-- Template 4: Acute Illness -->
          <div class="template-card" onclick="selectTemplate('acute', this)">
            <div class="template-icon">🤒</div>
            <div class="template-name">Acute Illness</div>
            <div class="template-desc">Fever, cough, injury</div>
          </div>

          <!-- Template 5: Pre-Operative Assessment -->
          <div class="template-card" onclick="selectTemplate('preop', this)">
            <div class="template-icon">🏥</div>
            <div class="template-name">Pre-Op Assessment</div>
            <div class="template-desc">Surgical clearance</div>
          </div>

          <!-- Template 6: Mental Health -->
          <div class="template-card" onclick="selectTemplate('mental', this)">
            <div class="template-icon">🧠</div>
            <div class="template-name">Mental Health</div>
            <div class="template-desc">Psychiatric evaluation</div>
          </div>
        </div>

        <div class="template-actions">
          <button type="button" class="btn-load-template" onclick="loadSelectedTemplate()">
            📝 Load Selected Template
          </button>
          <button type="button" class="btn-restore-draft" onclick="restoreDraft()" id="restoreDraftBtn" style="display: none;">
            🔄 Restore Last Draft
          </button>
          <button type="button" class="btn-clear-template" onclick="clearTemplate()">
            🗑️ Clear
          </button>
        </div>
      </div>

      <form action="{{ route('doctor.medical-records.store') }}" method="POST" enctype="multipart/form-data" class="modern-form">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $appointment->patient->patient_id }}">
        <input type="hidden" name="doctor_id" value="{{ $doctor->doctor_id }}">
        <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">

        <div class="form-row">
          <div class="form-group">
            <label>Record Date <span class="required">*</span></label>
            <input type="date" name="record_date" value="{{ date('Y-m-d') }}" required>
          </div>

          <div class="form-group">
            <label>Record Type <span class="required">*</span></label>
            <select name="record_type" required>
              <option value="">Select Type</option>
              <option value="Blood Test">Blood Test</option>
              <option value="X-Ray">X-Ray</option>
              <option value="CT Scan">CT Scan</option>
              <option value="MRI">MRI</option>
              <option value="Ultrasound">Ultrasound</option>
              <option value="ECG">ECG/EKG</option>
              <option value="Physical Examination">Physical Examination</option>
              <option value="Lab Report">Lab Report</option>
              <option value="Diagnosis">Diagnosis</option>
              <option value="Progress Note">Progress Note</option>
              <option value="Other">Other</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label>Record Title <span class="required">*</span></label>
          <input type="text" name="record_title" id="recordTitle" placeholder="e.g., Complete Blood Count Results" required>
        </div>

        <div class="form-group">
          <label>Description/Findings <span class="required">*</span></label>
          <textarea
            name="description"
            id="recordDescription"
            rows="12"
            placeholder="Enter detailed findings, observations, test results, or diagnosis..."
            required></textarea>
          <small>Include relevant vital signs, symptoms, test values, and clinical observations</small>
          <div class="char-count">
            <span id="charCount">0</span> characters
          </div>
        </div>

        <div class="form-group">
          <label>Upload Document/Image</label>
          <div class="file-upload-area">
            <input type="file" name="file_path" id="medicalFile" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
            <label for="medicalFile" class="file-label">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="17 8 12 3 7 8"></polyline>
                <line x1="12" y1="3" x2="12" y2="15"></line>
              </svg>
              <span>Click to upload or drag and drop</span>
              <small>PDF, JPG, PNG, DOC (Max 10MB)</small>
            </label>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Medical Record
          </button>
          <button type="reset" class="btn-secondary">Clear Form</button>
        </div>
      </form>
    </div>

    <!-- Tab Content: Prescriptions -->
    <div id="prescriptions" class="tab-content">
      <div class="content-header">
        <h2>Create Prescription</h2>
        <p>Prescribe medications with dosage and usage instructions</p>
      </div>

      <form action="{{ route('doctor.prescriptions.store') }}" method="POST" class="modern-form">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $appointment->patient->patient_id }}">
        <input type="hidden" name="doctor_id" value="{{ $doctor->doctor_id }}">
        <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">

        @if($appointment->patient->drugAllergies()->count() > 0)
        <div class="allergy-alert-banner">
          <div class="alert-icon">⚠️</div>
          <div class="alert-content">
            <strong>PATIENT HAS {{ $appointment->patient->drugAllergies()->count() }} DRUG ALLERGY(IES)</strong>
            <p>Please review patient allergies before prescribing.
              <a href="#" onclick="openTab('allergies'); return false;" style="color: white; text-decoration: underline;">View Allergies →</a>
            </p>
          </div>
        </div>
        @endif

        <div class="form-group">
          <label>Prescription Date <span class="required">*</span></label>
          <input type="date" name="prescribed_date" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-group">
          <label>Doctor's Notes</label>
          <textarea name="notes" rows="3" placeholder="General notes or instructions for the patient..."></textarea>
        </div>

        <hr style="margin: 25px 0; border: 1px solid #e0e8f0;">

        <h3 style="color: #002b5b; margin-bottom: 20px;">Medication Items</h3>

        <div id="prescription-items">
          <div class="prescription-item">
            <div class="item-header">
              <h4>Medicine #1</h4>
            </div>

            <!-- Medicine Name -->
            <div class="form-group">
              <label>Medicine Name <span class="required">*</span></label>
              <input type="text"
                name="medicines[0][medicine_name]"
                placeholder="Start typing medicine name..."
                class="medicine-autocomplete"
                required
                autocomplete="off"
                data-medicine-index="0"
                id="medicine_name_0">
              <input type="hidden" name="medicines[0][medicine_id]" id="medicine_id_0">
              <input type="hidden" name="medicines[0][unit_price]" id="unit_price_0">
              <input type="hidden" id="medicine_strength_0">
              <small>Type at least 2 characters to search inventory</small>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Dosage per Dose <span class="required">*</span></label>
                <input type="text"
                  name="medicines[0][dosage]"
                  id="dosage_0"
                  placeholder="e.g., 500mg, 2 tablets"
                  required>
                <small>How much to take each time</small>
              </div>

              <div class="form-group">
                <label>Frequency (Times per Day) <span class="required">*</span></label>
                <select name="medicines[0][frequency]"
                  id="frequency_0"
                  required
                  onchange="calculateQuantity(0)">
                  <option value="">Select frequency</option>
                  <option value="1">Once daily (OD)</option>
                  <option value="2">Twice daily (BD)</option>
                  <option value="3">Three times daily (TDS)</option>
                  <option value="4">Four times daily (QDS)</option>
                  <option value="0.5">Every other day</option>
                  <option value="prn">As needed (PRN)</option>
                </select>
              </div>

              <div class="form-group">
                <label>Duration (Days) <span class="required">*</span></label>
                <input type="number"
                  name="medicines[0][duration]"
                  id="duration_0"
                  min="1"
                  max="365"
                  placeholder="e.g., 7"
                  required
                  onchange="calculateQuantity(0)">
                <small>How many days of treatment</small>
              </div>
            </div>

            <!-- Special Instructions -->
            <div class="form-group">
              <label>Special Instructions</label>
              <input type="text"
                name="medicines[0][instructions]"
                id="instructions_0"
                placeholder="e.g., Take after meals, with plenty of water">
              <div class="frequency-shortcuts">
                <span class="freq-chip" onclick="addInstruction(0, 'Take after meals')">After meals</span>
                <span class="freq-chip" onclick="addInstruction(0, 'Take before meals')">Before meals</span>
                <span class="freq-chip" onclick="addInstruction(0, 'Take with food')">With food</span>
                <span class="freq-chip" onclick="addInstruction(0, 'Avoid alcohol')">Avoid alcohol</span>
                <span class="freq-chip" onclick="addInstruction(0, 'Take at bedtime')">At bedtime</span>
              </div>
            </div>

            <!-- Auto-calculated Quantity Display -->
            <div class="calculation-display" id="calc_display_0" style="display: none;">
              <div class="calc-row">
                <span class="calc-label">💊 Daily dose:</span>
                <span class="calc-value" id="daily_dose_0">-</span>
              </div>
              <div class="calc-row">
                <span class="calc-label">📦 Total quantity needed:</span>
                <span class="calc-value" id="total_quantity_0">-</span>
              </div>
              <div class="calc-row">
                <span class="calc-label">💵 Unit price:</span>
                <span class="calc-value" id="unit_price_display_0">-</span>
              </div>
              <div class="calc-row calc-total">
                <span class="calc-label">💰 Total cost:</span>
                <span class="calc-value" id="total_cost_0">RM 0.00</span>
              </div>
            </div>

            <!-- Hidden field for calculated quantity -->
            <input type="hidden" name="medicines[0][quantity_prescribed]" id="quantity_prescribed_0">
          </div>
        </div>

        <button type="button" class="btn-add-item" onclick="addMedicineItem()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="16"></line>
            <line x1="8" y1="12" x2="16" y2="12"></line>
          </svg>
          Add Another Medicine
        </button>

        <div class="form-actions">
          <button type="submit" class="btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Save Prescription
          </button>
          <button type="button" class="btn-secondary" onclick="clearPrescriptionForm()">Clear Form</button>
        </div>
      </form>
    </div>

    <!-- ✅ FIXED: Allergies Tab with proper empty state -->
    <div id="allergies" class="tab-content">
      <div class="content-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
          <div>
            <h2 style="color: #dc3545;">⚠️ Patient Allergies & Reactions</h2>
            <p>Manage and monitor patient allergies to prevent adverse reactions</p>
          </div>
          <button type="button" class="btn-add-allergy" onclick="toggleAllergyForm()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="8" x2="12" y2="16"></line>
              <line x1="8" y1="12" x2="16" y2="12"></line>
            </svg>
            Add New Allergy
          </button>
        </div>
      </div>

      <div class="modern-form">
        <!-- Add Allergy Form (Hidden by default) -->
        <div id="allergy-form" style="display: none;">
          <div class="form-header-section">
            <h3>Add New Allergy Record</h3>
            <p>Document patient allergies to medications, food, or environmental factors</p>
          </div>

          <form action="{{ route('doctor.patient-allergies.store') }}" method="POST">
            @csrf
            <input type="hidden" name="patient_id" value="{{ $appointment->patient->patient_id }}">

            <div class="form-row">
              <div class="form-group">
                <label>Allergy Type <span class="required">*</span></label>
                <select name="allergy_type" required>
                  <option value="">Select Type</option>
                  <option value="Drug/Medication">💊 Drug/Medication</option>
                  <option value="Food">🍎 Food</option>
                  <option value="Environmental">🌿 Environmental</option>
                  <option value="Other">📋 Other</option>
                </select>
              </div>

              <div class="form-group">
                <label>Allergen Name <span class="required">*</span></label>
                <input type="text" name="allergen_name" required placeholder="e.g., Penicillin, Aspirin, Peanuts">
                <small>Be specific - include brand names if applicable</small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Severity Level <span class="required">*</span></label>
                <select name="severity" required id="severity-select">
                  <option value="">Select Severity</option>
                  <option value="Mild">Mild - Minor symptoms</option>
                  <option value="Moderate">Moderate - Noticeable symptoms</option>
                  <option value="Severe">Severe - Serious symptoms</option>
                  <option value="Life-threatening">Life-threatening - Anaphylaxis risk</option>
                </select>
              </div>

              <div class="form-group">
                <label>First Occurred</label>
                <input type="date" name="onset_date" max="{{ date('Y-m-d') }}">
                <small>When was this allergy first noticed?</small>
              </div>
            </div>

            <div class="form-group">
              <label>Reaction Description <span class="required">*</span></label>
              <input type="text" name="reaction_description" required placeholder="e.g., Rash, hives, difficulty breathing, swelling">
              <small>Describe the symptoms or reactions experienced</small>
            </div>

            <div class="form-group">
              <label>Additional Notes</label>
              <textarea name="notes" rows="3" placeholder="Any additional information about triggers, treatment, or circumstances..."></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn-primary">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                </svg>
                Save Allergy Record
              </button>
              <button type="button" onclick="toggleAllergyForm()" class="btn-secondary">Cancel</button>
            </div>
          </form>
        </div>

        <!-- Existing Allergies List -->
        <div class="allergies-list-section">
          @if($appointment->patient->activeAllergies->count() > 0)
          <div class="allergy-summary">
            <h3>📋 Active Allergies ({{ $appointment->patient->activeAllergies->count() }})</h3>
            <p>Review all recorded allergies below</p>
          </div>

          @foreach($appointment->patient->activeAllergies->sortByDesc(function($allergy) {
          $severityOrder = ['Life-threatening' => 4, 'Severe' => 3, 'Moderate' => 2, 'Mild' => 1];
          return $severityOrder[$allergy->severity] ?? 0;
          }) as $allergy)
          <div class="allergy-detail-card severity-{{ strtolower(str_replace(['-', ' '], '', $allergy->severity)) }}">
            <div class="allergy-card-header">
              <div class="allergy-title-section">
                <h4>{{ $allergy->allergen_name }}</h4>
                <div class="allergy-badges">
                  <span class="severity-badge {{ strtolower(str_replace(['-', ' '], '', $allergy->severity)) }}">
                    {{ $allergy->severity }}
                  </span>
                  <span class="type-badge">{{ $allergy->allergy_type }}</span>
                </div>
              </div>
              <form action="{{ route('doctor.patient-allergies.destroy', $allergy->allergy_id) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to deactivate this allergy record? This action can affect future prescriptions.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-deactivate">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                  </svg>
                  Deactivate
                </button>
              </form>
            </div>

            <div class="allergy-details-grid">
              @if($allergy->reaction_description)
              <div class="detail-item">
                <span class="detail-label">Reaction:</span>
                <span class="detail-value">{{ $allergy->reaction_description }}</span>
              </div>
              @endif

              @if($allergy->onset_date)
              <div class="detail-item">
                <span class="detail-label">First Reported:</span>
                <span class="detail-value">{{ $allergy->onset_date->format('M d, Y') }} ({{ $allergy->onset_date->diffForHumans() }})</span>
              </div>
              @endif

              @if($allergy->notes)
              <div class="detail-item full-width">
                <span class="detail-label">Notes:</span>
                <span class="detail-value">{{ $allergy->notes }}</span>
              </div>
              @endif
            </div>

            <div class="allergy-footer">
              <small>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                Recorded by: {{ $allergy->recordedBy->name ?? 'Unknown' }} on {{ $allergy->created_at->format('M d, Y') }}
              </small>
            </div>
          </div>
          @endforeach
          @else
          <!-- ✅ FIXED: Restored empty state display -->
          <div class="empty-state-allergies">
            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
            <h3>No Known Allergies</h3>
            <p>This patient has no recorded allergies at this time.</p>
            <button type="button" class="btn-primary" onclick="toggleAllergyForm()">
              Add First Allergy Record
            </button>
          </div>
          @endif
        </div>
      </div>
    </div>

    <!-- Tab Content: Patient History -->
    <div id="history" class="tab-content">
      <div class="content-header">
        <h2>Patient History</h2>
        <p>View previous medical records and prescriptions</p>
      </div>

      <!-- Medical Records History -->
      <div class="history-section">
        <h3>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
          Medical Records
        </h3>

        @forelse($appointment->patient->medicalRecords->sortByDesc('record_date') as $record)
        <div class="history-card">
          <div class="history-header">
            <div>
              <h4>{{ $record->record_title }}</h4>
              <span class="record-type">{{ $record->record_type }}</span>
            </div>
            <span class="date">{{ \Carbon\Carbon::parse($record->record_date)->format('d M Y') }}</span>
          </div>
          <p class="description">{{ $record->description }}</p>
          @if($record->file_path)
          <a href="{{ asset('storage/' . $record->file_path) }}" target="_blank" class="file-link">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path>
              <polyline points="13 2 13 9 20 9"></polyline>
            </svg>
            View Attached File
          </a>
          @endif
          <div class="history-footer">
            <small>Recorded by: Dr. {{ $record->doctor->user->name ?? 'Unknown' }}</small>
          </div>
        </div>
        @empty
        <div class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
            <polyline points="14 2 14 8 20 8"></polyline>
          </svg>
          <p>No medical records found for this patient</p>
        </div>
        @endforelse
      </div>

      <!-- Prescriptions History -->
      <div class="history-section">
        <h3>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="9" x2="15" y2="9"></line>
            <line x1="9" y1="15" x2="15" y2="15"></line>
          </svg>
          Prescriptions
        </h3>

        @forelse($appointment->patient->prescriptions->sortByDesc('prescribed_date') as $prescription)
        <div class="history-card prescription-card">
          <div class="history-header">
            <h4>Prescription</h4>
            <span class="date">{{ \Carbon\Carbon::parse($prescription->prescribed_date)->format('d M Y') }}</span>
          </div>

          @if($prescription->notes)
          <p class="prescription-notes"><strong>Notes:</strong> {{ $prescription->notes }}</p>
          @endif

          <div class="medicines-list">
            @foreach($prescription->items as $item)
            <div class="medicine-item">
              <div class="medicine-name">{{ $item->medicine_name }}</div>
              <div class="medicine-details">
                <span><strong>Dosage:</strong> {{ $item->dosage }}</span>
                <span><strong>Frequency:</strong> {{ $item->frequency }}</span>
              </div>
            </div>
            @endforeach
          </div>

          <div class="history-footer">
            <small>Prescribed by: Dr. {{ $prescription->doctor->user->name ?? 'Unknown' }}</small>
          </div>
        </div>
        @empty
        <div class="empty-state">
          <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="9" y1="9" x2="15" y2="9"></line>
          </svg>
          <p>No prescriptions found for this patient</p>
        </div>
        @endforelse
      </div>
    </div>

    <div id="diagnosis" class="tab-content">
      <div class="content-header">
        <h2>🩺 Clinical Diagnosis</h2>
        <p>Record the patient's diagnosis with ICD-10 codes for proper tracking</p>
      </div>

      <!-- Existing Diagnoses for this Patient -->
      @if($appointment->patient->diagnoses->where('status', 'Active')->count() > 0)
      <div class="alert alert-info" style="margin-bottom: 20px;">
        <strong>📋 Active Diagnoses for this Patient:</strong>
        <ul style="margin: 10px 0 0 20px;">
          @foreach($appointment->patient->diagnoses->where('status', 'Active')->sortByDesc('diagnosis_date')->take(5) as $oldDiag)
          <li>
            <strong>{{ $oldDiag->diagnosisCode->diagnosis_name }}</strong>
            ({{ $oldDiag->diagnosisCode->icd10_code }})
            - diagnosed {{ $oldDiag->diagnosis_date->format('M d, Y') }}
            @if($oldDiag->status === 'Active')
            <span style="color: #f39c12;">● Active</span>
            @endif
          </li>
          @endforeach
        </ul>
      </div>
      @endif

      <form action="{{ route('doctor.diagnoses.store') }}" method="POST" class="modern-form">
        @csrf
        <input type="hidden" name="patient_id" value="{{ $appointment->patient->patient_id }}">
        <input type="hidden" name="doctor_id" value="{{ $doctor->doctor_id }}">
        <input type="hidden" name="appointment_id" value="{{ $appointment->appointment_id }}">

        <div class="form-row">
          <div class="form-group">
            <label>Diagnosis Date <span class="required">*</span></label>
            <input type="date" name="diagnosis_date" value="{{ date('Y-m-d') }}" required>
          </div>

          <div class="form-group">
            <label>Diagnosis Type <span class="required">*</span></label>
            <select name="diagnosis_type" required>
              <option value="Primary">Primary Diagnosis</option>
              <option value="Secondary">Secondary Diagnosis</option>
              <option value="Differential">Differential Diagnosis</option>
              <option value="Ruled Out">Ruled Out</option>
            </select>
          </div>
        </div>

        <!-- Diagnosis Search with Autocomplete -->
        <div class="form-group" style="position: relative;">
          <label>Search Diagnosis <span class="required">*</span></label>
          <input type="text"
            id="diagnosisSearch"
            placeholder="Type disease name or ICD-10 code (e.g., 'Influenza A', 'J11.1')"
            autocomplete="off"
            required>
          <input type="hidden" name="diagnosis_code_id" id="selectedDiagnosisId">
          <div id="diagnosisSuggestions" class="medicine-suggestions" style="display: none;"></div>
          <small>Start typing to search from 25+ common diagnoses</small>
        </div>

        <!-- Selected Diagnosis Display -->
        <div id="selectedDiagnosisCard" style="display: none; background: #f0f9ff; border: 2px solid #3b82f6; border-radius: 12px; padding: 20px; margin-bottom: 20px;">
          <h4 style="margin: 0 0 10px 0; color: #1e40af;">✓ Selected Diagnosis</h4>
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
            <div>
              <strong>Diagnosis:</strong> <span id="selectedDiagnosisName"></span>
            </div>
            <div>
              <strong>ICD-10 Code:</strong> <span id="selectedDiagnosisCode"></span>
            </div>
            <div>
              <strong>Category:</strong> <span id="selectedDiagnosisCategory"></span>
            </div>
            <div>
              <strong>Severity:</strong> <span id="selectedDiagnosisSeverity"></span>
            </div>
            <div style="grid-column: 1 / -1;">
              <strong>Description:</strong> <span id="selectedDiagnosisDescription"></span>
            </div>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Certainty Level <span class="required">*</span></label>
            <select name="certainty" required>
              <option value="Confirmed">Confirmed - Definitive diagnosis</option>
              <option value="Probable">Probable - Highly likely</option>
              <option value="Suspected">Suspected - Under investigation</option>
            </select>
          </div>

          <div class="form-group">
            <label>Current Status <span class="required">*</span></label>
            <select name="status" required>
              <option value="Active">Active - Currently being treated</option>
              <option value="Under Treatment">Under Treatment</option>
              <option value="Resolved">Resolved - Cured</option>
              <option value="Chronic">Chronic - Ongoing management</option>
            </select>
          </div>
        </div>

        <!-- Symptoms Section -->
        <div class="form-group">
          <label>Presenting Symptoms</label>
          <div id="symptomsContainer">
            <div class="symptom-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
              <input type="text"
                name="symptoms[0][name]"
                placeholder="Symptom (e.g., Fever, Cough, Headache)"
                style="flex: 2;">
              <select name="symptoms[0][severity]" style="flex: 1;">
                <option value="Mild">Mild</option>
                <option value="Moderate">Moderate</option>
                <option value="Severe">Severe</option>
              </select>
              <input type="number"
                name="symptoms[0][duration]"
                placeholder="Days"
                min="0"
                style="flex: 1;">
            </div>
          </div>
          <button type="button" class="btn-secondary" onclick="addSymptom()" style="margin-top: 10px;">
            + Add Another Symptom
          </button>
        </div>

        <div class="form-group">
          <label>Clinical Notes</label>
          <textarea name="clinical_notes"
            rows="4"
            placeholder="Detailed clinical observations, examination findings, or additional notes..."></textarea>
        </div>

        <div class="form-group">
          <label>Treatment Plan</label>
          <textarea name="treatment_plan"
            rows="4"
            placeholder="Recommended treatment, medications prescribed (see prescriptions tab), follow-up instructions..."></textarea>
        </div>

        <!-- Referral Section -->
        <div class="form-row">
          <div class="form-group">
            <label style="display: flex; align-items: center; gap: 8px;">
              <input type="checkbox" name="requires_referral" id="requiresReferral" onchange="toggleReferralField()">
              Requires Referral to Specialist
            </label>
          </div>
        </div>

        <div id="referralField" class="form-group" style="display: none;">
          <label>Refer To <span class="required">*</span></label>
          <input type="text" name="referral_to" placeholder="e.g., Cardiologist, Pulmonologist, Surgeon">
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            </svg>
            Save Diagnosis
          </button>
          <button type="reset" class="btn-secondary">Clear Form</button>
        </div>
      </form>
    </div>

    <script>
      // ========================================
      // ENCOUNTER TIMER
      // ========================================
      let encounterStartTime;

      @if($appointment -> consultation_started_at)
      encounterStartTime = new Date("{{ $appointment->consultation_started_at->toIso8601String() }}");
      @else
      encounterStartTime = new Date();
      @endif

      let timerInterval;

      function updateEncounterTimer() {
        const now = new Date();
        const duration = Math.floor((now - encounterStartTime) / 1000);

        const hours = Math.floor(duration / 3600);
        const minutes = Math.floor((duration % 3600) / 60);
        const seconds = duration % 60;

        const timeString =
          String(hours).padStart(2, '0') + ':' +
          String(minutes).padStart(2, '0') + ':' +
          String(seconds).padStart(2, '0');

        document.getElementById('encounterTimer').textContent = timeString;
      }

      timerInterval = setInterval(updateEncounterTimer, 1000);
      updateEncounterTimer();

      // ========================================
      // CLINICAL TEMPLATES
      // ========================================
      let selectedTemplateType = null;
      const DRAFT_KEY = 'medical_record_draft_{{ $appointment->appointment_id }}';

      const templates = {
        general: {
          title: "General Consultation",
          content: `CHIEF COMPLAINT:
{{ $appointment->reason ?? '[Patient\'s main concern]' }}

HISTORY OF PRESENT ILLNESS:
[When did symptoms start? Duration? Severity? What makes it better/worse?]

PHYSICAL EXAMINATION:
General: Well-appearing, no acute distress
Vitals: @if($appointment->latestVital)
- Temperature: {{ $appointment->latestVital->temperature }}°C
- Blood Pressure: {{ $appointment->latestVital->blood_pressure }}
- Heart Rate: {{ $appointment->latestVital->heart_rate }} bpm
- Oxygen Saturation: {{ $appointment->latestVital->oxygen_saturation }}%
@else
[Record vitals]
@endif

HEENT: Normocephalic, atraumatic. PERRLA. TMs clear bilaterally.
Cardiovascular: Regular rate and rhythm. No murmurs, rubs, or gallops.
Respiratory: Clear to auscultation bilaterally. No wheezes, rales, or rhonchi.
Abdomen: Soft, non-tender, non-distended. Normal bowel sounds.
Extremities: No edema, cyanosis, or clubbing.
Neurological: Alert and oriented x3. Cranial nerves II-XII intact.

ASSESSMENT:
1. [Primary diagnosis]
2. [Secondary diagnosis]

PLAN:
1. [Treatment plan]
2. [Medications prescribed - see prescriptions tab]
3. [Lab tests/imaging ordered]
4. [Follow-up instructions]
5. [Patient education provided]`
        },

        followup: {
          title: "Follow-Up Visit",
          content: `FOLLOW-UP VISIT - {{ $appointment->reason ?? 'Condition Review' }}

INTERVAL HISTORY:
[Changes since last visit? New symptoms? Medication compliance?]

REVIEW OF SYSTEMS:
[Any new complaints? Side effects from medications?]

CURRENT MEDICATIONS:
@if($appointment->patient->current_medications)
{{ $appointment->patient->current_medications }}
@else
[List current medications and compliance]
@endif

PHYSICAL EXAMINATION:
General: [Overall appearance compared to last visit]
Vitals: @if($appointment->latestVital)
- BP: {{ $appointment->latestVital->blood_pressure }} | HR: {{ $appointment->latestVital->heart_rate }} bpm
@endif

PROGRESS ASSESSMENT:
1. [Condition status: improved/stable/worsened]
2. [Treatment effectiveness]
3. [Compliance with treatment plan]

PLAN:
1. Continue current medications: □ Yes □ Adjust dosage
2. Order additional tests: [Specify]
3. Next follow-up: [Date]`
        },

        chronic: {
          title: "Chronic Disease Management",
          content: `CHRONIC DISEASE MANAGEMENT VISIT

PRIMARY DIAGNOSIS: [Diabetes Type 2 / Hypertension / Heart Disease / COPD]

DISEASE CONTROL STATUS:
□ Well-controlled
□ Partially controlled
□ Poorly controlled

VITAL SIGNS & KEY METRICS:
@if($appointment->latestVital)
Blood Pressure: {{ $appointment->latestVital->blood_pressure }}
Heart Rate: {{ $appointment->latestVital->heart_rate }} bpm
Weight: {{ $appointment->latestVital->weight ?? '[Record]' }} kg
@endif

MEDICATION REVIEW:
Current medications: [List with dosages]
Adherence: □ Good □ Fair □ Poor

PLAN:
1. Disease control: [Status and adjustments needed]
2. Medication changes: [Specify]
3. Follow-up interval: [Specify]`
        },

        acute: {
          title: "Acute Illness",
          content: `ACUTE ILLNESS VISIT

CHIEF COMPLAINT: {{ $appointment->reason ?? '[Symptom]' }}

SYMPTOM ONSET:
Started: [Date/time]
Duration: [Hours/days]

VITAL SIGNS:
@if($appointment->latestVital)
Temperature: {{ $appointment->latestVital->temperature }}°C
Blood Pressure: {{ $appointment->latestVital->blood_pressure }}
Heart Rate: {{ $appointment->latestVital->heart_rate }} bpm
@endif

ASSESSMENT:
Primary diagnosis: [e.g., Acute upper respiratory infection]
Severity: □ Mild □ Moderate □ Severe

PLAN:
1. Medications prescribed: [Specify]
2. Supportive care instructions
3. Follow-up: [Timeframe or PRN]`
        },

        preop: {
          title: "Pre-Operative Assessment",
          content: `PRE-OPERATIVE CLEARANCE EVALUATION

SURGICAL PROCEDURE PLANNED:
Procedure: [Name of surgery]
Scheduled date: [Date]

MEDICAL HISTORY REVIEW:
Chronic conditions: [List all]
Current medications: [Full medication list]

PHYSICAL EXAMINATION:
@if($appointment->latestVital)
BP: {{ $appointment->latestVital->blood_pressure }} | HR: {{ $appointment->latestVital->heart_rate }} bpm
@endif

CLEARANCE SUMMARY:
Patient is □ medically optimized □ requires optimization`
        },

        mental: {
          title: "Mental Health Assessment",
          content: `MENTAL HEALTH / PSYCHIATRIC EVALUATION

CHIEF COMPLAINT: {{ $appointment->reason ?? '[Presenting concern]' }}

PSYCHIATRIC SYMPTOMS REVIEW:
MOOD: [Depressed/Anxious/Stable]
SLEEP: [Quality and duration]
APPETITE: [Changes noted]

SUICIDAL IDEATION SCREENING:
□ Denies suicidal thoughts ✓ SAFE
□ Passive thoughts (without plan)
□ Active ideation - ⚠️ SAFETY PLAN NEEDED

MENTAL STATUS EXAMINATION:
Appearance: [Grooming, attire]
Mood: [Patient's stated mood]
Affect: [Observed emotional expression]

ASSESSMENT:
Primary diagnosis: [DSM-5 diagnosis]
Risk level: □ Low □ Moderate □ High

PLAN:
1. Medication management: [Specify]
2. Therapy referral: [Type]
3. Follow-up: [Timeframe]`
        }
      };

      function selectTemplate(type, cardElement) {
        document.querySelectorAll('.template-card').forEach(card => {
          card.classList.remove('selected');
        });
        cardElement.classList.add('selected');
        selectedTemplateType = type;
      }

      function loadSelectedTemplate() {
        if (!selectedTemplateType) {
          alert('Please select a template first');
          return;
        }

        const template = templates[selectedTemplateType];
        const textarea = document.getElementById('recordDescription');
        const currentContent = textarea.value.trim();

        if (currentContent.length > 0) {
          const confirmLoad = confirm('Loading a template will replace your current text. Do you want to save your current work as a draft first?');
          if (confirmLoad) {
            saveDraft(currentContent, document.getElementById('recordTitle').value);
          } else {
            return;
          }
        }

        document.getElementById('recordTitle').value = template.title + ' - ' + new Date().toLocaleDateString();
        textarea.value = template.content;
        autoExpandTextarea(textarea);
        document.getElementById('templateLoadedBadge').style.display = 'inline-block';
        updateCharCount();
        alert('✓ Template loaded successfully!');
      }

      function clearTemplate() {
        const textarea = document.getElementById('recordDescription');
        const currentContent = textarea.value.trim();

        if (currentContent.length > 0) {
          const confirmClear = confirm('Are you sure you want to clear?');
          if (!confirmClear) return;
        }

        document.getElementById('recordTitle').value = '';
        textarea.value = '';
        textarea.style.height = 'auto';
        document.getElementById('templateLoadedBadge').style.display = 'none';

        document.querySelectorAll('.template-card').forEach(card => {
          card.classList.remove('selected');
        });

        selectedTemplateType = null;
        updateCharCount();
      }

      function saveDraft(content, title) {
        const draft = {
          title: title || '',
          content: content,
          timestamp: new Date().toISOString()
        };

        localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
        document.getElementById('draftSavedBadge').style.display = 'inline-block';
        document.getElementById('restoreDraftBtn').style.display = 'inline-block';

        setTimeout(() => {
          document.getElementById('draftSavedBadge').style.display = 'none';
        }, 3000);
      }

      function restoreDraft() {
        const savedDraft = localStorage.getItem(DRAFT_KEY);

        if (!savedDraft) {
          alert('No draft found.');
          return;
        }

        const draft = JSON.parse(savedDraft);
        const savedTime = new Date(draft.timestamp);

        if (confirm(`Restore draft from ${savedTime.toLocaleString()}?`)) {
          document.getElementById('recordTitle').value = draft.title;
          document.getElementById('recordDescription').value = draft.content;
          autoExpandTextarea(document.getElementById('recordDescription'));
          updateCharCount();
          alert('✓ Draft restored successfully!');
        }
      }

      function autoExpandTextarea(textarea) {
        textarea.style.height = 'auto';
        const newHeight = Math.min(textarea.scrollHeight, 600);
        textarea.style.height = newHeight + 'px';
      }

      function updateCharCount() {
        const textarea = document.getElementById('recordDescription');
        if (textarea) {
          const count = textarea.value.length;
          const charCountEl = document.getElementById('charCount');
          if (charCountEl) {
            charCountEl.textContent = count.toLocaleString();
          }
        }
      }

      function checkForDraft() {
        const savedDraft = localStorage.getItem(DRAFT_KEY);
        if (savedDraft) {
          const draft = JSON.parse(savedDraft);
          const savedTime = new Date(draft.timestamp);
          const timeDiff = (new Date() - savedTime) / 1000 / 60;

          if (timeDiff < 1440) {
            const restoreBtn = document.getElementById('restoreDraftBtn');
            if (restoreBtn) {
              restoreBtn.style.display = 'inline-block';
            }
          }
        }
      }

      // ========================================
      // MEDICINE AUTOCOMPLETE
      // ========================================
      let medicineCount = 1;

      function initializeMedicineAutocomplete(input) {
        const index = input.dataset.medicineIndex;

        input.addEventListener('input', function() {
          const value = this.value;
          if (value.length < 2) {
            removeSuggestions(this);
            return;
          }

          fetch(`/doctor/medications/search?q=${encodeURIComponent(value)}`)
            .then(response => response.json())
            .then(medicines => {
              removeSuggestions(this);

              if (medicines.length > 0) {
                const suggestionsDiv = document.createElement('div');
                suggestionsDiv.className = 'medicine-suggestions';

                medicines.forEach(med => {
                  const stockClass = med.quantity_in_stock > 100 ? 'stock-good' :
                    med.quantity_in_stock > 0 ? 'stock-low' : 'stock-out';

                  const item = document.createElement('div');
                  item.className = 'suggestion-item';
                  item.innerHTML = `
              <span class="med-name">${med.medicine_name}</span>
              <span class="med-details">
                ${med.generic_name ? med.generic_name + ' • ' : ''}${med.strength} • ${med.form}
              </span>
              <span class="med-stock ${stockClass}">Stock: ${med.quantity_in_stock}</span>
              <span class="med-price">RM ${parseFloat(med.unit_price).toFixed(2)}/unit</span>
            `;

                  item.addEventListener('click', () => {
                    input.value = med.medicine_name;
                    document.getElementById(`medicine_id_${index}`).value = med.medicine_id;
                    document.getElementById(`unit_price_${index}`).value = med.unit_price;

                    if (document.getElementById(`medicine_strength_${index}`)) {
                      document.getElementById(`medicine_strength_${index}`).value = med.strength;
                    }

                    const dosageInput = document.getElementById(`dosage_${index}`);
                    if (dosageInput && !dosageInput.value) {
                      dosageInput.value = med.strength;
                    }

                    removeSuggestions(input);
                    calculateQuantity(index);
                  });

                  suggestionsDiv.appendChild(item);
                });

                this.parentElement.style.position = 'relative';
                this.parentElement.appendChild(suggestionsDiv);
              }
            })
            .catch(error => console.error('Error fetching medicines:', error));
        });
      }

      function removeSuggestions(input) {
        const existingSuggestions = input.parentElement.querySelector('.medicine-suggestions');
        if (existingSuggestions) {
          existingSuggestions.remove();
        }
      }

      function calculateQuantity(index) {
        const frequencySelect = document.getElementById(`frequency_${index}`);
        const durationInput = document.getElementById(`duration_${index}`);
        const dosageInput = document.getElementById(`dosage_${index}`);
        const unitPrice = parseFloat(document.getElementById(`unit_price_${index}`)?.value || 0);
        const calcDisplay = document.getElementById(`calc_display_${index}`);

        if (!frequencySelect || !durationInput || !dosageInput || !calcDisplay) return;

        const frequencyValue = frequencySelect.value;
        const duration = parseInt(durationInput.value) || 0;
        const dosage = dosageInput.value.trim().toLowerCase();

        if (!frequencyValue || !duration || !dosage || frequencyValue === 'prn') {
          calcDisplay.style.display = 'none';
          const qtyInput = document.getElementById(`quantity_prescribed_${index}`);
          if (qtyInput) qtyInput.value = '';
          return;
        }

        const frequency = parseFloat(frequencyValue);
        let unitsPerDose = 1;

        const explicitUnitMatch = dosage.match(/^(\d+\.?\d*)\s*(tablet|capsule|pill|cap|tab|unit)s?/i);
        if (explicitUnitMatch) {
          unitsPerDose = parseFloat(explicitUnitMatch[1]);
        } else {
          const isStrengthOnly = /^\d+\.?\d*\s*(mg|g|ml|mcg|µg|iu|unit)s?$/i.test(dosage);
          if (isStrengthOnly) {
            unitsPerDose = 1;
          } else {
            const bareNumber = dosage.match(/^(\d+\.?\d*)$/);
            if (bareNumber) {
              unitsPerDose = parseFloat(bareNumber[1]);
            }
          }
        }

        const dailyDose = unitsPerDose * frequency;
        const totalQuantity = Math.ceil(dailyDose * duration);
        const totalCost = totalQuantity * unitPrice;

        document.getElementById(`daily_dose_${index}`).textContent = `${dailyDose} units/day`;
        document.getElementById(`total_quantity_${index}`).textContent = `${totalQuantity} units`;
        document.getElementById(`unit_price_display_${index}`).textContent = `RM ${unitPrice.toFixed(2)}`;
        document.getElementById(`total_cost_${index}`).textContent = `RM ${totalCost.toFixed(2)}`;

        const qtyInput = document.getElementById(`quantity_prescribed_${index}`);
        if (qtyInput) qtyInput.value = totalQuantity;

        calcDisplay.style.display = 'block';
      }

      function addInstruction(index, text) {
        const input = document.getElementById(`instructions_${index}`);
        if (!input) return;

        const current = input.value;
        if (current.includes(text)) return;

        input.value = current ? `${current}, ${text}` : text;
      }

      function addMedicineItem() {
        const container = document.getElementById('prescription-items');
        const newItem = document.createElement('div');
        newItem.className = 'prescription-item';
        newItem.innerHTML = `
    <div class="item-header">
      <h4>Medicine #${medicineCount + 1}</h4>
      <button type="button" class="btn-remove" onclick="removeMedicineItem(this)">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"></line>
          <line x1="6" y1="6" x2="18" y2="18"></line>
        </svg>
        Remove
      </button>
    </div>
    
    <div class="form-group">
      <label>Medicine Name <span class="required">*</span></label>
      <input type="text" 
             class="medicine-autocomplete"
             data-medicine-index="${medicineCount}"
             name="medicines[${medicineCount}][medicine_name]"
             id="medicine_name_${medicineCount}"
             placeholder="Start typing medicine name..."
             autocomplete="off"
             required>
      <input type="hidden" name="medicines[${medicineCount}][medicine_id]" id="medicine_id_${medicineCount}">
      <input type="hidden" name="medicines[${medicineCount}][unit_price]" id="unit_price_${medicineCount}">
      <input type="hidden" id="medicine_strength_${medicineCount}">
      <small>Type at least 2 characters to search</small>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label>Dosage per Dose <span class="required">*</span></label>
        <input type="text" name="medicines[${medicineCount}][dosage]" id="dosage_${medicineCount}" 
               placeholder="e.g., 500mg, 2 tablets" required onchange="calculateQuantity(${medicineCount})">
        <small>How much to take each time</small>
      </div>

      <div class="form-group">
        <label>Frequency (Times per Day) <span class="required">*</span></label>
        <select name="medicines[${medicineCount}][frequency]" id="frequency_${medicineCount}" 
                required onchange="calculateQuantity(${medicineCount})">
          <option value="">Select frequency</option>
          <option value="1">Once daily (OD)</option>
          <option value="2">Twice daily (BD)</option>
          <option value="3">Three times daily (TDS)</option>
          <option value="4">Four times daily (QDS)</option>
          <option value="0.5">Every other day</option>
          <option value="prn">As needed (PRN)</option>
        </select>
      </div>

      <div class="form-group">
        <label>Duration (Days) <span class="required">*</span></label>
        <input type="number" name="medicines[${medicineCount}][duration]" id="duration_${medicineCount}" 
               min="1" max="365" placeholder="e.g., 7" required onchange="calculateQuantity(${medicineCount})">
        <small>How many days of treatment</small>
      </div>
    </div>

    <div class="form-group">
      <label>Special Instructions</label>
      <input type="text" name="medicines[${medicineCount}][instructions]" id="instructions_${medicineCount}" 
             placeholder="e.g., Take after meals, with plenty of water">
      <div class="frequency-shortcuts">
        <span class="freq-chip" onclick="addInstruction(${medicineCount}, 'Take after meals')">After meals</span>
        <span class="freq-chip" onclick="addInstruction(${medicineCount}, 'Take before meals')">Before meals</span>
        <span class="freq-chip" onclick="addInstruction(${medicineCount}, 'Take with food')">With food</span>
        <span class="freq-chip" onclick="addInstruction(${medicineCount}, 'Avoid alcohol')">Avoid alcohol</span>
        <span class="freq-chip" onclick="addInstruction(${medicineCount}, 'Take at bedtime')">At bedtime</span>
      </div>
    </div>

    <div class="calculation-display" id="calc_display_${medicineCount}" style="display: none;">
      <div class="calc-row">
        <span class="calc-label">💊 Daily dose:</span>
        <span class="calc-value" id="daily_dose_${medicineCount}">-</span>
      </div>
      <div class="calc-row">
        <span class="calc-label">📦 Total quantity needed:</span>
        <span class="calc-value" id="total_quantity_${medicineCount}">-</span>
      </div>
      <div class="calc-row">
        <span class="calc-label">💵 Unit price:</span>
        <span class="calc-value" id="unit_price_display_${medicineCount}">-</span>
      </div>
      <div class="calc-row calc-total">
        <span class="calc-label">💰 Total cost:</span>
        <span class="calc-value" id="total_cost_${medicineCount}">RM 0.00</span>
      </div>
    </div>

    <input type="hidden" name="medicines[${medicineCount}][quantity_prescribed]" id="quantity_prescribed_${medicineCount}">
  `;

        container.appendChild(newItem);

        const newMedicineInput = newItem.querySelector('.medicine-autocomplete');
        if (newMedicineInput) {
          initializeMedicineAutocomplete(newMedicineInput);
        }

        const dosageInput = document.getElementById(`dosage_${medicineCount}`);
        if (dosageInput) {
          dosageInput.addEventListener('input', function() {
            calculateQuantity(medicineCount);
          });
        }

        medicineCount++;
      }

      function removeMedicineItem(button) {
        button.closest('.prescription-item').remove();
        const items = document.querySelectorAll('.prescription-item');
        items.forEach((item, index) => {
          item.querySelector('h4').textContent = `Medicine #${index + 1}`;
        });
      }

      function clearPrescriptionForm() {
        if (!confirm('Are you sure you want to clear the entire prescription form?')) {
          return;
        }

        const form = document.querySelector('form[action*="prescriptions.store"]');
        if (form) form.reset();

        document.querySelectorAll('.calculation-display').forEach(display => {
          display.style.display = 'none';
        });

        document.querySelectorAll('[id^="medicine_id_"]').forEach(input => input.value = '');
        document.querySelectorAll('[id^="unit_price_"]').forEach(input => input.value = '');
        document.querySelectorAll('[id^="medicine_strength_"]').forEach(input => input.value = '');
        document.querySelectorAll('[id^="quantity_prescribed_"]').forEach(input => input.value = '');

        const items = document.querySelectorAll('.prescription-item');
        items.forEach((item, index) => {
          if (index > 0) item.remove();
        });

        medicineCount = 1;
      }

      // ========================================
      // DIAGNOSIS SEARCH
      // ========================================
      function initializeDiagnosisSearch() {
        const searchInput = document.getElementById('diagnosisSearch');
        const suggestionsContainer = document.getElementById('diagnosisSuggestions');

        if (!searchInput || !suggestionsContainer) {
          console.error('❌ Diagnosis search elements not found');
          return;
        }

        console.log('✅ Diagnosis search initialized');

        searchInput.addEventListener('input', function() {
          const query = this.value.trim();

          if (query.length < 2) {
            suggestionsContainer.innerHTML = '';
            suggestionsContainer.style.display = 'none';
            return;
          }

          suggestionsContainer.innerHTML = '<div style="padding: 20px; color: #6c757d; text-align: center;">🔍 Searching...</div>';
          suggestionsContainer.style.display = 'block';

          console.log('🔍 Searching for:', query);

          fetch(`{{ route('doctor.diagnoses.search') }}?q=${encodeURIComponent(query)}`)
            .then(response => {
              console.log('📡 Response status:', response.status);
              if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
              }
              return response.json();
            })
            .then(diagnoses => {
              console.log('✅ Found diagnoses:', diagnoses.length);
              suggestionsContainer.innerHTML = '';

              if (!diagnoses || diagnoses.length === 0) {
                suggestionsContainer.innerHTML = `
            <div style="padding: 20px; color: #6c757d; text-align: center;">
              <strong>No matching diagnoses found</strong><br>
              <small>Try searching by disease name, ICD-10 code, or category</small>
            </div>
          `;
                return;
              }

              diagnoses.forEach(diag => {
                const item = document.createElement('div');
                item.className = 'diagnosis-suggestion-item';
                item.innerHTML = `
            <span class="diagnosis-name">${escapeHtml(diag.diagnosis_name)}</span>
            <span class="diagnosis-code">${escapeHtml(diag.icd10_code)}</span>
            <span class="severity-badge severity-${diag.severity.replace(/[- ]/g, '')}">${escapeHtml(diag.severity)}</span>
            <div class="diagnosis-details">
              ${escapeHtml(diag.category)} • ${diag.is_infectious ? 'Infectious' : 'Non-infectious'}
              ${diag.is_chronic ? ' • Chronic condition' : ''}
            </div>
          `;

                item.addEventListener('click', () => {
                  selectDiagnosis(diag);
                });

                suggestionsContainer.appendChild(item);
              });

              suggestionsContainer.style.display = 'block';
            })
            .catch(error => {
              console.error('❌ Fetch error:', error);
              suggestionsContainer.innerHTML = `
          <div style="padding: 20px; color: #dc3545; text-align: center;">
            <strong>⚠️ Error loading diagnoses</strong><br>
            <small>${escapeHtml(error.message)}</small>
          </div>
        `;
            });
        });

        document.addEventListener('click', function(e) {
          if (!e.target.closest('#diagnosisSearch') && !e.target.closest('#diagnosisSuggestions')) {
            suggestionsContainer.style.display = 'none';
          }
        });

        searchInput.addEventListener('focus', function() {
          if (this.value.trim().length >= 2 && suggestionsContainer.children.length > 0) {
            suggestionsContainer.style.display = 'block';
          }
        });
      }

      function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      function selectDiagnosis(diagnosis) {
        console.log('✅ Diagnosis selected:', diagnosis.diagnosis_name);

        document.getElementById('selectedDiagnosisId').value = diagnosis.diagnosis_code_id;
        document.getElementById('diagnosisSearch').value = diagnosis.diagnosis_name;
        document.getElementById('selectedDiagnosisName').textContent = diagnosis.diagnosis_name;
        document.getElementById('selectedDiagnosisCode').textContent = diagnosis.icd10_code;
        document.getElementById('selectedDiagnosisCategory').textContent = diagnosis.category;
        document.getElementById('selectedDiagnosisSeverity').textContent = diagnosis.severity;
        document.getElementById('selectedDiagnosisDescription').textContent = diagnosis.description || 'No additional description available';
        document.getElementById('selectedDiagnosisCard').style.display = 'block';
        document.getElementById('diagnosisSuggestions').style.display = 'none';
      }

      function addSymptom() {
        const container = document.getElementById('symptomsContainer');
        const symptomCount = container.children.length;

        const newRow = document.createElement('div');
        newRow.className = 'symptom-row';
        newRow.style.cssText = 'display: flex; gap: 10px; margin-bottom: 10px;';
        newRow.innerHTML = `
    <input type="text"
      name="symptoms[${symptomCount}][name]"
      placeholder="Symptom (e.g., Fever, Cough, Headache)"
      style="flex: 2;">
    <select name="symptoms[${symptomCount}][severity]" style="flex: 1;">
      <option value="Mild">Mild</option>
      <option value="Moderate">Moderate</option>
      <option value="Severe">Severe</option>
    </select>
    <input type="number"
      name="symptoms[${symptomCount}][duration]"
      placeholder="Days"
      min="0"
      style="flex: 1;">
    <button type="button" 
      onclick="this.parentElement.remove()" 
      style="background: #dc3545; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
      ✕
    </button>
  `;

        container.appendChild(newRow);
      }

      function toggleReferralField() {
        const checkbox = document.getElementById('requiresReferral');
        const referralField = document.getElementById('referralField');

        if (checkbox.checked) {
          referralField.style.display = 'block';
          referralField.querySelector('input').required = true;
        } else {
          referralField.style.display = 'none';
          referralField.querySelector('input').required = false;
          referralField.querySelector('input').value = '';
        }
      }

      // ========================================
      // TAB FUNCTIONS
      // ========================================
      function openTab(tabName) {
        const tabContents = document.getElementsByClassName('tab-content');
        for (let i = 0; i < tabContents.length; i++) {
          tabContents[i].classList.remove('active');
        }

        const tabButtons = document.getElementsByClassName('tab-btn');
        for (let i = 0; i < tabButtons.length; i++) {
          tabButtons[i].classList.remove('active');
        }

        document.getElementById(tabName).classList.add('active');
        event.target.closest('.tab-btn').classList.add('active');
      }

      function toggleAllergyForm() {
        const form = document.getElementById('allergy-form');
        if (form.style.display === 'none' || form.style.display === '') {
          form.style.display = 'block';
          form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        } else {
          form.style.display = 'none';
          form.querySelector('form').reset();
        }
      }

      // ========================================
      // EVENT LISTENERS
      // ========================================
      document.addEventListener('DOMContentLoaded', function() {
        checkForDraft();
        updateCharCount();
        initializeDiagnosisSearch();

        document.querySelectorAll('.medicine-autocomplete').forEach(input => {
          initializeMedicineAutocomplete(input);
        });

        document.querySelectorAll('[id^="dosage_"]').forEach(input => {
          input.addEventListener('input', function() {
            const index = this.id.split('_')[1];
            calculateQuantity(index);
          });
        });

        const recordDesc = document.getElementById('recordDescription');
        if (recordDesc) {
          recordDesc.addEventListener('input', function() {
            autoExpandTextarea(this);
            updateCharCount();
          });

          recordDesc.addEventListener('blur', function() {
            const content = this.value.trim();
            const title = document.getElementById('recordTitle')?.value;
            if (content.length > 0) {
              saveDraft(content, title);
            }
          });

          if (recordDesc.value.length > 0) {
            autoExpandTextarea(recordDesc);
          }
        }

        const medicalRecordForm = document.querySelector('form[action*="medical-records.store"]');
        if (medicalRecordForm) {
          medicalRecordForm.addEventListener('submit', function() {
            localStorage.removeItem(DRAFT_KEY);
          });
        }

        const medicalFile = document.getElementById('medicalFile');
        if (medicalFile) {
          medicalFile.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            if (fileName) {
              const label = document.querySelector('.file-label span');
              if (label) label.textContent = fileName;
            }
          });
        }

        const severitySelect = document.getElementById('severity-select');
        if (severitySelect) {
          severitySelect.addEventListener('change', function() {
            const severity = this.value.toLowerCase().replace(/[- ]/g, '');
            this.className = 'severity-' + severity;
          });
        }
      });

      document.addEventListener('click', function(e) {
        if (!e.target.closest('.form-group')) {
          document.querySelectorAll('.medicine-suggestions').forEach(s => s.remove());
        }
      });

      window.addEventListener('beforeunload', function(e) {
        const textarea = document.getElementById('recordDescription');
        const content = textarea?.value.trim() || '';
        if (content.length > 50) {
          const savedDraft = localStorage.getItem(DRAFT_KEY);
          if (!savedDraft || JSON.parse(savedDraft).content !== content) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes.';
            return e.returnValue;
          }
        }
      });
    </script>
</body>

</html>