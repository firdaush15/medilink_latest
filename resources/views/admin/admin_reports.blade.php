{{-- resources/views/admin/admin_reports.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediLink – Reports & Analytics</title>
    @vite(['resources/css/sidebar.css', 'resources/css/admin/admin_reports.css'])
</head>

<body>
@include('admin.sidebar.admin_sidebar')

<div class="main">

    {{-- ══════════════════════════════════════════
         PAGE HEADER + DATE FILTER
    ══════════════════════════════════════════ --}}
    <div class="reports-header">
        <div class="reports-title">
            <h1>Reports &amp; Analytics</h1>
            <p>Overview of clinic performance across all departments</p>
        </div>

        <form method="GET" action="{{ route('admin.reports') }}" class="filter-bar">
            <div class="filter-group">
                <label for="from">From</label>
                <input type="date" id="from" name="from"
                       value="{{ request('from', $defaultFrom) }}">
            </div>
            <div class="filter-group">
                <label for="to">To</label>
                <input type="date" id="to" name="to"
                       value="{{ request('to', $defaultTo) }}">
            </div>
            <button type="submit" class="btn-filter">Apply</button>
            <a href="{{ route('admin.reports') }}" class="btn-reset">Reset</a>
        </form>
    </div>

    {{-- ══════════════════════════════════════════
         KPI CARDS
    ══════════════════════════════════════════ --}}
    <div class="kpi-grid">

        <div class="kpi-card kpi-blue">
            <div class="kpi-icon">📅</div>
            <div class="kpi-body">
                <span class="kpi-label">Total Appointments</span>
                <span class="kpi-value">{{ number_format($totalAppointments) }}</span>
                <span class="kpi-sub">{{ $completedAppointments }} completed · {{ $cancelledAppointments }} cancelled</span>
            </div>
        </div>

        <div class="kpi-card kpi-green">
            <div class="kpi-icon">🧑‍🦱</div>
            <div class="kpi-body">
                <span class="kpi-label">Total Patients</span>
                <span class="kpi-value">{{ number_format($totalPatients) }}</span>
                <span class="kpi-sub">+{{ $newPatients }} new in period</span>
            </div>
        </div>

        <div class="kpi-card kpi-purple">
            <div class="kpi-icon">👨‍⚕️</div>
            <div class="kpi-body">
                <span class="kpi-label">Active Doctors</span>
                <span class="kpi-value">{{ $activeDoctors }}</span>
                <span class="kpi-sub">{{ $totalDoctors }} total on record</span>
            </div>
        </div>

        <div class="kpi-card kpi-orange">
            <div class="kpi-icon">💊</div>
            <div class="kpi-body">
                <span class="kpi-label">Prescriptions Issued</span>
                <span class="kpi-value">{{ number_format($totalPrescriptions) }}</span>
                <span class="kpi-sub">{{ $dispensedPrescriptions }} dispensed</span>
            </div>
        </div>

        <div class="kpi-card kpi-red">
            <div class="kpi-icon">⚠️</div>
            <div class="kpi-body">
                <span class="kpi-label">Low / Out-of-Stock</span>
                <span class="kpi-value">{{ $lowStockCount }}</span>
                <span class="kpi-sub">{{ $outOfStockCount }} fully out of stock</span>
            </div>
        </div>

        <div class="kpi-card kpi-teal">
            <div class="kpi-icon">🏖️</div>
            <div class="kpi-body">
                <span class="kpi-label">Pending Leave Requests</span>
                <span class="kpi-value">{{ $pendingLeaves }}</span>
                <span class="kpi-sub">{{ $approvedLeaves }} approved in period</span>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 1 — APPOINTMENT BREAKDOWN + QUICK STATS
    ══════════════════════════════════════════ --}}
    <div class="reports-row">

        <div class="section section-wide">
            <div class="section-head">
                <h3>Appointment Status Breakdown</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Count</th>
                        <th>% of Total</th>
                        <th>Visual</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointmentStatusBreakdown as $row)
                        @php
                            $pct = $totalAppointments > 0
                                ? round(($row->count / $totalAppointments) * 100, 1)
                                : 0;
                        @endphp
                        <tr>
                            <td>
                                <span class="status-dot status-{{ Str::slug($row->status) }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                            </td>
                            <td>{{ number_format($row->count) }}</td>
                            <td>{{ $pct }}%</td>
                            <td>
                                <div class="bar-track">
                                    <div class="bar-fill bar-{{ Str::slug($row->status) }}"
                                         style="width: {{ $pct }}%"></div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-row">No appointment data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section section-narrow">
            <div class="section-head">
                <h3>No-Show &amp; Late Patients</h3>
            </div>
            <div class="stat-list">
                <div class="stat-row">
                    <span class="stat-label">No-Shows (period)</span>
                    <span class="stat-value warn">{{ $noShowCount }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Late Arrivals (period)</span>
                    <span class="stat-value">{{ $lateArrivalCount }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Flagged Patients</span>
                    <span class="stat-value warn">{{ $flaggedPatients }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Walk-Ins (period)</span>
                    <span class="stat-value">{{ $walkInCount }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Avg Consultation (min)</span>
                    <span class="stat-value good">{{ $avgConsultationMinutes }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 2 — TOP DOCTORS + BY SPECIALISATION
    ══════════════════════════════════════════ --}}
    <div class="reports-row">

        <div class="section section-wide">
            <div class="section-head">
                <h3>Top Doctors by Appointments</h3>
                <a href="{{ route('admin.doctors') }}" class="section-link">View All →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Doctor</th>
                        <th>Specialisation</th>
                        <th>Appointments</th>
                        <th>Completed</th>
                        <th>Completion Rate</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDoctors as $i => $doc)
                        @php
                            $rate = $doc->total > 0
                                ? round(($doc->completed / $doc->total) * 100, 1)
                                : 0;
                        @endphp
                        <tr>
                            <td class="rank">{{ $i + 1 }}</td>
                            <td>{{ $doc->name }}</td>
                            <td>{{ $doc->specialization }}</td>
                            <td>{{ $doc->total }}</td>
                            <td>{{ $doc->completed }}</td>
                            <td>
                                <div class="bar-track">
                                    <div class="bar-fill bar-completed"
                                         style="width: {{ $rate }}%"></div>
                                </div>
                                <small>{{ $rate }}%</small>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty-row">No data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section section-narrow">
            <div class="section-head">
                <h3>By Specialisation</h3>
            </div>
            <div class="stat-list">
                @forelse($appointmentsBySpecialisation as $spec)
                    <div class="stat-row">
                        <span class="stat-label">{{ $spec->specialization }}</span>
                        <span class="stat-value">{{ $spec->count }}</span>
                    </div>
                @empty
                    <div class="stat-row">
                        <span class="stat-label">No data for this period</span>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 3 — PHARMACY INVENTORY + RESTOCK
    ══════════════════════════════════════════ --}}
    <div class="reports-row">

        <div class="section section-half">
            <div class="section-head">
                <h3>Pharmacy Inventory Summary</h3>
                <a href="{{ route('admin.pharmacy-inventory.index') }}" class="section-link">View All →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Total</th>
                        <th>Low Stock</th>
                        <th>Out of Stock</th>
                        <th>Expiring ≤ 90 days</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pharmacyByCategory as $cat)
                        <tr>
                            <td>{{ $cat->category }}</td>
                            <td>{{ $cat->total }}</td>
                            <td class="{{ $cat->low_stock > 0 ? 'text-warn' : '' }}">{{ $cat->low_stock }}</td>
                            <td class="{{ $cat->out_of_stock > 0 ? 'text-danger' : '' }}">{{ $cat->out_of_stock }}</td>
                            <td class="{{ $cat->expiring_soon > 0 ? 'text-warn' : '' }}">{{ $cat->expiring_soon }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty-row">No inventory data</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section section-half">
            <div class="section-head">
                <h3>Restock Requests</h3>
                <a href="{{ route('admin.restock.index') }}" class="section-link">View All →</a>
            </div>
            <div class="stat-list">
                <div class="stat-row">
                    <span class="stat-label">Pending Approval</span>
                    <span class="stat-value warn">{{ $restockPending }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Approved</span>
                    <span class="stat-value good">{{ $restockApproved }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Ordered</span>
                    <span class="stat-value">{{ $restockOrdered }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Received (period)</span>
                    <span class="stat-value good">{{ $restockReceived }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Rejected (period)</span>
                    <span class="stat-value warn">{{ $restockRejected }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Total Disposals (period)</span>
                    <span class="stat-value">{{ $totalDisposals }}</span>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 4 — STAFF LEAVE + PATIENT DEMOGRAPHICS
    ══════════════════════════════════════════ --}}
    <div class="reports-row">

        <div class="section section-half">
            <div class="section-head">
                <h3>Staff Leave Summary</h3>
                <a href="{{ route('admin.leaves.index') }}" class="section-link">View All →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Role</th>
                        <th>Pending</th>
                        <th>Approved</th>
                        <th>Rejected</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($leaveByRole as $leave)
                        <tr>
                            <td>{{ ucfirst($leave->staff_role) }}</td>
                            <td class="{{ $leave->pending > 0 ? 'text-warn' : '' }}">{{ $leave->pending }}</td>
                            <td class="text-good">{{ $leave->approved }}</td>
                            <td class="{{ $leave->rejected > 0 ? 'text-danger' : '' }}">{{ $leave->rejected }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-row">No leave data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section section-half">
            <div class="section-head">
                <h3>Patient Demographics</h3>
            </div>
            <div class="demo-grid">
                <div class="demo-block">
                    <h4>Gender</h4>
                    <div class="stat-list">
                        @forelse($patientsByGender as $g)
                            <div class="stat-row">
                                <span class="stat-label">{{ $g->gender }}</span>
                                <span class="stat-value">{{ $g->count }}</span>
                            </div>
                        @empty
                            <div class="stat-row"><span class="stat-label">No data</span></div>
                        @endforelse
                    </div>
                </div>
                <div class="demo-block">
                    <h4>Blood Type</h4>
                    <div class="stat-list">
                        @forelse($patientsByBloodType as $b)
                            <div class="stat-row">
                                <span class="stat-label">{{ $b->blood_type }}</span>
                                <span class="stat-value">{{ $b->count }}</span>
                            </div>
                        @empty
                            <div class="stat-row"><span class="stat-label">No data</span></div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════════
         ROW 5 — TOP DIAGNOSES + PRESCRIPTION STATS
    ══════════════════════════════════════════ --}}
    <div class="reports-row">

        <div class="section section-half">
            <div class="section-head">
                <h3>Most Common Diagnoses (Period)</h3>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ICD-10</th>
                        <th>Diagnosis</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topDiagnoses as $d)
                        <tr>
                            <td><code>{{ $d->icd10_code }}</code></td>
                            <td>{{ $d->diagnosis_name }}</td>
                            <td>{{ $d->count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty-row">No diagnosis data for this period</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="section section-half">
            <div class="section-head">
                <h3>Prescription Stats (Period)</h3>
            </div>
            <div class="stat-list">
                <div class="stat-row">
                    <span class="stat-label">Total Issued</span>
                    <span class="stat-value">{{ $totalPrescriptions }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Dispensed</span>
                    <span class="stat-value good">{{ $dispensedPrescriptions }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Pending Verification</span>
                    <span class="stat-value warn">{{ $pendingPrescriptions }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Rejected</span>
                    <span class="stat-value warn">{{ $rejectedPrescriptions }}</span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Dispense Rate</span>
                    <span class="stat-value good">
                        {{ $totalPrescriptions > 0
                            ? round(($dispensedPrescriptions / $totalPrescriptions) * 100, 1)
                            : 0 }}%
                    </span>
                </div>
            </div>
        </div>

    </div>

</div>{{-- /.main --}}

@vite(['resources/js/sidebar.js'])
</body>
</html>