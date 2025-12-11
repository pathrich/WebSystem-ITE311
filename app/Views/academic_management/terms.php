<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Terms Management</h1>
        <p class="text-muted mb-0 small">Create and manage terms (2 terms per semester)</p>
    </div>
    <div>
        <a href="<?= base_url('academic-management') ?>" class="btn btn-secondary me-2">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#addTermModal">
            <i class="bi bi-plus-circle me-2"></i>Add Term
        </button>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Terms List</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($terms)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Term Name</th>
                            <th>Semester</th>
                            <th>Academic Year</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($terms as $term): ?>
                            <tr>
                                <td><?= esc($term['name']) ?></td>
                                <td><?= esc($term['semester_name']) ?></td>
                                <td><?= esc($term['year_start']) ?> - <?= esc($term['year_end']) ?></td>
                                <td><?= $term['start_date'] ? date('M d, Y', strtotime($term['start_date'])) : 'N/A' ?></td>
                                <td><?= $term['end_date'] ? date('M d, Y', strtotime($term['end_date'])) : 'N/A' ?></td>
                                <td>
                                    <?php if ($term['is_active']): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="editTerm(<?= htmlspecialchars(json_encode($term), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            onclick="deleteTerm(<?= $term['id'] ?>)">
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
                <p>No terms found. Create one to get started.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add/Edit Term Modal -->
<div class="modal fade" id="addTermModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="termForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create" id="termAction">
                <input type="hidden" name="id" id="termId">
                <div class="modal-header">
                    <h5 class="modal-title" id="termModalTitle">Add Term</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="formErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="term_semester_id" class="form-label">Semester <span class="text-danger">*</span></label>
                        <select class="form-select" id="term_semester_id" name="semester_id" required>
                            <option value="">Select Semester</option>
                            <?php foreach ($semesters as $semester): ?>
                                <option value="<?= $semester['id'] ?>" 
                                        data-academic-year="<?= $semester['academic_year_id'] ?>">
                                    <?= esc($semester['name']) ?> (<?= esc($semester['year_start']) ?>-<?= esc($semester['year_end']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="term_name" class="form-label">Term Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="term_name" name="name" 
                               placeholder="e.g., Term 1, Term 2" required>
                        <div class="form-text">Typically 2 terms per semester</div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="term_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="term_start_date" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="term_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="term_end_date" name="end_date">
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="term_is_active" name="is_active" value="1">
                            <label class="form-check-label" for="term_is_active">
                                Set as Active Term
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info" id="saveBtn">
                        <span id="saveBtnText">Save</span>
                        <span id="saveBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteTermModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="deleteForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="deleteTermId">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this term? This action cannot be undone.</p>
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

// Save Term
document.getElementById('termForm').addEventListener('submit', function(e) {
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
    
    fetch('<?= base_url('academic-management/save-term') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest', // Identify as AJAX request
            'Accept': 'application/json' // Request JSON response
        },
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Server returned HTML instead of JSON:', text.substring(0, 500));
                throw new Error('Server returned an error page. Check console for details.');
            });
        }
    })
    .then(data => {
        saveBtn.disabled = false;
        saveBtnText.textContent = 'Save';
        saveBtnSpinner.style.display = 'none';
        
        if (data.success) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addTermModal'));
            modal.hide();
            
            // Reset form
            form.reset();
            document.getElementById('termAction').value = 'create';
            document.getElementById('termId').value = '';
            document.getElementById('termModalTitle').textContent = 'Add Term';
            
            // Show success message
            showAlert(data.message || 'Term saved successfully!', 'success');
            
            // Reload page after 1 second
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            // Show error
            formErrors.innerHTML = data.message || 'Failed to save term.';
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

function editTerm(term) {
    document.getElementById('termAction').value = 'update';
    document.getElementById('termId').value = term.id;
    document.getElementById('term_semester_id').value = term.semester_id;
    document.getElementById('term_name').value = term.name;
    document.getElementById('term_start_date').value = term.start_date || '';
    document.getElementById('term_end_date').value = term.end_date || '';
    document.getElementById('term_is_active').checked = term.is_active == 1;
    document.getElementById('termModalTitle').textContent = 'Edit Term';
    new bootstrap.Modal(document.getElementById('addTermModal')).show();
}

function deleteTerm(id) {
    document.getElementById('deleteTermId').value = id;
    new bootstrap.Modal(document.getElementById('deleteTermModal')).show();
}

// Delete Term
document.getElementById('deleteForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    
    fetch('<?= base_url('academic-management/save-term') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json();
        } else {
            return response.text().then(text => {
                console.error('Server returned HTML instead of JSON:', text.substring(0, 500));
                throw new Error('Server returned an error page. Check console for details.');
            });
        }
    })
    .then(data => {
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteTermModal'));
        modal.hide();
        
        if (data.success) {
            showAlert(data.message || 'Term deleted successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showAlert(data.message || 'Failed to delete term.', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
});

// Reset form when modal is closed
document.getElementById('addTermModal').addEventListener('hidden.bs.modal', function() {
    const form = document.getElementById('termForm');
    form.reset();
    document.getElementById('termAction').value = 'create';
    document.getElementById('termId').value = '';
    document.getElementById('termModalTitle').textContent = 'Add Term';
    document.getElementById('formErrors').style.display = 'none';
});
</script>
<?= $this->endSection() ?>
