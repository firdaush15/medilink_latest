// public/js/doctor/prescription-allergy-check.js

/**
 * ✅ PRESCRIPTION ALLERGY CHECKING SYSTEM
 * Real-time allergy validation when doctors select medications
 */

class PrescriptionAllergyChecker {
    constructor(patientId) {
        this.patientId = patientId;
        this.allergyWarnings = [];
        this.selectedMedicines = new Map();
    }

    /**
     * Check medicine for allergies when selected
     */
    async checkMedicineAllergy(medicineId, medicineName) {
        try {
            const response = await fetch(
                `/doctor/medications/${medicineId}/availability?patient_id=${this.patientId}`
            );
            const data = await response.json();

            if (data.has_allergy) {
                this.showAllergyWarning(medicineId, medicineName, data);
                return false; // Cannot add this medicine
            }

            return true; // Safe to add
        } catch (error) {
            console.error('Error checking allergy:', error);
            return true; // Proceed if check fails (will validate server-side)
        }
    }

    /**
     * Display allergy warning modal
     */
    showAllergyWarning(medicineId, medicineName, allergyData) {
        const severity = allergyData.severity;
        const canPrescribe = allergyData.can_prescribe;

        let alertClass, icon, title, action;

        switch (severity) {
            case 'Life-threatening':
                alertClass = 'danger';
                icon = '🚨';
                title = 'CRITICAL ALLERGY - DO NOT PRESCRIBE';
                action = 'blocked';
                break;
            case 'Severe':
                alertClass = 'danger';
                icon = '⛔';
                title = 'SEVERE ALLERGY - PRESCRIPTION BLOCKED';
                action = 'blocked';
                break;
            case 'Moderate':
                alertClass = 'warning';
                icon = '⚡';
                title = 'ALLERGY WARNING - USE WITH CAUTION';
                action = 'caution';
                break;
            case 'Mild':
                alertClass = 'info';
                icon = 'ℹ️';
                title = 'MILD ALLERGY - MONITOR PATIENT';
                action = 'caution';
                break;
        }

        // Create modal HTML
        const modalHtml = `
            <div class="modal fade" id="allergyWarningModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                    <div class="modal-content border-${alertClass}">
                        <div class="modal-header bg-${alertClass} text-white">
                            <h5 class="modal-title">
                                ${icon} ${title}
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-${alertClass}">
                                <h5>⚠️ Patient Allergy Detected</h5>
                                <p><strong>Medicine:</strong> ${medicineName}</p>
                                <p><strong>Known Allergen:</strong> ${allergyData.details.allergen_name}</p>
                                <p><strong>Severity:</strong> ${severity}</p>
                                ${allergyData.details.reaction_description ? 
                                    `<p><strong>Previous Reaction:</strong> ${allergyData.details.reaction_description}</p>` 
                                    : ''}
                            </div>

                            ${allergyData.warning_message ? 
                                `<div class="p-3 bg-light border-left border-${alertClass}" style="border-left-width: 4px !important;">
                                    ${allergyData.warning_message.replace(/\n/g, '<br>')}
                                </div>` 
                                : ''}

                            <div class="mt-3">
                                <h6>Recommended Actions:</h6>
                                ${this.getRecommendedActions(severity, medicineId, medicineName)}
                            </div>
                        </div>
                        <div class="modal-footer">
                            ${action === 'blocked' ? `
                                <button type="button" class="btn btn-primary" 
                                    onclick="allergyChecker.showAlternatives('${medicineId}', '${medicineName}')">
                                    <i class="fa fa-search"></i> Find Safe Alternatives
                                </button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                    Close
                                </button>
                            ` : `
                                <button type="button" class="btn btn-warning" 
                                    onclick="allergyChecker.showAlternatives('${medicineId}', '${medicineName}')">
                                    <i class="fa fa-search"></i> Find Alternatives
                                </button>
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                                    Cancel
                                </button>
                                <button type="button" class="btn btn-danger" 
                                    onclick="allergyChecker.proceedWithOverride('${medicineId}', '${medicineName}')">
                                    ⚠️ Prescribe Anyway (Override)
                                </button>
                            `}
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Remove existing modal if any
        $('#allergyWarningModal').remove();
        
        // Add and show new modal
        $('body').append(modalHtml);
        $('#allergyWarningModal').modal('show');
    }

    /**
     * Get recommended actions based on severity
     */
    getRecommendedActions(severity, medicineId, medicineName) {
        switch (severity) {
            case 'Life-threatening':
            case 'Severe':
                return `
                    <ul class="mb-0">
                        <li><strong>DO NOT prescribe this medication</strong></li>
                        <li>Search for alternative medications in the same category</li>
                        <li>Consider referring to specialist if no alternatives available</li>
                        <li>Document the allergy in medical records</li>
                    </ul>
                `;
            case 'Moderate':
                return `
                    <ul class="mb-0">
                        <li>Strongly consider alternative medications</li>
                        <li>If you must prescribe, add antihistamine prophylaxis</li>
                        <li>Require close patient monitoring</li>
                        <li>Document override reason in prescription notes</li>
                        <li>Inform patient of risks and obtain consent</li>
                    </ul>
                `;
            case 'Mild':
                return `
                    <ul class="mb-0">
                        <li>Consider alternative medications if available</li>
                        <li>Inform patient to monitor for allergic reactions</li>
                        <li>Prescribe antihistamine alongside if needed</li>
                        <li>Document the decision in prescription notes</li>
                    </ul>
                `;
        }
    }

    /**
     * Show safe alternative medications
     */
    async showAlternatives(medicineId, medicineName) {
        try {
            const response = await fetch(
                `/doctor/medications/alternatives?medicine_id=${medicineId}&patient_id=${this.patientId}`
            );
            const data = await response.json();

            if (!data.has_safe_alternatives) {
                this.showNoAlternativesModal(medicineName, data.category);
                return;
            }

            this.displayAlternativesModal(data);
        } catch (error) {
            console.error('Error fetching alternatives:', error);
            alert('Error loading alternative medications. Please try manually searching.');
        }
    }

    /**
     * Display alternatives modal
     */
    displayAlternativesModal(data) {
        const alternativesHtml = data.alternatives.map(med => `
            <tr>
                <td>
                    <strong>${med.medicine_name}</strong>
                    ${med.generic_name ? `<br><small class="text-muted">Generic: ${med.generic_name}</small>` : ''}
                </td>
                <td>${med.strength}</td>
                <td>${med.form}</td>
                <td>
                    <span class="badge badge-${med.quantity_in_stock > 50 ? 'success' : 'warning'}">
                        ${med.quantity_in_stock} in stock
                    </span>
                </td>
                <td>RM ${parseFloat(med.unit_price).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-success" 
                        onclick="allergyChecker.selectAlternative('${med.medicine_id}', '${med.medicine_name}', '${med.unit_price}')">
                        <i class="fa fa-check"></i> Select
                    </button>
                </td>
            </tr>
        `).join('');

        const modalHtml = `
            <div class="modal fade" id="alternativesModal" tabindex="-1" role="dialog">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">
                                ✅ Safe Alternative Medications
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i>
                                These medications are in the same category (<strong>${data.category}</strong>) 
                                and have NO known allergies for this patient.
                            </div>

                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Medicine Name</th>
                                        <th>Strength</th>
                                        <th>Form</th>
                                        <th>Stock</th>
                                        <th>Price</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${alternativesHtml}
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                Close
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        $('#alternativesModal').remove();
        $('body').append(modalHtml);
        $('#allergyWarningModal').modal('hide');
        $('#alternativesModal').modal('show');
    }

    /**
     * Show modal when no alternatives found
     */
    showNoAlternativesModal(medicineName, category) {
        alert(`No safe alternative medications found in the ${category} category.\n\nRecommendations:\n- Search other categories\n- Consult with specialist\n- Consider referral`);
    }

    /**
     * Select an alternative medicine
     */
    selectAlternative(medicineId, medicineName, unitPrice) {
        // Close modals
        $('#alternativesModal').modal('hide');
        $('#allergyWarningModal').modal('hide');

        // Auto-fill the medicine in prescription form
        // This depends on your form structure - adjust accordingly
        $('input[name="medicine_search"]').val(medicineName);
        $('input[name="medicine_id"]').val(medicineId);
        $('input[name="unit_price"]').val(unitPrice);

        // Show success message
        toastr.success(`Selected safe alternative: ${medicineName}`, 'Success');
    }

    /**
     * Proceed with override (for Moderate/Mild allergies)
     */
    proceedWithOverride(medicineId, medicineName) {
        const reason = prompt(
            '⚠️ ALLERGY OVERRIDE\n\n' +
            'You are about to prescribe a medication that the patient is allergic to.\n\n' +
            'Please provide a detailed medical justification for this override:'
        );

        if (!reason || reason.trim() === '') {
            alert('Override reason is required. Prescription cancelled.');
            return;
        }

        // Set override flag and reason
        $('<input>').attr({
            type: 'hidden',
            name: 'allergy_override',
            value: '1'
        }).appendTo('#prescriptionForm');

        $('<input>').attr({
            type: 'hidden',
            name: 'allergy_override_reason',
            value: reason
        }).appendTo('#prescriptionForm');

        // Close modal and allow prescription
        $('#allergyWarningModal').modal('hide');
        
        toastr.warning(
            `⚠️ Allergy override recorded. Pharmacist will be notified.\nReason: ${reason}`,
            'Override Accepted',
            { timeOut: 5000 }
        );
    }
}

// Initialize on page load
let allergyChecker;

$(document).ready(function() {
    const patientId = $('input[name="patient_id"]').val();
    if (patientId) {
        allergyChecker = new PrescriptionAllergyChecker(patientId);
    }

    // Hook into medicine selection
    // Adjust this based on your autocomplete implementation
    $(document).on('select2:select', '#medicine-select', async function(e) {
        const medicineId = e.params.data.id;
        const medicineName = e.params.data.text;
        
        const isSafe = await allergyChecker.checkMedicineAllergy(medicineId, medicineName);
        
        if (!isSafe) {
            // Clear selection
            $(this).val(null).trigger('change');
        }
    });
});