<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Semesters Management</h1>
        <p class="text-muted mb-0 small">Create and manage semesters</p>
    </div>
    <div>
        <a href="<?= base_url('academic-management') ?>" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSemesterModal">
            <i class="bi bi-plus-circle me-2"></i>Add Semester
        </button>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Semesters List</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($semesters)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Semester Name</th>
                            <th>Academic Year</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($semesters as $semester): ?>
                            <tr>
                                <td><?= esc($semester['name']) ?></td>
                                <td><?= esc($semester['year_start']) ?> - <?= esc($semester['year_end']) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editSemester(<?= $semester['id'] ?>, '<?= esc($semester['name']) ?>', <?= $semester['academic_year_id'] ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteSemester(<?= $semester['id'] ?>)">
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
                <p>No semesters found. Create one to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Semester Modal -->
<div class="modal fade" id="addSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="semesterForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create" id="semesterAction">
                <input type="hidden" name="id" id="semesterId">
                <div class="modal-header">
                    <h5 class="modal-title" id="semesterModalTitle">Add Semester</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="semester_academic_year_id" class="form-label">Academic Year <span class="text-danger">*</span></label>
                        <select class="form-select" id="semester_academic_year_id" name="academic_year_id" required>
                            <option value="">Select Academic Year</option>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>">
                                    <?= esc($year['year_start']) ?> - <?= esc($year['year_end']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="semester_name" class="form-label">Semester Name <span class="text-danger">*</span></label>
                        <select class="form-select" id="semester_name" name="name" required>
                            <option value="">Select Semester</option>
                            <option value="1st semester">1st Semester</option>
                            <option value="2nd semester">2nd Semester</option>
                            <option value="summer">Summer</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success" id="saveBtn">
                        <span id="saveBtnText">Save</span>
                        <span id="saveBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteSemesterModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteSemesterId">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this semester? This action cannot be undone.</p>
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
    
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Save Semester
document.getElementById('semesterForm').addEventListener('submit', function(e) {
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
    
    fetch('<?= base_url('academic-management/save-semester') ?>', {
        method: 'POST',
        body: formData
    })
    .then(response => {
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
            const modal = bootstrap.Modal.getInstance(document.getElementById('addSemesterModal'));
            modal.hide();
            
            // Reset form
            form.reset();
            document.getElementById('semesterAction').value = 'create';
            document.getElementById('semesterId').value = '';
            document.getElementById('semesterModalTitle').textContent = 'Add Semester';
            
            // Show success message
            showAlert(data.message || 'Semester saved successfully!', 'success');
            
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show error
            formErrors.innerHTML = data.message || 'Failed to save semester.';
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

function editSemester(id, name, academicYearId) {
    document.getElementById('semesterAction').value = 'update';
    document.getElementById('semesterId').value = id;
    document.getElementById('semester_name').value = name;
    document.getElementById('semester_academic_year_id').value = academicYearId;
    document.getElementById('semesterModalTitle').textContent = 'Edit Semester';
    new bootstrap.Modal(document.getElementById('addSemesterModal')).show();
}

function deleteSemester(id) {
    document.getElementById('deleteSemesterId').value = id;
    new bootstrap.Modal(document.getElementById('deleteSemesterModal')).show();
}

// Delete Semester
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch('<?= base_url('academic-management/save-semester') ?>', {
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
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteSemesterModal'));
        modal.hide();
        
        if (data.success) {
            showAlert(data.message || 'Semester deleted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'Failed to delete semester.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
});

// Reset form when modal is closed
document.getElementById('addSemesterModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('semesterForm');
    form.reset();
    document.getElementById('semesterAction').value = 'create';
    document.getElementById('semesterId').value = '';
    document.getElementById('semesterModalTitle').textContent = 'Add Semester';
    document.getElementById('formErrors').style.display = 'none';
});
</script>
<?= $this->endSection() ?>
