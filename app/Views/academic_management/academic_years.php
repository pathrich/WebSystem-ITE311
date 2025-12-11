<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Academic Years Management</h1>
        <p class="text-muted mb-0 small">Create and manage academic year periods</p>
    </div>
    <div>
        <a href="<?= base_url('academic-management') ?>" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAcademicYearModal">
            <i class="bi bi-plus-circle me-2"></i>Add Academic Year
        </button>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Academic Years List</h5>
    </div>
    <div class="card-body">
        <div id="academicYearsList">
            <?php if (!empty($academicYears)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Year Start</th>
                                <th>Year End</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($academicYears as $year): ?>
                                <tr id="year-row-<?= $year['id'] ?>">
                                    <td><?= esc($year['year_start']) ?></td>
                                    <td><?= esc($year['year_end']) ?></td>
                                    <td>
                                        <?php if ($year['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editAcademicYear(<?= $year['id'] ?>, '<?= esc($year['year_start']) ?>', '<?= esc($year['year_end']) ?>', <?= $year['is_active'] ?>)">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteAcademicYear(<?= $year['id'] ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x display-4 d-block mb-3"></i>
                    <p>No academic years found. Create one to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add/Edit Academic Year Modal -->
<div class="modal fade" id="addAcademicYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="academicYearForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create" id="academicYearAction">
                <input type="hidden" name="id" id="academicYearId">
                <div class="modal-header">
                    <h5 class="modal-title" id="academicYearModalTitle">Add Academic Year</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="year_start" class="form-label">Year Start <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="year_start" name="year_start" 
                               min="2000" max="2100" required>
                    </div>
                    <div class="mb-3">
                        <label for="year_end" class="form-label">Year End <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="year_end" name="year_end" 
                               min="2000" max="2100" required>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1">
                            <label class="form-check-label" for="is_active">
                                Set as Active Academic Year
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBtn">
                        <span id="saveBtnText">Save</span>
                        <span id="saveBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteAcademicYearModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteAcademicYearId">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this academic year? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAlert(message, type = 'success') {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'bi-check-circle' : 'bi-exclamation-triangle';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="bi ${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Save Academic Year
document.getElementById('academicYearForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const saveBtn = document.getElementById('saveBtn');
    const saveBtnText = document.getElementById('saveBtnText');
    const saveBtnSpinner = document.getElementById('saveBtnSpinner');
    const formErrors = document.getElementById('formErrors');
    
    // Hide previous errors
    formErrors.style.display = 'none';
    formErrors.innerHTML = '';
    
    // Show loading state
    saveBtn.disabled = true;
    saveBtnText.textContent = 'Saving...';
    saveBtnSpinner.style.display = 'inline-block';
    
    // Use the new dedicated endpoint
    fetch('<?= base_url('academic-management/save-academic-year') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Always try to parse as JSON first
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Response is not JSON:', text.substring(0, 200));
                throw new Error('Server returned invalid response');
            }
        });
    })
    .then(data => {
        saveBtn.disabled = false;
        saveBtnText.textContent = 'Save';
        saveBtnSpinner.style.display = 'none';
        
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addAcademicYearModal'));
            modal.hide();
            
            // Reset form
            form.reset();
            document.getElementById('academicYearAction').value = 'create';
            document.getElementById('academicYearId').value = '';
            document.getElementById('academicYearModalTitle').textContent = 'Add Academic Year';
            
            // Show success message
            showAlert(data.message || 'Academic year saved successfully!', 'success');
            
            // Reload page after 1 second to show updated list
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show error
            formErrors.innerHTML = data.message || 'Failed to save academic year.';
            formErrors.style.display = 'block';
        }
    })
    .catch(error => {
        saveBtn.disabled = false;
        saveBtnText.textContent = 'Save';
        saveBtnSpinner.style.display = 'none';
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
});

function editAcademicYear(id, yearStart, yearEnd, isActive) {
    document.getElementById('academicYearAction').value = 'update';
    document.getElementById('academicYearId').value = id;
    document.getElementById('year_start').value = yearStart;
    document.getElementById('year_end').value = yearEnd;
    document.getElementById('is_active').checked = isActive == 1;
    document.getElementById('academicYearModalTitle').textContent = 'Edit Academic Year';
    new bootstrap.Modal(document.getElementById('addAcademicYearModal')).show();
}

function deleteAcademicYear(id) {
    document.getElementById('deleteAcademicYearId').value = id;
    new bootstrap.Modal(document.getElementById('deleteAcademicYearModal')).show();
}

// Delete Academic Year
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch('<?= base_url('academic-management/save-academic-year') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                throw new Error('Server returned invalid response');
            }
        });
    })
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAcademicYearModal'));
        modal.hide();
        
        if (data.success) {
            showAlert(data.message || 'Academic year deleted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'Failed to delete academic year.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
});

// Reset form when modal is closed
document.getElementById('addAcademicYearModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('academicYearForm');
    form.reset();
    document.getElementById('academicYearAction').value = 'create';
    document.getElementById('academicYearId').value = '';
    document.getElementById('academicYearModalTitle').textContent = 'Add Academic Year';
    document.getElementById('formErrors').style.display = 'none';
});
</script>
<?= $this->endSection() ?>
