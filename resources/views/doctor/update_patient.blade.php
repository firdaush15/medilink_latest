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

    /* ✅ NEW: Restore Draft Button */
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

    /* Template loaded indicator */
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

    /* ✅ NEW: Draft saved indicator */
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

    /* ✅ NEW: Auto-expanding textarea */
    #recordDescription {
      min-height: 150px;
      max-height: 600px;
      resize: vertical;
      overflow-y: auto;
      transition: height 0.3s ease;
    }

    /* ✅ NEW: Character count display */
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
      border: 1px solid #ddd;
      border-radius: 4px;
      max-height: 200px;
      overflow-y: auto;
      width: 100%;
      z-index: 1000;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-top: 2px;
    }

    .suggestion-item {
      padding: 10px;
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

    .cost-display {
      padding: 12px;
      background: #f0f9ff;
      border: 2px solid #3b82f6;
      border-radius: 8px;
      text-align: center;
    }

    .cost-display strong {
      display: block;
      margin-bottom: 4px;
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

            <div class="form-row">
              <div class="form-group">
                <label>Medicine Name <span class="required">*</span></label>
                <input type="text"
                  name="medicines[0][medicine_name]"
                  placeholder="Start typing medicine name..."
                  class="medicine-autocomplete"
                  required
                  data-medicine-index="0">
                <input type="hidden" name="medicines[0][medicine_id]" id="medicine_id_0">
                <input type="hidden" name="medicines[0][unit_price]" id="unit_price_0">
                <small>Type at least 2 characters to search inventory</small>
              </div>

              <div class="form-group">
                <label>Dosage <span class="required">*</span></label>
                <input type="text"
                  name="medicines[0][dosage]"
                  placeholder="e.g., 500mg"
                  required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Frequency/Instructions <span class="required">*</span></label>
                <input type="text"
                  name="medicines[0][frequency]"
                  placeholder="e.g., 1 tablet 3 times daily after meals"
                  required
                  id="frequency_0">
                <small>Be specific: include dose per time and times per day</small>
              </div>

              <div class="form-group">
                <label>Quantity to Prescribe <span class="required">*</span></label>
                <input type="number"
                  name="medicines[0][quantity_prescribed]"
                  placeholder="e.g., 30 tablets"
                  min="1"
                  required
                  id="quantity_0"
                  onchange="calculateDaysSupply(0)">
                <small>Total units (tablets/capsules/ml)</small>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Days Supply (Auto-calculated)</label>
                <input type="number"
                  name="medicines[0][days_supply]"
                  id="days_supply_0"
                  readonly
                  style="background: #f0f0f0;">
                <small>Calculated based on quantity and frequency</small>
              </div>

              <div class="form-group">
                <label>Estimated Cost</label>
                <div class="cost-display" id="cost_display_0">
                  <strong>RM 0.00</strong>
                  <small>(Unit price × Quantity)</small>
                </div>
              </div>
            </div>
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
          <button type="reset" class="btn-secondary">Clear Form</button>
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

    <script>
      // ========================================
      // ENCOUNTER TIMER - FIXED VERSION
      // ========================================

      // ✅ FIX: Use the actual consultation start time from the appointment
      let encounterStartTime;

      // Check if consultation has already started (from database)
      @if($appointment -> consultation_started_at)
      // ✅ Use the stored consultation start time
      encounterStartTime = new Date("{{ $appointment->consultation_started_at->toIso8601String() }}");
      console.log("✅ Consultation already in progress since:", "{{ $appointment->consultation_started_at->format('h:i A') }}");
      @else
      // New consultation - start timer now
      encounterStartTime = new Date();
      console.log("🆕 Starting new consultation timer");
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

      // Start timer on page load
      timerInterval = setInterval(updateEncounterTimer, 1000);
      updateEncounterTimer(); // Initial call

      // ✅ Optional: Show warning if page is about to close during active consultation
      window.addEventListener('beforeunload', function(e) {
        @if($appointment -> isWithDoctor() && !$appointment -> isCompleted())
        const duration = Math.floor((new Date() - encounterStartTime) / 60000);
        if (duration > 0) {
          e.preventDefault();
          e.returnValue = 'Consultation in progress. Are you sure you want to leave?';
          return e.returnValue;
        }
        @endif
      });

      // ✅ Log consultation duration when forms are submitted
      document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
          const duration = Math.floor((new Date() - encounterStartTime) / 1000);
          const minutes = Math.floor(duration / 60);

          console.log(`📊 Consultation duration: ${minutes} minutes`);

          // Optional: Show billing code based on duration
          if (minutes >= 40) {
            console.log('💰 Billing code: 99215 (Level 5 - 40+ minutes)');
          } else if (minutes >= 30) {
            console.log('💰 Billing code: 99214 (Level 4 - 30-39 minutes)');
          } else if (minutes >= 15) {
            console.log('💰 Billing code: 99213 (Level 3 - 15-29 minutes)');
          }
        });
      });

      // Start timer on page load
      timerInterval = setInterval(updateEncounterTimer, 1000);
      updateEncounterTimer(); // Initial call

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
5. [Patient education provided]

