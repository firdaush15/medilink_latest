{{-- resources/views/doctor/doctor_patients.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | My Patients</title>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/doctor_patients.css'])
</head>
<body>

@include('doctor.sidebar.doctor_sidebar')

  <div class="main">
    <h1 class="page-title">My Patients</h1>

    <!-- ✅ NEW: Care Status Info Banner -->
    <div class="info-banner">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="10"></circle>
        <line x1="12" y1="16" x2="12" y2="12"></line>
        <line x1="12" y1="8" x2="12.01" y2="8"></line>
      </svg>
      <div>
        <strong>Patient Privacy Notice:</strong> You can only view patients you have treated or have scheduled appointments with. Historical records are retained for 2 years per medical regulations.
      </div>
    </div>

    <div class="section">
      <!-- Search & Filter Form -->
      <form method="GET" action="{{ route('doctor.patients') }}" class="search-filter">
        <div class="search-group">
          <input type="text" name="search" value="{{ request('search') }}" placeholder="Search patient name, email, phone..." />
          <button type="submit" class="btn-search">Search</button>
        </div>

        <div class="filter-group">
          <!-- ✅ NEW: Care Status Filter -->
          <select name="status" onchange="this.form.submit()">
            <option value="">All Care Status</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active Care (Has Upcoming)</option>
            <option value="recent" {{ request('status') == 'recent' ? 'selected' : '' }}>Recently Treated (Last 30 Days)</option>
            <option value="followup" {{ request('status') == 'followup' ? 'selected' : '' }}>Follow-up Needed</option>
            <option value="historical" {{ request('status') == 'historical' ? 'selected' : '' }}>Historical Records (6+ Months)</option>
          </select>

          <select name="gender" onchange="this.form.submit()">
            <option value="">All Genders</option>
            <option value="Male" {{ request('gender') == 'Male' ? 'selected' : '' }}>Male</option>
            <option value="Female" {{ request('gender') == 'Female' ? 'selected' : '' }}>Female</option>
            <option value="Other" {{ request('gender') == 'Other' ? 'selected' : '' }}>Other</option>
          </select>

          <select name="sort" onchange="this.form.submit()">
            <option value="">Sort By</option>
            <option value="last-visit-recent" {{ request('sort') == 'last-visit-recent' ? 'selected' : '' }}>Most Recent Visit</option>
            <option value="last-visit-oldest" {{ request('sort') == 'last-visit-oldest' ? 'selected' : '' }}>Oldest Visit (Needs Follow-up)</option>
            <option value="name-asc" {{ request('sort') == 'name-asc' ? 'selected' : '' }}>Name A → Z</option>
            <option value="name-desc" {{ request('sort') == 'name-desc' ? 'selected' : '' }}>Name Z → A</option>
            <option value="age-asc" {{ request('sort') == 'age-asc' ? 'selected' : '' }}>Age Young → Old</option>
            <option value="age-desc" {{ request('sort') == 'age-desc' ? 'selected' : '' }}>Age Old → Young</option>
          </select>

          @if(request()->hasAny(['search', 'gender', 'sort', 'status']))
          <a href="{{ route('doctor.patients') }}" class="btn-reset">Clear Filters</a>
          @endif
        </div>
      </form>

      <!-- ✅ UPDATED: Statistics Cards with Real Metrics -->
      <div class="stats-cards">
        <div class="stat-card stat-active">
          <h3>Active Patients</h3>
          <p class="stat-number">{{ $stats['total_active'] }}</p>
          <small>Patients with upcoming appointments</small>
        </div>
        <div class="stat-card stat-recent">
          <h3>Seen This Month</h3>
          <p class="stat-number">{{ $stats['seen_this_month'] }}</p>
          <small>Completed appointments in {{ now()->format('F') }}</small>
        </div>
        <div class="stat-card stat-followup">
          <h3>Follow-up Needed</h3>
          <p class="stat-number">{{ $stats['followup_needed'] }}</p>
          <small>Patients needing follow-up consultation</small>
        </div>
        <div class="stat-card stat-total">
          <h3>Total Under Care</h3>
          <p class="stat-number">{{ $stats['total_historical'] }}</p>
          <small>All patients within 2 years</small>
        </div>
      </div>

      <!-- Patients Table -->
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Patient Name</th>
              <th>Gender</th>
              <th>Age</th>
              <th>Contact</th>
              <th>Care Status</th>
              <th>Last Visit</th>
              <th>Total Visits</th>
              <th>Next Appointment</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($patients as $patient)
            <tr>
              <td><strong>{{ $patient->user->name }}</strong></td>
              <td>{{ $patient->gender }}</td>
              <td>{{ $patient->age ?? 'N/A' }}</td>
              <td>
                <small>{{ $patient->phone_number }}</small>
              </td>
              <!-- ✅ NEW: Care Status Badge -->
              <td>
                <span class="status-badge {{ $patient->care_status_class }}">
                  {{ $patient->care_status_label }}
                </span>
              </td>
              <td>
                @if($patient->last_visit)
                  {{ \Carbon\Carbon::parse($patient->last_visit->appointment_date)->format('d/m/Y') }}
                  <br><small class="text-muted">{{ \Carbon\Carbon::parse($patient->last_visit->appointment_date)->diffForHumans() }}</small>
                @else
                  <span class="no-data">No completed visits</span>
                @endif
              </td>
              <td>
                <strong>{{ $patient->total_visits }}</strong>
                <small>visits</small>
              </td>
              <td>
                @if($patient->next_appointment)
                  <span class="status-badge status-active">
                    {{ \Carbon\Carbon::parse($patient->next_appointment->appointment_date)->format('d/m/Y') }}
                    <br><small>{{ \Carbon\Carbon::parse($patient->next_appointment->appointment_time)->format('h:i A') }}</small>
                  </span>
                @else
                  <span class="no-data">None scheduled</span>
                @endif
              </td>
              <td class="actions">
                <a href="{{ route('doctor.patients.show', $patient->patient_id) }}" class="btn-view">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                  View Profile
                </a>
              </td>
            </tr>
            @empty
            <tr>
              <td colspan="9" class="no-data-row">
                <div class="empty-state">
                  <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                  </svg>
                  <p>No patients found</p>
                  @if(request()->hasAny(['search', 'gender', 'sort', 'status']))
                    <small>Try adjusting your filters</small>
                  @else
                    <small>Patients will appear here after you complete appointments with them</small>
                  @endif
                </div>
              </td>
            </tr>
            @endforelse
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="pagination-container">
        {{ $patients->links() }}
      </div>
    </div>

    <!-- ✅ UPDATED: Info Box -->
    <div class="info-box">
      <h3>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="16" x2="12" y2="12"></line>
          <line x1="12" y1="8" x2="12.01" y2="8"></line>
        </svg>
        Patient Access & Privacy Guidelines
      </h3>
      <ul>
        <li><strong>Active Care:</strong> Patients with upcoming appointments or seen within last 30 days</li>
        <li><strong>Follow-up Needed:</strong> Patients seen 30-90 days ago without scheduled follow-up</li>
        <li><strong>Historical Records:</strong> Patients treated within last 2 years (medical record retention period)</li>
        <li><strong>Access Restriction:</strong> You can only view patients YOU have treated (not other doctors' patients)</li>
        <li><strong>Data Protection:</strong> Patient demographics cannot be modified (GDPR/HIPAA compliance)</li>
        <li><strong>Clinical Notes:</strong> You may add observations and notes to patient medical records</li>
        <li><strong>Continuity of Care:</strong> All appointments, prescriptions, and lab results with this patient are accessible</li>
      </ul>
    </div>
  </div>



</body>
</html>