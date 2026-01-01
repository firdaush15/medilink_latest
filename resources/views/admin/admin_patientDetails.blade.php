<!--admin_patientDetails.blade.php-->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MediLink | Patient Details</title>
    @vite(['resources/css/admin/admin_sidebar.css', 'resources/css/admin/admin_patientDetails.css'])
</head>

<body>

    {{-- Sidebar --}}
    @include('admin.sidebar.admin_sidebar')

    <div class="main">
        <button id="back-btn" class="back-btn">← Back to Patients</button>

        <script>
            document.getElementById('back-btn').onclick = function() {
                window.location.href = "{{ route('admin.patients') }}";
            };
        </script>



        <h1>Patient Profile: {{ $patient->user->name ?? 'N/A' }}</h1>

        {{-- Patient Info --}}
        <div class="section">
            <h2>👤 Patient Information</h2>
            <table class="details-table">
                <tr>
                    <td>Patient ID:</td>
                    <td>{{ $patient->patient_id }}</td>
                </tr>
                <tr>
                    <td>Full Name:</td>
                    <td>{{ $patient->user->name }}</td>
                </tr>
                <tr>
                    <td>Gender:</td>
                    <td>{{ $patient->gender }}</td>
                </tr>
                <tr>
                    <td>Age:</td>
                    <td>{{ $patient->age ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Date of Birth:</td>
                    <td>{{ $patient->date_of_birth ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Phone Number:</td>
                    <td>{{ $patient->phone_number }}</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>{{ $patient->user->email }}</td>
                </tr>
                <tr>
                    <td>Address:</td>
                    <td>{{ $patient->user->address ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        {{-- Medical Records --}}
        <div class="section">
            <h2>📋 Medical Records</h2>
            @if($medicalRecords->isEmpty())
            <p>No medical records found.</p>
            @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Record Type</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>File</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($medicalRecords as $record)
                    <tr>
                        <td>{{ $record->record_date }}</td>
                        <td>{{ $record->doctor->user->name ?? 'N/A' }} ({{ $record->doctor->specialization ?? 'General' }})</td>
                        <td>{{ $record->record_type }}</td>
                        <td>{{ $record->record_title }}</td>
                        <td>{{ $record->description }}</td>
                        <td>
                            @if($record->file_path)
                            <a href="{{ asset('storage/'.$record->file_path) }}" target="_blank">View File</a>
                            @else
                            N/A
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>

        {{-- Prescriptions --}}
        <div class="section">
            <h2>💊 Prescriptions</h2>
            @if($prescriptions->isEmpty())
            <p>No prescriptions found.</p>
            @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Doctor</th>
                        <th>Medicine</th>
                        <th>Dosage</th>
                        <th>Frequency</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($prescriptions as $prescription)
                    @foreach($prescription->items as $item)
                    <tr>
                        <td>{{ $prescription->prescribed_date }}</td>
                        <td>{{ $prescription->doctor->user->name ?? 'N/A' }}</td>
                        <td>{{ $item->medicine_name }}</td>
                        <td>{{ $item->dosage }}</td>
                        <td>{{ $item->frequency }}</td>
                    </tr>
                    @endforeach
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

</body>

</html>