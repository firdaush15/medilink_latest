{{-- resources/views/doctor/doctor_reports.blade.php --}}
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | Advanced Analytics</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  @vite(['resources/css/doctor/doctor_sidebar.css', 'resources/css/doctor/doctor_reports.css'])
  <style>
    .no-data-message {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      text-align: center;
      color: #6b7280;
      min-height: 250px;
    }

    .no-data-message .icon {
      font-size: 48px;
      margin-bottom: 16px;
      opacity: 0.5;
    }

    .no-data-message h4 {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 8px;
      color: #374151;
    }

    .no-data-message p {
      font-size: 14px;
      color: #9ca3af;
    }
  </style>
</head>

<body>

  @include('doctor.sidebar.doctor_sidebar')

  <div class="main">
    <!-- Header with Filters -->
    <div class="header">
      <div class="title-section">
        <h1 class="page-title">📊 Advanced Analytics Dashboard</h1>
        <p class="subtitle">Comprehensive insights into your medical practice</p>
      </div>

      <div class="filter-buttons">
        <a href="{{ route('doctor.reports', ['time_filter' => 'week']) }}"
          class="filter-btn {{ $timeFilter == 'week' ? 'active' : '' }}">
          <span>📅</span> This Week
        </a>
        <a href="{{ route('doctor.reports', ['time_filter' => 'month']) }}"
          class="filter-btn {{ $timeFilter == 'month' ? 'active' : '' }}">
          <span>📆</span> This Month
        </a>
        <a href="{{ route('doctor.reports', ['time_filter' => 'year']) }}"
          class="filter-btn {{ $timeFilter == 'year' ? 'active' : '' }}">
          <span>📊</span> This Year
        </a>
      </div>
    </div>

    <!-- KPI Cards with Trends -->
    <div class="kpi-grid">
      <div class="kpi-card gradient-blue">
        <div class="kpi-header">
          <div class="kpi-icon">👥</div>
          <div class="kpi-trend {{ $changes['total_appointments'] >= 0 ? 'up' : 'down' }}">
            {{ $changes['total_appointments'] >= 0 ? '↑' : '↓' }} {{ abs($changes['total_appointments']) }}%
          </div>
        </div>
        <div class="kpi-body">
          <h3>Total Appointments</h3>
          <p class="kpi-value">{{ $currentStats['total_appointments'] }}</p>
          <small>vs previous period</small>
        </div>
      </div>

      <div class="kpi-card gradient-green">
        <div class="kpi-header">
          <div class="kpi-icon">✅</div>
          <div class="kpi-trend {{ $changes['completed_appointments'] >= 0 ? 'up' : 'down' }}">
            {{ $changes['completed_appointments'] >= 0 ? '↑' : '↓' }} {{ abs($changes['completed_appointments']) }}%
          </div>
        </div>
        <div class="kpi-body">
          <h3>Completed</h3>
          <p class="kpi-value">{{ $currentStats['completed_appointments'] }}</p>
          <small>{{ $currentStats['total_appointments'] > 0 ? round(($currentStats['completed_appointments']/$currentStats['total_appointments'])*100, 1) : 0 }}% completion rate</small>
        </div>
      </div>

      <div class="kpi-card gradient-purple">
        <div class="kpi-header">
          <div class="kpi-icon">📋</div>
          <div class="kpi-trend {{ $changes['medical_records'] >= 0 ? 'up' : 'down' }}">
            {{ $changes['medical_records'] >= 0 ? '↑' : '↓' }} {{ abs($changes['medical_records']) }}%
          </div>
        </div>
        <div class="kpi-body">
          <h3>Medical Records</h3>
          <p class="kpi-value">{{ $currentStats['medical_records'] }}</p>
          <small>{{ $documentationRate }}% documentation rate</small>
        </div>
      </div>

      <div class="kpi-card gradient-pink">
        <div class="kpi-header">
          <div class="kpi-icon">💊</div>
          <div class="kpi-trend {{ $changes['prescriptions'] >= 0 ? 'up' : 'down' }}">
            {{ $changes['prescriptions'] >= 0 ? '↑' : '↓' }} {{ abs($changes['prescriptions']) }}%
          </div>
        </div>
        <div class="kpi-body">
          <h3>Prescriptions</h3>
          <p class="kpi-value">{{ $currentStats['prescriptions'] }}</p>
          <small>{{ $prescriptionRate }}% prescription rate</small>
        </div>
      </div>

      <div class="kpi-card gradient-yellow">
        <div class="kpi-header">
          <div class="kpi-icon">⭐</div>
          <div class="kpi-info">{{ $totalRatings }} ratings</div>
        </div>
        <div class="kpi-body">
          <h3>Average Rating</h3>
          <p class="kpi-value">{{ number_format($averageRating, 1) }}/5.0</p>
          <div class="star-display">
            @for($i = 1; $i <= 5; $i++)
              <span class="{{ $i <= round($averageRating) ? 'star-filled' : 'star-empty' }}">★</span>
              @endfor
          </div>
        </div>
      </div>

      <div class="kpi-card gradient-orange">
        <div class="kpi-header">
          <div class="kpi-icon">🚫</div>
          <div class="kpi-info">{{ $currentStats['cancelled_appointments'] }} cancelled</div>
        </div>
        <div class="kpi-body">
          <h3>Cancellation Rate</h3>
          <p class="kpi-value">{{ $cancellationRate }}%</p>
          <small>Lower is better</small>
        </div>
      </div>
    </div>

    <!-- Quick Insights Row -->
    <div class="insights-row">
      <div class="insight-card">
        <div class="insight-icon">🆕</div>
        <div class="insight-content">
          <h4>New Patients</h4>
          <p class="insight-value">{{ $newPatients }}</p>
          <small>First-time visitors</small>
        </div>
      </div>

      <div class="insight-card">
        <div class="insight-icon">🔄</div>
        <div class="insight-content">
          <h4>Returning Patients</h4>
          <p class="insight-value">{{ $returningPatients }}</p>
          <small>Repeat visitors</small>
        </div>
      </div>

      <div class="insight-card">
        <div class="insight-icon">📅</div>
        <div class="insight-content">
          <h4>Avg Per Day</h4>
          <p class="insight-value">{{ $avgAppointmentsPerDay }}</p>
          <small>appointments/day</small>
        </div>
      </div>

      <div class="insight-card">
        <div class="insight-icon">📝</div>
        <div class="insight-content">
          <h4>Documentation</h4>
          <p class="insight-value">{{ $documentationRate }}%</p>
          <small>records per visit</small>
        </div>
      </div>

      <div class="insight-card">
        <div class="insight-icon">🧠</div>
        <div class="insight-content">
          <h4>Mental Health Checks</h4>
          <p class="insight-value">{{ $mentalHealthAssessments }}</p>
          <small>{{ $criticalMentalHealth }} need attention</small>
        </div>
      </div>
    </div>

    <!-- Charts Grid -->
    <div class="charts-grid">
      <!-- Appointment Trend (30 days) -->
      <div class="chart-card full-width">
        <div class="chart-header">
          <h3>📈 Appointment Trend (Last 30 Days)</h3>
          <span class="chart-subtitle">Daily appointment volume</span>
        </div>
        <canvas id="appointmentTrendChart"></canvas>
      </div>

      <!-- Status Distribution -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>📊 Appointment Status</h3>
          <span class="chart-subtitle">Distribution by status</span>
        </div>
        <canvas id="statusPieChart"></canvas>
      </div>

      <!-- Top Diagnoses -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>🩺 Top Medical Records</h3>
          <span class="chart-subtitle">Most common record types</span>
        </div>
        @if($topDiagnoses->count() > 0)
        <canvas id="diagnosesChart"></canvas>
        @else
        <div class="no-data-message">
          <div class="icon">📋</div>
          <h4>No Medical Records Yet</h4>
          <p>Medical records data will appear here once you start documenting patient visits</p>
        </div>
        @endif
      </div>

      <!-- Hourly Activity -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>⏰ Hourly Activity Pattern</h3>
          <span class="chart-subtitle">Busiest hours of the day</span>
        </div>
        <canvas id="hourlyChart"></canvas>
      </div>

      <!-- Top Medications -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>💊 Most Prescribed Medications</h3>
          <span class="chart-subtitle">Top 7 prescriptions</span>
        </div>
        @if($topMedications->count() > 0)
        <canvas id="medicationsChart"></canvas>
        @else
        <div class="no-data-message">
          <div class="icon">💊</div>
          <h4>No Prescriptions Yet</h4>
          <p>Prescription data will appear here once you start prescribing medications</p>
        </div>
        @endif
      </div>

      <!-- Age Distribution -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>👤 Patient Age Distribution</h3>
          <span class="chart-subtitle">By age group</span>
        </div>
        @if($ageDistribution->count() > 0)
        <canvas id="ageChart"></canvas>
        @else
        <div class="no-data-message">
          <div class="icon">👥</div>
          <h4>No Patient Data</h4>
          <p>Age distribution will appear once you have appointments with patients</p>
        </div>
        @endif
      </div>

      <!-- Gender Distribution -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>⚤ Gender Distribution</h3>
          <span class="chart-subtitle">Patient demographics</span>
        </div>
        @if($genderDistribution->count() > 0)
        <canvas id="genderChart"></canvas>
        @else
        <div class="no-data-message">
          <div class="icon">⚤</div>
          <h4>No Patient Data</h4>
          <p>Gender distribution will appear once you have appointments with patients</p>
        </div>
        @endif
      </div>

      <!-- Disease Trend Analysis -->
      <div class="chart-card full-width">
        <div class="chart-header">
          <h3>🦠 Disease Trend Analysis</h3>
          <span class="chart-subtitle">Most diagnosed conditions over time</span>
        </div>
        @if($diseaseTrend->count() > 0)
        <canvas id="diseaseTrendChart"></canvas>
        
        <!-- Disease Statistics Table -->
        <div style="margin-top: 25px; overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 12px; text-align: left;">Disease</th>
                <th style="padding: 12px; text-align: center;">ICD-10</th>
                <th style="padding: 12px; text-align: center;">Cases</th>
                <th style="padding: 12px; text-align: center;">Trend</th>
                <th style="padding: 12px; text-align: center;">Category</th>
                <th style="padding: 12px; text-align: center;">Infectious</th>
              </tr>
            </thead>
            <tbody>
              @foreach($diseaseTrend as $disease)
              <tr style="border-bottom: 1px solid #e9ecef;">
                <td style="padding: 12px; font-weight: 600;">{{ $disease->diagnosis_name }}</td>
                <td style="padding: 12px; text-align: center;">
                  <code style="background: #e0e8f0; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                    {{ $disease->icd10_code }}
                  </code>
                </td>
                <td style="padding: 12px; text-align: center; font-weight: 700; color: #4f46e5;">
                  {{ $disease->total_cases }}
                </td>
                <td style="padding: 12px; text-align: center;">
                  @if($disease->trend_change > 0)
                    <span style="color: #ef4444;">↑ {{ $disease->trend_change }}%</span>
                  @elseif($disease->trend_change < 0)
                    <span style="color: #16a34a;">↓ {{ abs($disease->trend_change) }}%</span>
                  @else
                    <span style="color: #6b7280;">→ Stable</span>
                  @endif
                </td>
                <td style="padding: 12px; text-align: center;">
                  <span style="background: #f0f9ff; padding: 4px 10px; border-radius: 12px; font-size: 12px;">
                    {{ $disease->category }}
                  </span>
                </td>
                <td style="padding: 12px; text-align: center;">
                  @if($disease->is_infectious)
                    <span style="color: #ef4444;">⚠️ Yes</span>
                  @else
                    <span style="color: #6b7280;">No</span>
                  @endif
                </td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        @else
        <div class="no-data-message">
          <div class="icon">🦠</div>
          <h4>No Diagnosis Data Yet</h4>
          <p>Disease trends will appear once you start recording diagnoses during consultations</p>
        </div>
        @endif
      </div>

      <!-- Top Infectious Diseases Alert -->
      @if($infectiousDiseasesCount > 0)
      <div class="chart-card">
        <div class="chart-header">
          <h3>⚠️ Infectious Disease Alert</h3>
          <span class="chart-subtitle">Cases requiring public health attention</span>
        </div>
        <div style="padding: 20px;">
          <div style="background: #fef2f2; border: 2px solid #ef4444; border-radius: 12px; padding: 20px; margin-bottom: 15px;">
            <h4 style="margin: 0 0 10px 0; color: #b91c1c;">🚨 {{ $infectiousDiseasesCount }} Infectious Disease Cases</h4>
            <p style="margin: 0; color: #6b7280;">Monitor for potential outbreaks and ensure proper infection control measures</p>
          </div>
          
          <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach($topInfectiousDiseases as $disease)
            <li style="padding: 12px; background: #f9fafb; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong>{{ $disease->diagnosis_name }}</strong>
                <small style="color: #6b7280; display: block;">{{ $disease->category }}</small>
              </div>
              <span style="background: #ef4444; color: white; padding: 4px 12px; border-radius: 12px; font-weight: 700;">
                {{ $disease->count }} cases
              </span>
            </li>
            @endforeach
          </ul>
        </div>
      </div>
      @endif

      <!-- Seasonal Disease Patterns -->
      <div class="chart-card">
        <div class="chart-header">
          <h3>📅 Seasonal Disease Patterns</h3>
          <span class="chart-subtitle">Monthly diagnosis frequency</span>
        </div>
        @if($seasonalPattern->count() > 0)
        <canvas id="seasonalPatternChart"></canvas>
        @else
        <div class="no-data-message">
          <div class="icon">📅</div>
          <h4>No Seasonal Data Yet</h4>
          <p>Seasonal patterns will appear after accumulating diagnosis data over several months</p>
        </div>
        @endif
      </div>

    </div>

    <!-- Top Engaged Patients -->
    <div class="section-card">
      <div class="section-header">
        <h3>🏆 Most Engaged Patients</h3>
        <p>Patients with highest appointment completion rates (min. 3 appointments)</p>
      </div>
      <div class="patients-grid">
        @forelse($topEngagedPatients as $patient)
        <div class="patient-badge">
          <div class="patient-avatar">{{ substr($patient->user->name, 0, 1) }}</div>
          <div class="patient-info">
            <h4>{{ $patient->user->name }}</h4>
            <p>{{ $patient->completed_appointments }}/{{ $patient->total_appointments }} completed</p>
            <div class="progress-bar">
              <div class="progress-fill" style="width: {{ $patient->completion_rate }}%"></div>
            </div>
            <span class="completion-badge">{{ $patient->completion_rate }}%</span>
          </div>
          <a href="{{ route('doctor.patients.show', $patient->patient_id) }}" class="view-link">→</a>
        </div>
        @empty
        <p class="no-data">No patients with sufficient appointment history yet</p>
        @endforelse
      </div>
    </div>

    <!-- Recent Ratings -->
    @if($recentRatings->count() > 0)
    <div class="section-card">
      <div class="section-header">
        <h3>⭐ Recent Patient Feedback</h3>
        <p>Latest ratings from your patients</p>
      </div>
      <div class="ratings-list">
        @foreach($recentRatings as $rating)
        <div class="rating-item">
          <div class="rating-header">
            <div class="rating-patient">
              <div class="patient-avatar-small">{{ substr($rating->patient->user->name, 0, 1) }}</div>
              <div>
                <strong>{{ $rating->patient->user->name }}</strong>
                <small>{{ $rating->created_at->diffForHumans() }}</small>
              </div>
            </div>
            <div class="rating-stars">
              @for($i = 1; $i <= 5; $i++)
                <span class="{{ $i <= $rating->rating ? 'star-filled' : 'star-empty' }}">★</span>
                @endfor
            </div>
          </div>
          @if($rating->comment)
          <p class="rating-comment">"{{ $rating->comment }}"</p>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @endif

    <!-- Rating Distribution -->
    <div class="section-card">
      <div class="section-header">
        <h3>📊 Rating Distribution</h3>
        <p>Breakdown of all ratings received</p>
      </div>
      <div class="rating-bars">
        @foreach([5,4,3,2,1] as $star)
        @php
        $count = $ratingDistribution->where('rating', $star)->first()->count ?? 0;
        $percentage = $totalRatings > 0 ? round(($count / $totalRatings) * 100) : 0;
        @endphp
        <div class="rating-bar-item">
          <span class="rating-label">{{ $star }} ⭐</span>
          <div class="rating-bar-container">
            <div class="rating-bar-fill" style="width: {{ $percentage }}%"></div>
          </div>
          <span class="rating-count">{{ $count }}</span>
        </div>
        @endforeach
      </div>
    </div>

  </div>

  <script>
    // Prepare data
    const appointmentTrend = @json($appointmentTrend);
    const statusDistribution = @json($statusDistribution);
    const topDiagnoses = @json($topDiagnoses);
    const topMedications = @json($topMedications);
    const hourlyActivity = @json($hourlyActivity);
    const ageDistribution = @json($ageDistribution);
    const genderDistribution = @json($genderDistribution);

    // Chart 1: Appointment Trend (30 days)
    new Chart(document.getElementById('appointmentTrendChart'), {
      type: 'line',
      data: {
        labels: appointmentTrend.map(d => d.date),
        datasets: [{
          label: 'Appointments',
          data: appointmentTrend.map(d => d.count),
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          borderWidth: 3,
          fill: true,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // Chart 2: Status Pie
    new Chart(document.getElementById('statusPieChart'), {
      type: 'doughnut',
      data: {
        labels: statusDistribution.map(s => s.status),
        datasets: [{
          data: statusDistribution.map(s => s.count),
          backgroundColor: statusDistribution.map(s => s.color),
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });

    // Chart 3: Top Diagnoses (only if data exists)
    @if($topDiagnoses->count() > 0)
    new Chart(document.getElementById('diagnosesChart'), {
      type: 'bar',
      data: {
        labels: topDiagnoses.map(d => d.record_type),
        datasets: [{
          label: 'Count',
          data: topDiagnoses.map(d => d.count),
          backgroundColor: ['#4f46e5', '#7b3ff8', '#f472b6', '#facc15', '#16a34a'],
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
    @endif

    // Chart 4: Hourly Activity
    new Chart(document.getElementById('hourlyChart'), {
      type: 'bar',
      data: {
        labels: hourlyActivity.map(h => `${h.hour}:00`),
        datasets: [{
          label: 'Appointments',
          data: hourlyActivity.map(h => h.count),
          backgroundColor: '#16a34a',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });

    // Chart 5: Top Medications (only if data exists)
    @if($topMedications->count() > 0)
    new Chart(document.getElementById('medicationsChart'), {
      type: 'bar',
      data: {
        labels: topMedications.map(m => m.medicine_name),
        datasets: [{
          label: 'Prescriptions',
          data: topMedications.map(m => m.count),
          backgroundColor: '#f472b6',
          borderRadius: 8
        }]
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          x: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
    @endif

    // Chart 6: Age Distribution (only if data exists)
    @if($ageDistribution->count() > 0)
    new Chart(document.getElementById('ageChart'), {
      type: 'bar',
      data: {
        labels: ageDistribution.map(a => a.age_group),
        datasets: [{
          label: 'Patients',
          data: ageDistribution.map(a => a.count),
          backgroundColor: ['#4f46e5', '#7b3ff8', '#f472b6', '#facc15', '#16a34a'],
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
    @endif

    // Chart 7: Gender Distribution (only if data exists)
    @if($genderDistribution->count() > 0)
    new Chart(document.getElementById('genderChart'), {
      type: 'pie',
      data: {
        labels: genderDistribution.map(g => g.gender),
        datasets: [{
          data: genderDistribution.map(g => g.count),
          backgroundColor: ['#4f46e5', '#f472b6', '#facc15'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom'
          }
        }
      }
    });
    @endif

    // ========================================
    // DISEASE TREND CHART (Line Chart)
    // ========================================
    @if($diseaseTrend->count() > 0)
    const diseaseTrendData = @json($diseaseTrendTimeline);

    new Chart(document.getElementById('diseaseTrendChart'), {
      type: 'line',
      data: {
        labels: diseaseTrendData.labels,
        datasets: diseaseTrendData.datasets.map((dataset, index) => ({
          label: dataset.name,
          data: dataset.data,
          borderColor: dataset.color,
          backgroundColor: dataset.color + '20',
          borderWidth: 3,
          fill: false,
          tension: 0.4,
          pointRadius: 4,
          pointHoverRadius: 6
        }))
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12,
              padding: 15
            }
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.dataset.label + ': ' + context.parsed.y + ' cases';
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            },
            title: {
              display: true,
              text: 'Number of Cases'
            }
          },
          x: {
            title: {
              display: true,
              text: 'Date'
            }
          }
        }
      }
    });
    @endif

    // ========================================
    // SEASONAL PATTERN CHART (Bar Chart)
    // ========================================
    @if($seasonalPattern->count() > 0)
    const seasonalPattern = @json($seasonalPattern);

    new Chart(document.getElementById('seasonalPatternChart'), {
      type: 'bar',
      data: {
        labels: seasonalPattern.map(s => s.month_name),
        datasets: [{
          label: 'Total Diagnoses',
          data: seasonalPattern.map(s => s.total),
          backgroundColor: '#4f46e5',
          borderRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
    @endif
  </script>

</body>

</html>