BILLING CODE: [To be determined based on visit duration]`
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
  - Temp: {{ $appointment->latestVital->temperature }}°C | SpO2: {{ $appointment->latestVital->oxygen_saturation }}%
@endif

Focused Exam: [Relevant to chief complaint]

PROGRESS ASSESSMENT:
1. [Condition status: improved/stable/worsened]
2. [Treatment effectiveness]
3. [Compliance with treatment plan]

PLAN:
1. Continue current medications: □ Yes □ Adjust dosage
2. Order additional tests: [Specify]
3. Referral needed: □ Yes □ No
4. Next follow-up: [Date]
5. Patient counseling: [Topics discussed]`
        },

        chronic: {
          title: "Chronic Disease Management",
          content: `CHRONIC DISEASE MANAGEMENT VISIT

PRIMARY DIAGNOSIS: [Diabetes Type 2 / Hypertension / Heart Disease / COPD / Other]

DISEASE CONTROL STATUS:
□ Well-controlled
□ Partially controlled
□ Poorly controlled

REVIEW OF CHRONIC CONDITIONS:
@if($appointment->patient->chronic_conditions)
Known conditions: {{ $appointment->patient->chronic_conditions }}
@else
[List all chronic conditions]
@endif

VITAL SIGNS & KEY METRICS:
@if($appointment->latestVital)
Blood Pressure: {{ $appointment->latestVital->blood_pressure }}
Heart Rate: {{ $appointment->latestVital->heart_rate }} bpm
Weight: {{ $appointment->latestVital->weight ?? '[Record]' }} kg
@endif

Disease-Specific Metrics:
[For Diabetes: HbA1c, fasting glucose, last eye exam]
[For HTN: BP trends, target BP]
[For Heart Disease: EF%, last EKG, chest pain frequency]

MEDICATION REVIEW:
Current medications: [List with dosages]
Adherence: □ Good □ Fair □ Poor
Side effects: [Any reported]

LIFESTYLE MODIFICATIONS:
Diet: □ Compliant □ Needs improvement
Exercise: [Frequency and type]
@if($appointment->patient->smoking)
Smoking: Active smoker
@else
Smoking: Non-smoker
@endif

PREVENTIVE CARE:
□ Flu vaccine (due: annually)
□ Pneumonia vaccine (if indicated)
□ Diabetic foot exam
□ Diabetic eye exam
□ Mammogram/colonoscopy (age appropriate)

ASSESSMENT & PLAN:
1. Disease control: [Status and adjustments needed]
2. Medication changes: [Specify]
3. Lab orders: [List tests needed]
4. Specialist referrals: [If needed]
5. Follow-up interval: [Specify]
6. Patient education: [Topics reviewed]`
        },

        acute: {
          title: "Acute Illness",
          content: `ACUTE ILLNESS VISIT

CHIEF COMPLAINT: {{ $appointment->reason ?? '[Symptom]' }}

SYMPTOM ONSET:
Started: [Date/time]
Duration: [Hours/days]
Progression: □ Improving □ Stable □ Worsening

ASSOCIATED SYMPTOMS:
□ Fever (if yes, max temp: _____°C)
□ Cough
□ Shortness of breath
□ Chest pain
□ Nausea/vomiting
□ Diarrhea
□ Headache
□ Body aches
□ Other: [Specify]

VITAL SIGNS:
@if($appointment->latestVital)
Temperature: {{ $appointment->latestVital->temperature }}°C {{ $appointment->latestVital->temperature > 38 ? '(FEVER)' : '' }}
Blood Pressure: {{ $appointment->latestVital->blood_pressure }}
Heart Rate: {{ $appointment->latestVital->heart_rate }} bpm
Respiratory Rate: {{ $appointment->latestVital->respiratory_rate ?? '[Record]' }}
Oxygen Saturation: {{ $appointment->latestVital->oxygen_saturation }}%
@else
[Record vitals - CRITICAL for acute illness assessment]
@endif

PHYSICAL EXAMINATION:
General: [Appears ill / well-appearing]
HEENT: [Throat, ears, nose findings]
Respiratory: [Lung sounds, respiratory effort]
Cardiovascular: [Heart sounds]
Abdomen: [Tenderness, bowel sounds]
Skin: [Rashes, lesions]

ASSESSMENT:
Primary diagnosis: [e.g., Acute upper respiratory infection, Acute gastroenteritis]
Severity: □ Mild □ Moderate □ Severe

RED FLAGS SCREENED:
□ No warning signs present
□ Warning signs present: [Specify - may require hospital admission]

PLAN:
1. Medications prescribed:
   - Symptomatic relief: [Specify]
   - Antibiotics (if indicated): [Specify]
2. Supportive care instructions:
   - Rest and hydration
   - Fever management
   - Return precautions
3. Follow-up: [Timeframe or PRN]
4. Return if:
   - Fever >39°C persisting >3 days
   - Worsening symptoms
   - New symptoms develop
   - [Other specific warnings]`
        },

        preop: {
          title: "Pre-Operative Assessment",
          content: `PRE-OPERATIVE CLEARANCE EVALUATION

SURGICAL PROCEDURE PLANNED:
Procedure: [Name of surgery]
Surgeon: [Surgeon name]
Scheduled date: [Date]
Anesthesia type: □ General □ Regional □ Local

MEDICAL HISTORY REVIEW:
@if($appointment->patient->chronic_conditions)
Chronic conditions: {{ $appointment->patient->chronic_conditions }}
@else
Chronic conditions: [List all]
@endif

Current medications: 
@if($appointment->patient->current_medications)
{{ $appointment->patient->current_medications }}
@else
[Full medication list with dosages]
@endif

Allergies: 
@if($appointment->patient->activeAllergies->count() > 0)
⚠️ DOCUMENTED ALLERGIES:
@foreach($appointment->patient->activeAllergies as $allergy)
- {{ $allergy->allergen_name }} ({{ $allergy->severity }})
@endforeach
@else
□ NKDA (No Known Drug Allergies)
@endif

Previous surgeries:
@if($appointment->patient->past_surgeries)
{{ $appointment->patient->past_surgeries }}
@else
[List previous surgical history]
@endif

CARDIOVASCULAR RISK ASSESSMENT:
Cardiac history: □ None □ See below
- History of MI: □ No □ Yes (date: ____)
- Angina: □ No □ Yes
- Heart failure: □ No □ Yes
- Arrhythmias: □ No □ Yes

Exercise tolerance: [e.g., Can climb 2 flights of stairs without symptoms]

PULMONARY ASSESSMENT:
Respiratory history: □ None □ See below
- Asthma/COPD: □ No □ Yes
- Sleep apnea: □ No □ Yes
@if($appointment->patient->smoking)
Smoking status: Active smoker - counseled on cessation
@else
Smoking status: Non-smoker
@endif

PHYSICAL EXAMINATION:
Vital signs:
@if($appointment->latestVital)
BP: {{ $appointment->latestVital->blood_pressure }} | HR: {{ $appointment->latestVital->heart_rate }} bpm
Temp: {{ $appointment->latestVital->temperature }}°C | SpO2: {{ $appointment->latestVital->oxygen_saturation }}%
Weight: {{ $appointment->latestVital->weight ?? '[Record]' }} kg
@else
[Record complete vital signs]
@endif

General: [Well-appearing, nutritional status]
Cardiovascular: [Heart sounds, peripheral pulses]
Respiratory: [Breath sounds, respiratory effort]
Airway assessment: [Mallampati score, neck mobility]

LABORATORY RESULTS:
□ CBC - [Results or pending]
□ BMP - [Results or pending]
□ Coagulation studies - [Results or pending]
□ EKG - [Interpretation]
□ Chest X-ray - [If indicated]
□ Other: [Specify]

PRE-OP RISK STRATIFICATION:
ASA Physical Status: □ I □ II □ III □ IV □ V
Cardiac risk (RCRI score): [Low/Moderate/High]

RECOMMENDATIONS:
1. Surgical clearance: □ CLEARED □ CLEARED WITH PRECAUTIONS □ NOT CLEARED
2. Medications to continue: [List]
3. Medications to hold: [List with timing]
4. Pre-op instructions given:
   - NPO after midnight before surgery
   - Morning medication instructions
   - What to bring to hospital
5. Post-op considerations: [VTE prophylaxis, antibiotic prophylaxis, etc.]

CLEARANCE SUMMARY:
Patient is □ medically optimized □ requires optimization for elective surgery scheduled [date].`
        },

        mental: {
          title: "Mental Health Assessment",
          content: `MENTAL HEALTH / PSYCHIATRIC EVALUATION

CHIEF COMPLAINT: {{ $appointment->reason ?? '[Presenting concern]' }}

PRESENT ILLNESS:
Onset: [When symptoms began]
Duration: [How long present]
Severity: □ Mild □ Moderate □ Severe
Impact on daily functioning: [Work, relationships, self-care]

PSYCHIATRIC SYMPTOMS REVIEW:
MOOD:
□ Depressed mood
□ Anhedonia (loss of pleasure)
□ Irritability
□ Mood swings
Duration of mood symptoms: [Specify]

ANXIETY:
□ Excessive worry
□ Panic attacks
□ Social anxiety
□ Specific phobias

SLEEP:
□ Insomnia (difficulty falling/staying asleep)
□ Hypersomnia (excessive sleep)
□ Sleep quality: [Poor/Fair/Good]

CONCENTRATION:
□ Difficulty focusing
□ Memory problems
□ Indecisiveness

ENERGY LEVEL:
□ Fatigue
□ Decreased motivation
□ Psychomotor changes

APPETITE/WEIGHT:
□ Decreased appetite
□ Increased appetite
□ Weight change: [Amount]

SUICIDAL IDEATION SCREENING:
□ Denies suicidal thoughts ✓ SAFE
□ Passive thoughts (without plan)
□ Active ideation with plan - ⚠️ SAFETY PLAN NEEDED
□ Recent attempt - ⚠️ IMMEDIATE INTERVENTION

If positive: [Detail safety assessment and intervention]

SUBSTANCE USE:
@if($appointment->patient->alcohol)
Alcohol: Reports use - [Frequency and amount]
@else
Alcohol: Denies
@endif
@if($appointment->patient->smoking)
Tobacco: Current smoker
@else
Tobacco: Non-smoker
@endif
Illicit drugs: □ Denies □ Reports: [Specify]

PAST PSYCHIATRIC HISTORY:
Previous diagnoses: [List]
Hospitalizations: □ None □ Yes: [When and why]
Suicide attempts: □ None □ Yes: [When and method]

CURRENT MEDICATIONS:
@if($appointment->patient->current_medications)
{{ $appointment->patient->current_medications }}
@else
[List psychiatric medications and response]
@endif

SOCIAL HISTORY:
Living situation: [With whom, housing stability]
Support system: [Family, friends]
Employment: [Working/disabled/unemployed]
Stressors: [Recent life events]

MENTAL STATUS EXAMINATION:
Appearance: [Grooming, attire]
Behavior: [Cooperative, agitated, withdrawn]
Speech: [Rate, volume, fluency]
Mood: [Patient's stated mood]
Affect: [Observed emotional expression - congruent/flat/labile]
Thought process: [Logical, tangential, circumstantial]
Thought content: [Delusions, obsessions, preoccupations]
Perceptions: [Hallucinations - auditory/visual]
Cognition: [Alert and oriented x3, memory intact]
Insight: [Good/Fair/Poor regarding condition]
Judgment: [Good/Fair/Poor regarding decisions]

ASSESSMENT:
Primary diagnosis: [DSM-5 diagnosis]
Severity: □ Mild □ Moderate □ Severe
Risk level: □ Low □ Moderate □ High

PLAN:
1. Medication management:
   □ Start new medication: [Specify]
   □ Continue current: [Specify]
   □ Adjust dosage: [Specify]
2. Therapy referral:
   □ CBT (Cognitive Behavioral Therapy)
   □ Psychotherapy
   □ Group therapy
3. Safety planning:
   [Crisis hotline numbers, emergency contacts]
4. Follow-up: [Timeframe - typically 2-4 weeks for medication checks]
5. Patient education provided:
   - Medication side effects to monitor
   - Warning signs requiring immediate care
   - Self-care strategies`
        }
      };

      function selectTemplate(type, cardElement) {
        // Remove selected class from all cards
        document.querySelectorAll('.template-card').forEach(card => {
          card.classList.remove('selected');
        });

        // Add selected class to clicked card
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

        // ✅ FIX: Save current content as draft before loading template
        const currentContent = textarea.value.trim();
        if (currentContent.length > 0) {
          const confirmLoad = confirm('Loading a template will replace your current text. Do you want to save your current work as a draft first?');
          if (confirmLoad) {
            saveDraft(currentContent, document.getElementById('recordTitle').value);
          } else {
            return; // Cancel template load
          }
        }

        // Set title
        document.getElementById('recordTitle').value = template.title + ' - ' + new Date().toLocaleDateString();

        // Set description
        textarea.value = template.content;

        // ✅ FIX: Auto-expand textarea to fit content
        autoExpandTextarea(textarea);

        // Show loaded badge
        document.getElementById('templateLoadedBadge').style.display = 'inline-block';

        // Update character count
        updateCharCount();

        // Show success message
        alert('✓ Template loaded successfully!\n\nYou can now edit the documentation as needed.');
      }

      function clearTemplate() {
        const textarea = document.getElementById('recordDescription');
        const title = document.getElementById('recordTitle');

        // ✅ FIX: Ask to save draft before clearing
        const currentContent = textarea.value.trim();
        if (currentContent.length > 0) {
          const confirmClear = confirm('Are you sure you want to clear? Your work will be lost unless you save it as a draft first.');
          if (!confirmClear) {
            return;
          }
        }

        title.value = '';
        textarea.value = '';
        textarea.style.height = 'auto';
        document.getElementById('templateLoadedBadge').style.display = 'none';

        // Deselect all template cards
        document.querySelectorAll('.template-card').forEach(card => {
          card.classList.remove('selected');
        });

        selectedTemplateType = null;
        updateCharCount();
      }

      // ✅ NEW: Save draft function
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

      // ✅ NEW: Restore draft function
      function restoreDraft() {
        const savedDraft = localStorage.getItem(DRAFT_KEY);

        if (!savedDraft) {
          alert('No draft found.');
          return;
        }

        const draft = JSON.parse(savedDraft);
        const savedTime = new Date(draft.timestamp);

        const confirmRestore = confirm(
          `Restore draft from ${savedTime.toLocaleString()}?\n\n` +
          `This will replace your current content.`
        );

        if (confirmRestore) {
          document.getElementById('recordTitle').value = draft.title;
          document.getElementById('recordDescription').value = draft.content;
          autoExpandTextarea(document.getElementById('recordDescription'));
          updateCharCount();
          alert('✓ Draft restored successfully!');
        }
      }

      // ✅ NEW: Auto-expand textarea as user types
      function autoExpandTextarea(textarea) {
        // Reset height to auto to get the correct scrollHeight
        textarea.style.height = 'auto';

        // Set new height based on content
        const newHeight = Math.min(textarea.scrollHeight, 600); // Max 600px
        textarea.style.height = newHeight + 'px';
      }

      // ✅ NEW: Character count function
      function updateCharCount() {
        const textarea = document.getElementById('recordDescription');
        const count = textarea.value.length;
        document.getElementById('charCount').textContent = count.toLocaleString();
      }

      // ✅ NEW: Auto-save draft every 30 seconds
      let autoSaveInterval;

      function startAutoSave() {
        autoSaveInterval = setInterval(() => {
          const textarea = document.getElementById('recordDescription');
          const title = document.getElementById('recordTitle');
          const content = textarea.value.trim();

          if (content.length > 0) {
            saveDraft(content, title.value);
          }
        }, 30000); // Every 30 seconds
      }

      // ✅ NEW: Check for existing draft on page load
      function checkForDraft() {
        const savedDraft = localStorage.getItem(DRAFT_KEY);

        if (savedDraft) {
          const draft = JSON.parse(savedDraft);
          const savedTime = new Date(draft.timestamp);
          const timeDiff = (new Date() - savedTime) / 1000 / 60; // Minutes

          // Show restore button if draft is less than 24 hours old
          if (timeDiff < 1440) {
            document.getElementById('restoreDraftBtn').style.display = 'inline-block';
          }
        }
      }

      // ========================================
      // EXISTING FUNCTIONS
      // ========================================
      let medicineCount = 1;

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

      // ========================================
      // MEDICINE AUTOCOMPLETE FOR PRESCRIPTION
      // ========================================

      // Initialize autocomplete for all medicine name inputs (including dynamically added ones)
      // ========================================
      // ENHANCED MEDICINE AUTOCOMPLETE WITH PRICING
      // ========================================
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
                  const item = document.createElement('div');
                  item.className = 'suggestion-item';
                  item.innerHTML = `
                            <div style="display: flex; justify-content: space-between;">
                                <div>
                                    <strong>${med.medicine_name}</strong>
                                    <small style="display: block; color: #666; margin-top: 4px;">
                                        ${med.generic_name ? med.generic_name + ' | ' : ''}${med.strength} | ${med.form}
                                    </small>
                                    <small style="color: ${med.quantity_in_stock > 0 ? '#16a34a' : '#dc2626'}; font-weight: 600;">
                                        Stock: ${med.quantity_in_stock}
                                    </small>
                                </div>
                                <div style="text-align: right;">
                                    <strong style="color: #2563eb;">RM ${med.unit_price}</strong>
                                    <small style="display: block; color: #666;">per unit</small>
                                </div>
                            </div>
                        `;

                  item.addEventListener('click', () => {
                    // Fill medicine details
                    input.value = `${med.medicine_name} ${med.strength}`;

                    // Store medicine ID and price (hidden fields)
                    document.getElementById(`medicine_id_${index}`).value = med.medicine_id;
                    document.getElementById(`unit_price_${index}`).value = med.unit_price;

                    // Auto-fill dosage
                    const dosageInput = input.closest('.prescription-item')
                      .querySelector('input[name*="[dosage]"]');
                    if (dosageInput && !dosageInput.value) {
                      dosageInput.value = med.strength;
                    }

                    // Update cost display if quantity is set
                    updateCostDisplay(index);

                    removeSuggestions(input);
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

      // ========================================
      // CALCULATE DAYS SUPPLY
      // ========================================
      function calculateDaysSupply(index) {
        const frequencyInput = document.getElementById(`frequency_${index}`);
        const quantityInput = document.getElementById(`quantity_${index}`);
        const daysSupplyInput = document.getElementById(`days_supply_${index}`);

        if (!frequencyInput || !quantityInput || !daysSupplyInput) return;

        const frequency = frequencyInput.value.toLowerCase();
        const quantity = parseInt(quantityInput.value) || 0;

        if (quantity === 0) {
          daysSupplyInput.value = '';
          return;
        }

        // Extract dose and times from frequency
        // Examples: "1 tablet 3 times daily", "2 capsules twice daily"
        const doseMatch = frequency.match(/(\d+)\s*(?:tablet|capsule|ml|drop)/i);
        const timesMatch = frequency.match(/(\d+)\s*times?\s*(?:daily|per day|a day)/i) ||
          frequency.match(/(once|twice|thrice)/i);

        let dosePerTime = doseMatch ? parseInt(doseMatch[1]) : 1;
        let timesPerDay = 1;

        if (timesMatch) {
          if (timesMatch[1].match(/\d+/)) {
            timesPerDay = parseInt(timesMatch[1]);
          } else {
            const timeWords = {
              once: 1,
              twice: 2,
              thrice: 3
            };
            timesPerDay = timeWords[timesMatch[1].toLowerCase()] || 1;
          }
        }

        const dailyDose = dosePerTime * timesPerDay;
        const daysSupply = Math.ceil(quantity / dailyDose);

        daysSupplyInput.value = daysSupply;

        // Also update cost
        updateCostDisplay(index);
      }

      // ========================================
      // UPDATE COST DISPLAY
      // ========================================
      function updateCostDisplay(index) {
        const unitPriceInput = document.getElementById(`unit_price_${index}`);
        const quantityInput = document.getElementById(`quantity_${index}`);
        const costDisplay = document.getElementById(`cost_display_${index}`);

        if (!unitPriceInput || !quantityInput || !costDisplay) return;

        const unitPrice = parseFloat(unitPriceInput.value) || 0;
        const quantity = parseInt(quantityInput.value) || 0;
        const totalCost = unitPrice * quantity;

        costDisplay.innerHTML = `
        <strong style="font-size: 1.2rem; color: #2563eb;">RM ${totalCost.toFixed(2)}</strong>
        <small style="display: block; color: #666;">
            (RM ${unitPrice.toFixed(2)} × ${quantity} units)
        </small>
    `;
      }


      // Helper function to remove suggestions
      function removeSuggestions(input) {
        const existingSuggestions = input.parentElement.querySelector('.medicine-suggestions');
        if (existingSuggestions) {
          existingSuggestions.remove();
        }
      }

      // Close suggestions when clicking outside
      document.addEventListener('click', function(e) {
        if (!e.target.closest('.form-group')) {
          document.querySelectorAll('.medicine-suggestions').forEach(s => s.remove());
        }
      });

      // ========================================
      // ADD NEW MEDICINE ITEM (UPDATED)
      // ========================================
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
        
        <div class="form-row">
            <div class="form-group">
                <label>Medicine Name <span class="required">*</span></label>
                <input type="text" name="medicines[${medicineCount}][medicine_name]" 
                       placeholder="Start typing medicine name..." required
                       class="medicine-autocomplete" data-medicine-index="${medicineCount}">
                <input type="hidden" name="medicines[${medicineCount}][medicine_id]" id="medicine_id_${medicineCount}">
                <input type="hidden" name="medicines[${medicineCount}][unit_price]" id="unit_price_${medicineCount}">
            </div>

            <div class="form-group">
                <label>Dosage <span class="required">*</span></label>
                <input type="text" name="medicines[${medicineCount}][dosage]" 
                       placeholder="e.g., 500mg" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Frequency/Instructions <span class="required">*</span></label>
                <input type="text" name="medicines[${medicineCount}][frequency]" 
                       placeholder="e.g., 1 tablet 3 times daily after meals" 
                       required id="frequency_${medicineCount}">
            </div>

            <div class="form-group">
                <label>Quantity to Prescribe <span class="required">*</span></label>
                <input type="number" name="medicines[${medicineCount}][quantity_prescribed]" 
                       placeholder="e.g., 30 tablets" min="1" required
                       id="quantity_${medicineCount}" onchange="calculateDaysSupply(${medicineCount})">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label>Days Supply (Auto-calculated)</label>
                <input type="number" name="medicines[${medicineCount}][days_supply]" 
                       id="days_supply_${medicineCount}" readonly style="background: #f0f0f0;">
            </div>

            <div class="form-group">
                <label>Estimated Cost</label>
                <div class="cost-display" id="cost_display_${medicineCount}">
                    <strong>RM 0.00</strong>
                </div>
            </div>
        </div>
    `;
        container.appendChild(newItem);
        medicineCount++;

        // Initialize autocomplete for the new input
        const newMedicineInput = newItem.querySelector('.medicine-autocomplete');
        if (newMedicineInput) {
          initializeMedicineAutocomplete(newMedicineInput);
        }
      }

      // Initialize autocomplete on page load
      document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.medicine-autocomplete').forEach(input => {
          initializeMedicineAutocomplete(input);
        });
      });

      function removeMedicineItem(button) {
        button.closest('.prescription-item').remove();
        const items = document.querySelectorAll('.prescription-item');
        items.forEach((item, index) => {
          item.querySelector('h4').textContent = `Medicine #${index + 1}`;
        });
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

      // File upload preview
      document.getElementById('medicalFile')?.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        if (fileName) {
          const label = document.querySelector('.file-label span');
          label.textContent = fileName;
        }
      });

      // Severity select color change
      document.getElementById('severity-select')?.addEventListener('change', function() {
        const severity = this.value.toLowerCase().replace(/[- ]/g, '');
        this.className = 'severity-' + severity;
      });

      // ========================================
      // EVENT LISTENERS
      // ========================================

      // ✅ NEW: Textarea auto-expand on input
      document.getElementById('recordDescription')?.addEventListener('input', function() {
        autoExpandTextarea(this);
        updateCharCount();
      });

      // ✅ NEW: Save draft on blur (when user clicks away)
      document.getElementById('recordDescription')?.addEventListener('blur', function() {
        const content = this.value.trim();
        const title = document.getElementById('recordTitle').value;

        if (content.length > 0) {
          saveDraft(content, title);
        }
      });

      // ✅ NEW: Handle form submission - clear draft after successful save
      document.querySelector('form[action*="medical-records.store"]')?.addEventListener('submit', function(e) {
        // Clear draft after form submission
        localStorage.removeItem(DRAFT_KEY);
      });

      // ✅ NEW: Warn user before leaving if there's unsaved content
      window.addEventListener('beforeunload', function(e) {
        const textarea = document.getElementById('recordDescription');
        const content = textarea?.value.trim() || '';

        if (content.length > 50) { // If substantial content
          const savedDraft = localStorage.getItem(DRAFT_KEY);

          if (!savedDraft || JSON.parse(savedDraft).content !== content) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
            return e.returnValue;
          }
        }
      });

      // ========================================
      // INITIALIZATION ON PAGE LOAD
      // ========================================
      document.addEventListener('DOMContentLoaded', function() {
        // Check for existing draft
        checkForDraft();

        // Start auto-save
        startAutoSave();

        // Initialize character count
        updateCharCount();

        // Initial textarea expansion if there's content
        const textarea = document.getElementById('recordDescription');
        if (textarea && textarea.value.length > 0) {
          autoExpandTextarea(textarea);
        }
      });

      // ========================================
      // SAVE ENCOUNTER DURATION ON FORM SUBMIT
      // ========================================
      document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
          const duration = Math.floor((new Date() - encounterStartTime) / 1000);
          const minutes = Math.floor(duration / 60);

          console.log(`Encounter duration: ${minutes} minutes`);

          // Optional: Show billing code based on duration
          if (minutes >= 40) {
            console.log('Billing code: 99215 (Level 5 - 40+ minutes)');
          } else if (minutes >= 30) {
            console.log('Billing code: 99214 (Level 4 - 30-39 minutes)');
          } else if (minutes >= 15) {
            console.log('Billing code: 99213 (Level 3 - 15-29 minutes)');
          }
        });
      });
    </script>
</body>

</html>