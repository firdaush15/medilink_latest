{{-- resources/views/doctor/doctor_appointments.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | Doctor Appointments</title>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/doctor_appointments.css'])
</head>

<body>

  @include('doctor.sidebar.doctor_sidebar')

  <div class="main">
    <h1 class="page-title">My Appointments</h1>

    <!-- Time Period Filters -->
    <div class="filter-buttons">
      <button class="time-filter {{ $timeFilter == 'today' ? 'active' : '' }}" data-filter="today">Today</button>
      <button class="time-filter {{ $timeFilter == 'week' ? 'active' : '' }}" data-filter="week">Week</button>
      <button class="time-filter {{ $timeFilter == 'month' ? 'active' : '' }}" data-filter="month">Month</button>
      <button class="time-filter {{ $timeFilter == 'year' ? 'active' : '' }}" data-filter="year">Year</button>
    </div>

    <!-- Overview -->
    <div class="overview">
      <div class="card">
        <h3>Total Appointments</h3>
        <p>{{ $stats['total'] ?? 0 }}</p>
      </div>
      <div class="card">
        <h3>Ready for Me</h3>
        <p>{{ $stats['ready_for_doctor'] ?? 0 }}</p>
      </div>
      <div class="card">
        <h3>In Consultation</h3>
        <p>{{ $stats['in_consultation'] ?? 0 }}</p>
      </div>
      <div class="card">
        <h3>Completed Today</h3>
        <p>{{ $stats['completed'] ?? 0 }}</p>
      </div>
    </div>

    <!-- Patients Ready Section -->
    <h3 class="today-appointments-title">
      @if($timeFilter == 'today')
      Patients Ready for Consultation
      @elseif($timeFilter == 'week')
      This Week's Ready Patients
      @elseif($timeFilter == 'month')
      This Month's Ready Patients
      @elseif($timeFilter == 'year')
      This Year's Ready Patients
      @endif
    </h3>

    <div class="today-appointments">
      @forelse($filteredAppointments as $appointment)
      <div class="appt-card {{ $appointment->isReadyForDoctor() || $appointment->isWithDoctor() ? '' : 'disabled-card' }}">
        <h4>{{ $appointment->patient->user->name }}</h4>

        <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</p>
        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</p>
        <p><strong>Type:</strong> {{ $appointment->reason ?? 'General Consultation' }}</p>

        <p><strong>Status:</strong>
          <span class="status {{ str_replace('_', '-', strtolower($appointment->status)) }}">
            {{ $appointment->getCurrentStageDisplay() }}
          </span>
        </p>

        @if($appointment->queue_number)
        <p><strong>Queue Number:</strong>
          <span class="queue-badge">Q{{ str_pad($appointment->queue_number, 3, '0', STR_PAD_LEFT) }}</span>
        </p>
        @endif

        @if($appointment->latestVital)
        <div class="vitals-box">
          <strong>Latest Vitals:</strong>
          <div class="vitals-grid">
            <span>🌡️ {{ $appointment->latestVital->temperature }}°C</span>
            <span>💓 {{ $appointment->latestVital->heart_rate }} bpm</span>
            <span>🩸 {{ $appointment->latestVital->blood_pressure }}</span>
            <span>🫁 {{ $appointment->latestVital->oxygen_saturation }}%</span>
          </div>
          @if($appointment->latestVital->is_critical)
          <div class="critical-alert">
            ⚠️ <strong>Critical vitals detected</strong> - Requires immediate attention
          </div>
          @endif
        </div>
        @endif

        {{-- ✅ FIXED: Show appropriate buttons based on appointment status --}}
        @if($appointment->isReadyForDoctor())
        {{-- Patient is ready, doctor hasn't started yet --}}
        <div class="appt-actions">
          <button onclick="viewAppointment({{ $appointment->appointment_id }})">View Details</button>
          <button onclick="startConsultation({{ $appointment->appointment_id }})" class="btn-start">
            Start Consultation
          </button>
        </div>

        @elseif($appointment->isWithDoctor())
        {{-- Consultation is in progress --}}
        @if($appointment->consultation_started_at)
        @php
        $duration = $appointment->consultation_started_at->diffInMinutes(now());
        $hours = floor($duration / 60);
        $minutes = $duration % 60;
        @endphp
        <div class="consultation-timer-badge">
          ⏱️ In Progress:
          @if($hours > 0)
          {{ $hours }}h {{ $minutes }}m
          @else
          {{ $minutes }} minutes
          @endif
        </div>
        @endif

        <div class="appt-actions">
          <button onclick="viewAppointment({{ $appointment->appointment_id }})">View Details</button>
          <button onclick="window.location.href='/doctor/appointments/{{ $appointment->appointment_id }}/update-patient'" class="btn-continue">
            📋 Continue Consultation
          </button>
        </div>
        <button class="complete-btn" onclick="confirmCompletion({{ $appointment->appointment_id }}, this)">
          ✓ Mark as Completed
        </button>

        @else
        {{-- Patient not ready yet (still in receptionist/nurse workflow) --}}
        <div class="workflow-lock-overlay">
          <div class="workflow-lock-message">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <h4>Patient Not Ready</h4>
            <p>Waiting for patient to complete:</p>
            @if($appointment->status === 'confirmed')
            <p><strong>1. Check-in at reception</strong></p>
            @elseif(in_array($appointment->status, ['checked_in', 'vitals_pending']))
            <p><strong>2. Vitals recording by nurse</strong></p>
            @elseif($appointment->status === 'vitals_recorded')
            <p><strong>3. Nurse verification</strong></p>
            @endif
            <span class="workflow-badge {{ str_replace('_', '-', $appointment->status) }}">
              {{ $appointment->getCurrentStageDisplay() }}
            </span>
          </div>
        </div>
        @endif
      </div>
      @empty
      <p class="empty-message">
        No patients ready for consultation
        @if($timeFilter == 'today') today
        @elseif($timeFilter == 'week') this week
        @elseif($timeFilter == 'month') this month
        @elseif($timeFilter == 'year') this year
        @endif.
      </p>
      @endforelse
    </div>

    <!-- Status Filter Buttons + Sort Button (Aligned) -->
    <div class="filters-and-sort-container">
      <div class="filter-buttons">
        <button class="status-filter {{ $statusFilter == 'confirmed' ? 'active' : '' }}" data-status="confirmed">Scheduled</button>
        <button class="status-filter {{ $statusFilter == 'ready' ? 'active' : '' }}" data-status="ready">Ready for Me</button>
        <button class="status-filter {{ $statusFilter == 'in_consultation' ? 'active' : '' }}" data-status="in_consultation">In Consultation</button>
        <button class="status-filter {{ $statusFilter == 'completed' ? 'active' : '' }}" data-status="completed">Completed</button>
        <button class="status-filter {{ $statusFilter == 'cancelled' ? 'active' : '' }}" data-status="cancelled">Cancelled</button>
      </div>

      <div class="sort-dropdown">
        <button class="sort-btn" onclick="toggleSortDropdown()">
          Sort By &#9662;
        </button>
        <div id="sortOptions" class="sort-options">
          <div onclick="sortTable('date-asc')">Date (Oldest → Newest)</div>
          <div onclick="sortTable('date-desc')">Date (Newest → Oldest)</div>
          <div onclick="sortTable('name-asc')">Patient Name (A → Z)</div>
          <div onclick="sortTable('name-desc')">Patient Name (Z → A)</div>
        </div>
      </div>
    </div>

    <div class="section">
      <h3>Appointment List</h3>
      <table>
        <thead>
          <tr>
            <th>Patient Name</th>
            <th>Date</th>
            <th>Time</th>
            <th>Type</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="appointmentTableBody">
          @forelse($appointments as $appointment)
          <tr>
            <td>{{ $appointment->patient->user->name }}</td>
            <td>{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }}</td>
            <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
            <td>{{ $appointment->reason ?? 'General Consultation' }}</td>
            <td>
              <span class="status {{ str_replace('_', '-', strtolower($appointment->status)) }}">
                {{ $appointment->getCurrentStageDisplay() }}
              </span>
            </td>
            <td>
              <button class="action-btn view-btn" onclick="viewAppointment({{ $appointment->appointment_id }})">View</button>

              {{-- ✅ ADDED: Continue button in table for in-progress consultations --}}
              @if($appointment->isWithDoctor())
              <button class="action-btn continue-btn" onclick="window.location.href='/doctor/appointments/{{ $appointment->appointment_id }}/update-patient'">
                Continue
              </button>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" style="text-align: center; color: #a0aec0; padding: 40px;">No appointments found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <!-- View Modal -->
  <div id="viewModal" class="modal">
    <div class="modal-content">
      <button class="close-btn" onclick="closeModal('viewModal')">X</button>
      <h2>Appointment Details</h2>
      <div id="viewModalContent"></div>
    </div>
  </div>

  <!-- Start Consultation Modal -->
  <div id="startConsultationModal" class="modal">
    <div class="modal-content modal-small">
      <h2>Start Consultation</h2>
      <p>Are you ready to start consultation with this patient?</p>
      <div class="modal-actions">
        <button class="save-btn" id="confirmStartBtn">Yes, Start Now</button>
        <button class="close-btn" onclick="closeModal('startConsultationModal')">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Completion Confirmation Modal -->
  <div id="confirmModal" class="modal">
    <div class="modal-content modal-small">
      <h2>Confirm Completion</h2>
      <p>Are you sure you want to mark this appointment as <strong>Completed</strong>?</p>
      <div class="modal-actions">
        <button class="save-btn" id="confirmYesBtn">Yes, Confirm</button>
        <button class="close-btn" onclick="closeModal('confirmModal')">Cancel</button>
      </div>
    </div>
  </div>

  <script>
    let selectedButton = null;
    let selectedAppointmentId = null;
    let currentTimeFilter = '{{ $timeFilter }}';
    let currentStatusFilter = '{{ $statusFilter }}';

    function openModal(id) {
      document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {
      document.getElementById(id).style.display = 'none';
    }

    function startConsultation(appointmentId) {
      selectedAppointmentId = appointmentId;
      openModal('startConsultationModal');
    }

    document.getElementById('confirmStartBtn').addEventListener('click', function() {
      if (selectedAppointmentId) {
        fetch(`/doctor/appointments/${selectedAppointmentId}/start`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              closeModal('startConsultationModal');
              window.location.href = `/doctor/appointments/${selectedAppointmentId}/update-patient`;
            } else {
              alert(data.message || 'Error starting consultation');
              closeModal('startConsultationModal');
            }
          })
          .catch(error => {
            alert('Error starting consultation');
            console.error(error);
            closeModal('startConsultationModal');
          });
      }
    });

    function confirmCompletion(appointmentId, button) {
      selectedButton = button;
      selectedAppointmentId = appointmentId;
      openModal('confirmModal');
    }

    function viewAppointment(appointmentId) {
      fetch(`/doctor/appointments/${appointmentId}`)
        .then(response => response.json())
        .then(data => {
          const content = `
            <p><strong>Patient Name:</strong> ${data.patient_name}</p>
            <p><strong>Date:</strong> ${data.date}</p>
            <p><strong>Time:</strong> ${data.time}</p>
            <p><strong>Type:</strong> ${data.reason || 'General Consultation'}</p>
            <p><strong>Status:</strong> ${data.status}</p>
            <p><strong>Patient Phone:</strong> ${data.patient_phone}</p>
            <p><strong>Patient Gender:</strong> ${data.patient_gender}</p>
          `;
          document.getElementById('viewModalContent').innerHTML = content;
          openModal('viewModal');
        })
        .catch(error => {
          alert('Error loading appointment details');
          console.error(error);
        });
    }

    function toggleSortDropdown() {
      document.getElementById("sortOptions").classList.toggle("show");
    }

    function sortTable(sortType) {
      const table = document.getElementById("appointmentTableBody");
      const rows = Array.from(table.querySelectorAll("tr"));

      rows.sort((a, b) => {
        if (sortType.includes('name')) {
          const aText = a.cells[0].textContent.trim();
          const bText = b.cells[0].textContent.trim();
          return sortType === 'name-asc' ? aText.localeCompare(bText) : bText.localeCompare(aText);
        } else if (sortType.includes('date')) {
          const aDate = new Date(a.cells[1].textContent.split('/').reverse().join('-'));
          const bDate = new Date(b.cells[1].textContent.split('/').reverse().join('-'));
          return sortType === 'date-asc' ? aDate - bDate : bDate - aDate;
        }
        return 0;
      });

      rows.forEach(row => table.appendChild(row));
      toggleSortDropdown();
    }

    function applyFilters() {
      window.location.href = `/doctor/appointments?time_filter=${currentTimeFilter}&status_filter=${currentStatusFilter}`;
    }

    window.onclick = function(event) {
      if (!event.target.matches('.sort-btn')) {
        const dropdowns = document.getElementsByClassName("sort-options");
        for (let i = 0; i < dropdowns.length; i++) {
          dropdowns[i].classList.remove('show');
        }
      }
    }

    document.getElementById('confirmYesBtn').addEventListener('click', function() {
      if (selectedAppointmentId) {
        fetch(`/doctor/appointments/${selectedAppointmentId}/complete`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              closeModal('confirmModal');
              location.reload();
            } else {
              alert(data.message || 'Error completing appointment');
              closeModal('confirmModal');
            }
          })
          .catch(error => {
            alert('Error completing appointment');
            console.error(error);
            closeModal('confirmModal');
          });
      }
    });

    document.querySelectorAll('.time-filter').forEach(button => {
      button.addEventListener('click', function() {
        currentTimeFilter = this.getAttribute('data-filter');
        applyFilters();
      });
    });

    document.querySelectorAll('.status-filter').forEach(button => {
      button.addEventListener('click', function() {
        currentStatusFilter = this.getAttribute('data-status');
        applyFilters();
      });
    });
  </script>
</body>

</html>