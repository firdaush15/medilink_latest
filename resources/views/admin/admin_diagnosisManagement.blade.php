<!-- ========================================
     ADMIN DIAGNOSIS MANAGEMENT PAGE
     resources/views/admin/diagnosis_management.blade.php
     ======================================== -->

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediLink | Diagnosis Management</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #f5f7fa;
      padding: 20px;
    }

    .container {
      max-width: 1400px;
      margin: 0 auto;
    }

    .header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px;
      border-radius: 16px;
      margin-bottom: 30px;
      box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }

    .header h1 {
      font-size: 32px;
      margin-bottom: 8px;
    }

    .header p {
      opacity: 0.9;
      font-size: 16px;
    }

    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }

    .stat-card {
      background: white;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .stat-card .icon {
      font-size: 32px;
      margin-bottom: 10px;
    }

    .stat-card .label {
      color: #6b7280;
      font-size: 14px;
      margin-bottom: 8px;
    }

    .stat-card .value {
      font-size: 28px;
      font-weight: 700;
      color: #1f2937;
    }

    .actions-bar {
      background: white;
      padding: 20px;
      border-radius: 12px;
      margin-bottom: 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 15px;
      flex-wrap: wrap;
    }

    .search-box {
      flex: 1;
      min-width: 300px;
      position: relative;
    }

    .search-box input {
      width: 100%;
      padding: 12px 45px 12px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s;
    }

    .search-box input:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .search-icon {
      position: absolute;
      right: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
    }

    .filter-group {
      display: flex;
      gap: 10px;
      align-items: center;
    }

    .filter-group select {
      padding: 10px 15px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 14px;
      cursor: pointer;
    }

    .btn-primary {
      background: #667eea;
      color: white;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: all 0.3s;
    }

    .btn-primary:hover {
      background: #5568d3;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .table-container {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    thead {
      background: #f9fafb;
      border-bottom: 2px solid #e5e7eb;
    }

    th {
      padding: 16px;
      text-align: left;
      font-weight: 600;
      color: #374151;
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    tbody tr {
      border-bottom: 1px solid #f3f4f6;
      transition: background-color 0.2s;
    }

    tbody tr:hover {
      background: #f9fafb;
    }

    td {
      padding: 16px;
      color: #4b5563;
    }

    .icd-code {
      font-family: 'Courier New', monospace;
      background: #e0e8f0;
      padding: 4px 10px;
      border-radius: 6px;
      font-weight: 600;
      color: #1e40af;
      font-size: 13px;
    }

    .badge {
      display: inline-block;
      padding: 5px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    .badge-minor { background: #d1fae5; color: #065f46; }
    .badge-moderate { background: #fef3c7; color: #92400e; }
    .badge-severe { background: #fee2e2; color: #991b1b; }
    .badge-critical { background: #991b1b; color: white; }

    .status-badge {
      padding: 5px 12px;
      border-radius: 12px;
      font-size: 12px;
      font-weight: 600;
    }

    .status-active { background: #d1fae5; color: #065f46; }
    .status-inactive { background: #f3f4f6; color: #6b7280; }

    .tag {
      display: inline-block;
      padding: 3px 8px;
      background: #ede9fe;
      color: #6b21a8;
      border-radius: 6px;
      font-size: 11px;
      margin-right: 5px;
    }

    .tag.infectious {
      background: #fee2e2;
      color: #991b1b;
    }

    .tag.chronic {
      background: #fef3c7;
      color: #92400e;
    }

    .actions {
      display: flex;
      gap: 8px;
    }

    .btn-edit, .btn-delete, .btn-toggle {
      padding: 6px 12px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 13px;
      font-weight: 500;
      transition: all 0.2s;
    }

    .btn-edit {
      background: #dbeafe;
      color: #1e40af;
    }

    .btn-edit:hover {
      background: #bfdbfe;
    }

    .btn-toggle {
      background: #fef3c7;
      color: #92400e;
    }

    .btn-toggle:hover {
      background: #fde68a;
    }

    .btn-delete {
      background: #fee2e2;
      color: #991b1b;
    }

    .btn-delete:hover {
      background: #fecaca;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      align-items: center;
      justify-content: center;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: white;
      border-radius: 16px;
      padding: 30px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
    }

    .modal-header h2 {
      font-size: 24px;
      color: #1f2937;
    }

    .close-modal {
      font-size: 28px;
      cursor: pointer;
      color: #9ca3af;
      background: none;
      border: none;
      padding: 0;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      transition: all 0.2s;
    }

    .close-modal:hover {
      background: #f3f4f6;
      color: #374151;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: #374151;
      font-size: 14px;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px;
      border: 2px solid #e5e7eb;
      border-radius: 8px;
      font-size: 15px;
      transition: all 0.3s;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: #667eea;
      box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-group textarea {
      resize: vertical;
      min-height: 80px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
    }

    .checkbox-group {
      display: flex;
      gap: 20px;
      margin-top: 10px;
    }

    .checkbox-group label {
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 500;
      cursor: pointer;
    }

    .checkbox-group input[type="checkbox"] {
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .form-actions {
      display: flex;
      gap: 10px;
      justify-content: flex-end;
      margin-top: 25px;
      padding-top: 20px;
      border-top: 1px solid #e5e7eb;
    }

    .btn-secondary {
      background: #f3f4f6;
      color: #374151;
      border: none;
      padding: 12px 24px;
      border-radius: 8px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.2s;
    }

    .btn-secondary:hover {
      background: #e5e7eb;
    }

    .required {
      color: #ef4444;
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: #6b7280;
    }

    .empty-state-icon {
      font-size: 64px;
      margin-bottom: 16px;
      opacity: 0.5;
    }

    @media (max-width: 768px) {
      .actions-bar {
        flex-direction: column;
        align-items: stretch;
      }

      .search-box {
        min-width: 100%;
      }

      .form-row {
        grid-template-columns: 1fr;
      }

      table {
        font-size: 13px;
      }

      th, td {
        padding: 10px;
      }
    }
  </style>
</head>
<body>

<div class="container">
  <!-- Header -->
  <div class="header">
    <h1>🩺 Diagnosis Master List Management</h1>
    <p>Manage ICD-10 diagnosis codes available to doctors across the system</p>
  </div>

  <!-- Statistics -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="icon">📋</div>
      <div class="label">Total Diagnoses</div>
      <div class="value">{{ $totalDiagnoses }}</div>
    </div>
    <div class="stat-card">
      <div class="icon">✅</div>
      <div class="label">Active</div>
      <div class="value">{{ $activeDiagnoses }}</div>
    </div>
    <div class="stat-card">
      <div class="icon">🦠</div>
      <div class="label">Infectious Diseases</div>
      <div class="value">{{ $infectiousDiagnoses }}</div>
    </div>
    <div class="stat-card">
      <div class="icon">💊</div>
      <div class="label">Chronic Conditions</div>
      <div class="value">{{ $chronicDiagnoses }}</div>
    </div>
  </div>

  <!-- Actions Bar -->
  <div class="actions-bar">
    <div class="search-box">
      <input type="text" id="searchInput" placeholder="Search by disease name, ICD-10 code, or category...">
      <span class="search-icon">🔍</span>
    </div>
    
    <div class="filter-group">
      <select id="categoryFilter">
        <option value="">All Categories</option>
        <option value="Respiratory Infection">Respiratory Infection</option>
        <option value="Fever & Infection">Fever & Infection</option>
        <option value="Cardiovascular">Cardiovascular</option>
        <option value="Endocrine">Endocrine</option>
        <option value="Gastrointestinal">Gastrointestinal</option>
        <option value="Musculoskeletal">Musculoskeletal</option>
        <option value="Dermatological">Dermatological</option>
        <option value="Mental Health">Mental Health</option>
        <option value="Neurological">Neurological</option>
      </select>

      <select id="statusFilter">
        <option value="">All Status</option>
        <option value="active">Active Only</option>
        <option value="inactive">Inactive Only</option>
      </select>
    </div>

    <button class="btn-primary" onclick="openAddModal()">
      <span>➕</span>
      Add New Diagnosis
    </button>
  </div>

  <!-- Table -->
  <div class="table-container">
    <table id="diagnosisTable">
      <thead>
        <tr>
          <th>ICD-10</th>
          <th>Diagnosis Name</th>
          <th>Category</th>
          <th>Severity</th>
          <th>Properties</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($diagnoses as $diagnosis)
        <tr data-id="{{ $diagnosis->diagnosis_code_id }}">
          <td>
            <span class="icd-code">{{ $diagnosis->icd10_code }}</span>
          </td>
          <td>
            <strong>{{ $diagnosis->diagnosis_name }}</strong>
            @if($diagnosis->generic_name)
            <br><small style="color: #6b7280;">{{ $diagnosis->generic_name }}</small>
            @endif
          </td>
          <td>{{ $diagnosis->category }}</td>
          <td>
            <span class="badge badge-{{ strtolower($diagnosis->severity) }}">
              {{ $diagnosis->severity }}
            </span>
          </td>
          <td>
            @if($diagnosis->is_infectious)
            <span class="tag infectious">🦠 Infectious</span>
            @endif
            @if($diagnosis->is_chronic)
            <span class="tag chronic">💊 Chronic</span>
            @endif
            @if($diagnosis->requires_followup)
            <span class="tag">📅 Follow-up</span>
            @endif
          </td>
          <td>
            <span class="status-badge status-{{ $diagnosis->is_active ? 'active' : 'inactive' }}">
              {{ $diagnosis->is_active ? '✓ Active' : '✕ Inactive' }}
            </span>
          </td>
          <td>
            <div class="actions">
              <button class="btn-edit" onclick="editDiagnosis({{ $diagnosis->diagnosis_code_id }})">
                Edit
              </button>
              <button class="btn-toggle" onclick="toggleStatus({{ $diagnosis->diagnosis_code_id }}, {{ $diagnosis->is_active ? 'false' : 'true' }})">
                {{ $diagnosis->is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7">
            <div class="empty-state">
              <div class="empty-state-icon">📋</div>
              <h3>No Diagnoses Found</h3>
              <p>Start by adding your first diagnosis to the system</p>
            </div>
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Add/Edit Modal -->
<div id="diagnosisModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitle">Add New Diagnosis</h2>
      <button class="close-modal" onclick="closeModal()">&times;</button>
    </div>

    <form id="diagnosisForm" method="POST" action="{{ route('admin.diagnoses.store') }}">
      @csrf
      <input type="hidden" id="diagnosisId" name="diagnosis_id">
      <input type="hidden" id="formMethod" name="_method" value="POST">

      <div class="form-row">
        <div class="form-group">
          <label>ICD-10 Code <span class="required">*</span></label>
          <input type="text" name="icd10_code" id="icd10Code" required 
                 placeholder="e.g., J11.1">
        </div>
        <div class="form-group">
          <label>Severity <span class="required">*</span></label>
          <select name="severity" id="severity" required>
            <option value="Minor">Minor</option>
            <option value="Moderate">Moderate</option>
            <option value="Severe">Severe</option>
            <option value="Critical">Critical</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label>Diagnosis Name <span class="required">*</span></label>
        <input type="text" name="diagnosis_name" id="diagnosisName" required
               placeholder="e.g., Influenza A">
      </div>

      <div class="form-group">
        <label>Category <span class="required">*</span></label>
        <select name="category" id="category" required>
          <option value="">Select Category</option>
          <option value="Respiratory Infection">Respiratory Infection</option>
          <option value="Fever & Infection">Fever & Infection</option>
          <option value="Cardiovascular">Cardiovascular</option>
          <option value="Endocrine">Endocrine</option>
          <option value="Gastrointestinal">Gastrointestinal</option>
          <option value="Musculoskeletal">Musculoskeletal</option>
          <option value="Dermatological">Dermatological</option>
          <option value="Mental Health">Mental Health</option>
          <option value="Neurological">Neurological</option>
          <option value="Other">Other</option>
        </select>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea name="description" id="description" 
                  placeholder="Brief description of the condition..."></textarea>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Typical Recovery Days</label>
          <input type="number" name="typical_recovery_days" id="recoveryDays" 
                 placeholder="e.g., 7" min="0">
          <small style="color: #6b7280; font-size: 12px;">Leave blank for chronic conditions</small>
        </div>
        <div class="form-group">
          <label>Properties</label>
          <div class="checkbox-group">
            <label>
              <input type="checkbox" name="is_infectious" id="isInfectious">
              Infectious
            </label>
            <label>
              <input type="checkbox" name="is_chronic" id="isChronic">
              Chronic
            </label>
            <label>
              <input type="checkbox" name="requires_followup" id="requiresFollowup">
              Requires Follow-up
            </label>
          </div>
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn-primary">
          <span id="submitIcon">💾</span>
          <span id="submitText">Save Diagnosis</span>
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function() {
  filterTable();
});

document.getElementById('categoryFilter').addEventListener('change', function() {
  filterTable();
});

document.getElementById('statusFilter').addEventListener('change', function() {
  filterTable();
});

function filterTable() {
  const searchTerm = document.getElementById('searchInput').value.toLowerCase();
  const categoryFilter = document.getElementById('categoryFilter').value;
  const statusFilter = document.getElementById('statusFilter').value;
  const rows = document.querySelectorAll('#diagnosisTable tbody tr');

  rows.forEach(row => {
    const text = row.textContent.toLowerCase();
    const category = row.cells[2]?.textContent || '';
    const status = row.querySelector('.status-badge')?.classList.contains('status-active');

    let matchesSearch = text.includes(searchTerm);
    let matchesCategory = !categoryFilter || category === categoryFilter;
    let matchesStatus = !statusFilter || 
      (statusFilter === 'active' && status) || 
      (statusFilter === 'inactive' && !status);

    row.style.display = matchesSearch && matchesCategory && matchesStatus ? '' : 'none';
  });
}

// Modal functions
function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Add New Diagnosis';
  document.getElementById('diagnosisForm').reset();
  document.getElementById('diagnosisId').value = '';
  document.getElementById('formMethod').value = 'POST';
  document.getElementById('submitText').textContent = 'Save Diagnosis';
  document.getElementById('diagnosisModal').classList.add('active');
}

function closeModal() {
  document.getElementById('diagnosisModal').classList.remove('active');
}

function editDiagnosis(id) {
  // In real implementation, fetch diagnosis data via AJAX
  fetch(`/admin/diagnoses/${id}/edit`)
    .then(response => response.json())
    .then(data => {
      document.getElementById('modalTitle').textContent = 'Edit Diagnosis';
      document.getElementById('diagnosisId').value = data.diagnosis_code_id;
      document.getElementById('icd10Code').value = data.icd10_code;
      document.getElementById('diagnosisName').value = data.diagnosis_name;
      document.getElementById('category').value = data.category;
      document.getElementById('severity').value = data.severity;
      document.getElementById('description').value = data.description || '';
      document.getElementById('recoveryDays').value = data.typical_recovery_days || '';
      document.getElementById('isInfectious').checked = data.is_infectious;
      document.getElementById('isChronic').checked = data.is_chronic;
      document.getElementById('requiresFollowup').checked = data.requires_followup;
      
      document.getElementById('formMethod').value = 'PUT';
      document.getElementById('submitText').textContent = 'Update Diagnosis';
      document.getElementById('diagnosisForm').action = `/admin/diagnoses/${id}`;
      
      document.getElementById('diagnosisModal').classList.add('active');
    });
}

function toggleStatus(id, activate) {
  if (confirm(`Are you sure you want to ${activate ? 'activate' : 'deactivate'} this diagnosis?`)) {
    fetch(`/admin/diagnoses/${id}/toggle-status`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      },
      body: JSON.stringify({ is_active: activate })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        location.reload();
      }
    });
  }
}

// Close modal on outside click
document.getElementById('diagnosisModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeModal();
  }
});
</script>

</body>
</html>