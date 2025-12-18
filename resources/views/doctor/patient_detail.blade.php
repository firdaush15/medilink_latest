{{-- resources/views/doctor/patient_detail.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | {{ $patient->user->name }} - Profile</title>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/patient_detail.css'])
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

  <script>
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
  </script>
</body>
</html>