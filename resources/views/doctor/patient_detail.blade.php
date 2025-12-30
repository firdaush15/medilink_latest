{{-- resources/views/doctor/patient_detail.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | {{ $patient->user->name }} - Profile</title>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/patient_detail.css'])
  <style>
    /* Mental Health Styles */
    .mental-health-alert {
      background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
      color: white;
      padding: 15px 20px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      gap: 15px;
      margin-bottom: 20px;
      box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    .alert-icon {
      font-size: 32px;
      animation: pulse 2s infinite;
    }

    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.7; }
    }

    .mental-health-summary {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-bottom: 25px;
    }

    .stat-card {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      text-align: center;
    }

    .stat-card h4 {
      font-size: 14px;
      color: #6c757d;
      margin-bottom: 10px;
    }

    .stat-card .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #002b5b;
      margin: 0;
    }

    .mental-health-card {
      border-left-width: 4px !important;
    }

    .risk-badge {
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    .assessment-score {
      font-size: 18px;
      color: #002b5b;
    }

    .detail-section {
      background: #f8f9fa;
      padding: 15px;
      border-radius: 8px;
      margin: 10px 0;
    }

    .detail-section h5 {
      margin-bottom: 10px;
      color: #002b5b;
      font-size: 15px;
      font-weight: 600;
    }

    .detail-section ul {
      margin-left: 20px;
    }

    .doctor-review {
      background: #e7f3ff;
      border-left: 3px solid #0066cc;
    }

    .btn-view-details {
      background: transparent;
      border: 1px solid #002b5b;
      color: #002b5b;
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 10px;
      transition: all 0.3s ease;
    }

    .btn-view-details:hover {
      background: #002b5b;
      color: white;
    }

    .answers-section {
      margin-top: 15px;
      padding: 15px;
      background: white;
      border-radius: 8px;
      border: 1px solid #e0e8f0;
    }

    .answer-item {
      padding: 10px;
      margin: 8px 0;
      border-left: 3px solid;
      background: #f8f9fa;
      border-radius: 4px;
    }

    .answer-badge {
      display: inline-block;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 12px;
      margin-top: 5px;
    }

    .modal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.5);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 10000;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: white;
      padding: 30px;
      border-radius: 16px;
      max-width: 600px;
      width: 90%;
      max-height: 80vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }

    .modal-header h3 {
      margin: 0;
      color: #002b5b;
    }

    .modal-header button {
      background: none;
      border: none;
      font-size: 28px;
      cursor: pointer;
      color: #6c757d;
      line-height: 1;
      padding: 0;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-header button:hover {
      color: #002b5b;
    }

    .assessment-count-badge {
      background: #dc3545;
      color: white;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 11px;
      margin-left: 8px;
      font-weight: 600;
    }

    .trend-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      padding: 4px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    .trend-improving {
      background: #d4edda;
      color: #155724;
    }

    .trend-declining {
      background: #f8d7da;
      color: #721c24;
    }

    .trend-stable {
      background: #fff3cd;
      color: #856404;
    }
  </style>
</head>
<body>

@include('doctor.sidebar.doctor_sidebar')

  <div class="main">
    <!-- Back Button -->
    <a href="{{ route('doctor.patients') }}" class="back-btn">
      ← Back to My Patients
    </a>

    <!-- Patient Profile Header -->
    <div class="patient-header">
      <div class="patient-avatar">
        @if($patient->user->profile_photo)
          <img src="{{ asset('storage/' . $patient->user->profile_photo) }}" alt="Patient Photo">
        @else
          <div class="avatar-placeholder">{{ substr($patient->user->name, 0, 1) }}</div>
        @endif
      </div>
      <div class="patient-info">
        <h1>{{ $patient->user->name }}</h1>
        <div class="patient-meta">
          <span><strong>Age:</strong> {{ $patient->age ?? 'N/A' }} years old</span>
          <span><strong>Gender:</strong> {{ $patient->gender }}</span>
          <span><strong>Phone:</strong> {{ $patient->phone_number }}</span>
          <span><strong>Email:</strong> {{ $patient->user->email }}</span>
        </div>
        @if($patient->user->address)
        <p class="patient-address">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
            <circle cx="12" cy="10" r="3"></circle>
          </svg>
          {{ $patient->user->address }}
        </p>
        @endif
        @if($patient->emergency_contact)
        <p class="emergency-contact">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
          </svg>
          <strong>Emergency Contact:</strong> {{ $patient->emergency_contact }}
        </p>
        @endif
      </div>
      <div class="patient-stats">
        <div class="stat-item">
          <h3>{{ $patient->appointments->count() }}</h3>
          <p>Total Appointments</p>
        </div>
        <div class="stat-item">
          <h3>{{ $patient->appointments->where('status', 'completed')->count() }}</h3>
          <p>Completed Visits</p>
        </div>
        <div class="stat-item">
          <h3>{{ $patient->prescriptions->count() }}</h3>
          <p>Prescriptions</p>
        </div>
      </div>
    </div>

    {{-- ✅ Mental Health Alert Banner --}}
    @if($patient->hasCriticalMentalHealth())
      <div class="mental-health-alert">
        <div class="alert-icon">⚠️</div>
        <div class="alert-content">
          <strong>MENTAL HEALTH ALERT</strong>
          <p>Patient has {{ $patient->mentalHealthAssessments->whereIn('risk_level', ['severe', 'moderate'])->count() }} 
             recent assessment(s) indicating emotional distress. 
             <a href="#" onclick="openTab('mental-health'); return false;" style="color: white; text-decoration: underline;">
               View Assessments →
             </a>
          </p>
        </div>
      </div>
    @endif

    <!-- Tabs Navigation -->
    <div class="tabs">
      <button class="tab-btn active" onclick="openTab('appointments')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="16" y1="2" x2="16" y2="6"></line>
          <line x1="8" y1="2" x2="8" y2="6"></line>
          <line x1="3" y1="10" x2="21" y2="10"></line>
        </svg>
        Appointments History
      </button>
      <button class="tab-btn" onclick="openTab('medical-records')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
          <polyline points="14 2 14 8 20 8"></polyline>
        </svg>
        Medical Records
      </button>
      <button class="tab-btn" onclick="openTab('prescriptions')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
          <line x1="9" y1="9" x2="15" y2="9"></line>
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
        @if($patient->activeAllergies->count() > 0)
        <span class="allergy-count-badge">{{ $patient->activeAllergies->count() }}</span>
        @endif
      </button>
      <button class="tab-btn" onclick="openTab('mental-health')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        Mental Health
        @if($patient->mentalHealthAssessments->count() > 0)
          <span class="assessment-count-badge">{{ $patient->mentalHealthAssessments->count() }}</span>
        @endif
      </button>
      <button class="tab-btn" onclick="openTab('clinical-notes')">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
          <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
        </svg>
        Clinical Notes
      </button>
    </div>

    <!-- Tab Content: Appointments -->
    <div id="appointments" class="tab-content active">
      <div class="content-header">
        <h2>Appointment History</h2>
      </div>
      
      @forelse($patient->appointments->sortByDesc('appointment_date') as $appointment)
      <div class="history-card appointment-card">
        <div class="card-header">
          <div>
            <h4>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y') }} at {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</h4>
            <span class="status-badge status-{{ strtolower($appointment->status) }}">
              {{ ucfirst($appointment->status) }}
            </span>
          </div>
        </div>
        <p><strong>Reason:</strong> {{ $appointment->reason ?? 'General Consultation' }}</p>
        @if($appointment->cancelled_reason)
        <p class="cancelled-reason"><strong>Cancellation Reason:</strong> {{ $appointment->cancelled_reason }}</p>
        @endif
      </div>
      @empty
      <div class="empty-state">
        <p>No appointment history found</p>
      </div>
      @endforelse
    </div>

    <!-- Tab Content: Medical Records -->
    <div id="medical-records" class="tab-content">
      <div class="content-header">
        <h2>Medical Records</h2>
      </div>

      @forelse($patient->medicalRecords->sortByDesc('record_date') as $record)
      <div class="history-card medical-card">
        <div class="card-header">
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
      </div>
      @empty
      <div class="empty-state">
        <p>No medical records found</p>
      </div>
      @endforelse
    </div>

    <!-- Tab Content: Prescriptions -->
    <div id="prescriptions" class="tab-content">
      <div class="content-header">
        <h2>Prescription History</h2>
      </div>

      @forelse($patient->prescriptions->sortByDesc('prescribed_date') as $prescription)
      <div class="history-card prescription-card">
        <div class="card-header">
          <h4>Prescription - {{ \Carbon\Carbon::parse($prescription->prescribed_date)->format('d M Y') }}</h4>
        </div>
        
        @if($prescription->notes)
        <div class="prescription-notes">
          <strong>Doctor's Notes:</strong> {{ $prescription->notes }}
        </div>
        @endif

        <div class="medicines-list">
          <h5>Prescribed Medications:</h5>
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
      </div>
      @empty
      <div class="empty-state">
        <p>No prescriptions found</p>
      </div>
      @endforelse
    </div>

    <!-- Tab Content: Allergies -->
    <div id="allergies" class="tab-content">
      <div class="content-header">
        <h2>Patient Allergies</h2>
      </div>

      @forelse($patient->activeAllergies as $allergy)
      <div class="history-card allergy-card">
        <div class="card-header">
          <div>
            <h4>{{ $allergy->allergen_name }}</h4>
            <span class="severity-badge severity-{{ strtolower(str_replace(['-', ' '], '', $allergy->severity)) }}">
              {{ $allergy->severity }}
            </span>
          </div>
        </div>
        <p><strong>Type:</strong> {{ $allergy->allergy_type }}</p>
        @if($allergy->reaction_description)
        <p><strong>Reaction:</strong> {{ $allergy->reaction_description }}</p>
        @endif
      </div>
      @empty
      <div class="empty-state">
        <p>No known allergies</p>
      </div>
      @endforelse
    </div>

    <!-- Tab Content: Mental Health Assessments -->
    <div id="mental-health" class="tab-content">
      <div class="content-header">
        <h2>Mental Health Assessment History</h2>
        <p>Track patient's emotional wellbeing and mental health over time</p>
      </div>

      @if($patient->mentalHealthAssessments->count() > 0)
        {{-- Statistics Summary --}}
        <div class="mental-health-summary">
          <div class="stat-card">
            <h4>Total Assessments</h4>
            <p class="stat-value">{{ $patient->mentalHealthAssessments->count() }}</p>
          </div>
          <div class="stat-card">
            <h4>Latest Risk Level</h4>
            <p class="stat-value" style="color: {{ $patient->mentalHealthAssessments->first()->risk_color }}">
              {{ $patient->mentalHealthAssessments->first()->risk_level_display }}
            </p>
          </div>
          <div class="stat-card">
            <h4>Needs Attention</h4>
            <p class="stat-value">
              {{ $patient->mentalHealthAssessments->whereIn('risk_level', ['moderate', 'severe'])->count() }}
            </p>
          </div>
          <div class="stat-card">
            <h4>Trend</h4>
            @php
              $trend = $patient->getMentalHealthTrend();
            @endphp
            <p class="stat-value">
              @if($trend === 'improving')
                <span class="trend-badge trend-improving">↓ Improving</span>
              @elseif($trend === 'declining')
                <span class="trend-badge trend-declining">↑ Declining</span>
              @elseif($trend === 'stable')
                <span class="trend-badge trend-stable">→ Stable</span>
              @else
                <span style="font-size: 14px; color: #6c757d;">Not enough data</span>
              @endif
            </p>
          </div>
        </div>

        {{-- Assessment Timeline --}}
        @foreach($patient->mentalHealthAssessments->sortByDesc('assessment_date') as $assessment)
          <div class="history-card mental-health-card" 
               style="border-left: 4px solid {{ $assessment->risk_color }}">
            <div class="card-header">
              <div>
                <h4>Assessment - {{ $assessment->assessment_date->format('d M Y, h:i A') }}</h4>
                <span class="risk-badge" style="background-color: {{ $assessment->risk_color }}20; color: {{ $assessment->risk_color }}; border: 1px solid {{ $assessment->risk_color }}">
                  {{ $assessment->risk_level_display }}
                </span>
              </div>
              <div class="assessment-score">
                <strong>Score: {{ $assessment->total_score }}/60</strong>
              </div>
            </div>

            <div class="assessment-details">
              @if(!empty($assessment->recommendations) && is_array($assessment->recommendations))
              <div class="detail-section">
                <h5>📋 AI-Generated Recommendations:</h5>
                <ul>
                  @foreach($assessment->recommendations as $recommendation)
                    <li>{{ $recommendation }}</li>
                  @endforeach
                </ul>
              </div>
              @else
              <div class="detail-section">
                <h5>📋 AI-Generated Recommendations:</h5>
                <p style="color: #6c757d; font-style: italic;">No recommendations available for this assessment.</p>
              </div>
              @endif

              @if($assessment->isReviewed())
                <div class="detail-section doctor-review">
                  <h5>👨‍⚕️ Your Clinical Notes:</h5>
                  <p>{{ $assessment->doctor_notes }}</p>
                  <small>Reviewed on {{ $assessment->reviewed_at->format('M d, Y h:i A') }}</small>
                </div>
              @else
                <div class="detail-section">
                  <button class="btn-primary" onclick="showReviewModal({{ $assessment->assessment_id }})">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                      <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                    </svg>
                    Add Clinical Review
                  </button>
                </div>
              @endif

              {{-- View Full Details --}}
              <button class="btn-view-details" onclick="toggleAnswers('assessment_{{ $assessment->assessment_id }}')">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
                View Patient Answers
              </button>

              <div id="assessment_{{ $assessment->assessment_id }}" class="answers-section" style="display: none;">
                <h5>📝 Patient's Responses:</h5>
                @foreach($assessment->answers as $answer)
                  <div class="answer-item" style="border-left-color: {{ $answer->score_value >= 2 ? '#ef4444' : ($answer->score_value == 1 ? '#f59e0b' : '#10b981') }}">
                    <strong>Q{{ $answer->question_number }}:</strong> {{ $answer->question_text }}<br>
                    <span class="answer-badge" style="background: {{ $answer->score_value >= 2 ? '#ef444420' : ($answer->score_value == 1 ? '#f59e0b20' : '#10b98120') }}; color: {{ $answer->score_value >= 2 ? '#ef4444' : ($answer->score_value == 1 ? '#f59e0b' : '#10b981') }}">
                      Answer: {{ $answer->answer_option }} (Score: {{ $answer->score_value }})
                    </span>
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        @endforeach
      @else
        <div class="empty-state">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="12" cy="12" r="10"></circle>
            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <h3>No Mental Health Assessments</h3>
          <p>This patient hasn't completed any mental health assessments yet.</p>
          <p><small>Encourage patients to use the MediLink mobile app for self-assessment.</small></p>
        </div>
      @endif
    </div>

    <!-- Tab Content: Clinical Notes -->
    <div id="clinical-notes" class="tab-content">
      <div class="content-header">
        <h2>Add Clinical Note</h2>
        <p>Add observations, clinical findings, or follow-up notes for this patient</p>
      </div>

      <form action="{{ route('doctor.patients.add-note', $patient->patient_id) }}" method="POST" class="modern-form">
        @csrf
        
        <div class="form-group">
          <label>Note Type <span class="required">*</span></label>
          <select name="note_type" required>
            <option value="">Select Type</option>
            <option value="Clinical Observation">Clinical Observation</option>
            <option value="Follow-up Note">Follow-up Note</option>
            <option value="Progress Note">Progress Note</option>
            <option value="Treatment Update">Treatment Update</option>
            <option value="General Note">General Note</option>
          </select>
        </div>

        <div class="form-group">
          <label>Clinical Note <span class="required">*</span></label>
          <textarea name="note_content" rows="6" placeholder="Enter your clinical observations, findings, or notes..." required></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            </svg>
            Save Clinical Note
          </button>
          <button type="reset" class="btn-secondary">Clear Form</button>
        </div>
      </form>

      <hr style="margin: 30px 0; border: 1px solid #e0e8f0;">

      <h3 style="color: #002b5b; margin-bottom: 20px;">Previous Clinical Notes</h3>

      @forelse($patient->medicalRecords->whereIn('record_type', ['Clinical Observation', 'Follow-up Note', 'Progress Note', 'Treatment Update', 'General Note'])->sortByDesc('record_date') as $note)
      <div class="history-card note-card">
        <div class="card-header">
          <div>
            <h4>{{ $note->record_type }}</h4>
          </div>
          <span class="date">{{ \Carbon\Carbon::parse($note->record_date)->format('d M Y, g:i A') }}</span>
        </div>
        <p class="description">{{ $note->description }}</p>
      </div>
      @empty
      <div class="empty-state">
        <p>No clinical notes yet</p>
      </div>
      @endforelse
    </div>

  </div>

  {{-- Mental Health Review Modal --}}
  <div id="reviewModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Add Clinical Review</h3>
        <button type="button" onclick="closeReviewModal()">&times;</button>
      </div>
      <form id="reviewForm" method="POST" action="{{ route('doctor.mental-health.review') }}">
        @csrf
        <input type="hidden" name="assessment_id" id="review_assessment_id">
        
        <div class="form-group">
          <label>Your Clinical Assessment & Recommendations:</label>
          <textarea name="doctor_notes" rows="6" required 
                    placeholder="Based on the patient's responses and your clinical expertise, provide your professional assessment..."></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary">Save Review</button>
          <button type="button" onclick="closeReviewModal()" class="btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Tab switching function
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

    // Toggle answers section
    function toggleAnswers(id) {
      const element = document.getElementById(id);
      const isVisible = element.style.display !== 'none';
      element.style.display = isVisible ? 'none' : 'block';
      
      // Update button text
      const button = element.previousElementSibling;
      const svg = button.querySelector('svg polyline');
      if (isVisible) {
        svg.setAttribute('points', '6 9 12 15 18 9');
      } else {
        svg.setAttribute('points', '18 15 12 9 6 15');
      }
    }

    // Show review modal
    function showReviewModal(assessmentId) {
      document.getElementById('review_assessment_id').value = assessmentId;
      document.getElementById('reviewModal').classList.add('active');
    }

    // Close review modal
    function closeReviewModal() {
      document.getElementById('reviewModal').classList.remove('active');
      document.getElementById('reviewForm').reset();
    }

    // Close modal when clicking outside
    document.getElementById('reviewModal').addEventListener('click', function(e) {
      if (e.target === this) {
        closeReviewModal();
      }
    });
  </script>

</body>
</html>