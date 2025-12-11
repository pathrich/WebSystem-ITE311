<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Course Numbers & Units Management</h1>
        <p class="text-muted mb-0 small">Edit course numbers (CN) and units for all courses</p>
    </div>
    <a href="<?= base_url('academic-management') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Courses List</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($courses)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Course Number (CN)</th>
                            <th>Title</th>
                            <th>Instructor</th>
                            <th>Schedule/Time</th>
                            <th>Units</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($courses as $course): ?>
                            <tr data-course-id="<?= $course['id'] ?>">
                                <td class="course-number"><?= esc($course['course_number'] ?? 'N/A') ?></td>
                                <td class="course-title"><?= esc($course['title']) ?></td>
                                <td class="course-instructor"><?= esc($course['instructor_name'] ?? 'N/A') ?></td>
                                <td class="course-schedule">
                                    <?php if (!empty($course['schedules'])): ?>
                                        <?php foreach ($course['schedules'] as $schedule): ?>
                                            <?php
                                            // Format time from 24-hour to 12-hour format
                                            $startTime = date('g:i A', strtotime($schedule['start_time']));
                                            $endTime = date('g:i A', strtotime($schedule['end_time']));
                                            ?>
                                            <div class="badge bg-info mb-1">
                                                <?= esc($schedule['day_of_week']) ?>: <?= $startTime ?> - <?= $endTime ?>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No schedule</span>
                                    <?php endif; ?>
                                </td>
                                <td class="course-units"><?= esc($course['units'] ?? '0') ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary edit-course-btn" 
                                                data-course-id="<?= $course['id'] ?>"
                                                data-course='<?= htmlspecialchars(json_encode($course), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-info assign-teacher-btn" 
                                                data-course-id="<?= $course['id'] ?>"
                                                data-course='<?= htmlspecialchars(json_encode($course), ENT_QUOTES, 'UTF-8') ?>'>
                                            <i class="bi bi-person-plus"></i> Assign Teacher
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-book-x display-4 d-block mb-3"></i>
                <p>No courses found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Edit Course Modal -->
<div class="modal fade" id="editCourseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editCourseForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id" id="courseId">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="editCourseErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label for="courseTitle" class="form-label">Course Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="courseTitle" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="course_number" class="form-label">Control Number (CN)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">CN-</span>
                            <input type="text" class="form-control" id="course_number" name="course_number" 
                                   placeholder="0001" pattern="[0-9]{4}" maxlength="4" minlength="4"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                        </div>
                        <div class="form-text">Enter exactly 4 digits (e.g., 0001 for CN-0001). CN must be unique.</div>
                    </div>
                    <div class="mb-3">
                        <label for="units" class="form-label">Units</label>
                        <input type="number" class="form-control" id="units" name="units" 
                               step="0.5" min="0" max="10" placeholder="e.g., 3.0">
                        <div class="form-text">Enter the number of units for this course</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <span id="editCourseBtnText">Save Changes</span>
                        <span id="editCourseBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Teacher Modal -->
<div class="modal fade" id="assignTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="assignTeacherForm">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="assign_teacher">
                <input type="hidden" name="course_id" id="assignTeacherCourseId">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">Assign Teacher to Course</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="assignTeacherErrors" class="alert alert-danger" style="display: none;"></div>
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <input type="text" class="form-control" id="assignTeacherCourseTitle" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="teacher_id" class="form-label">Select Teacher <span class="text-danger">*</span></label>
                        <select class="form-select" id="teacher_id" name="teacher_id" required>
                            <option value="">Select a teacher...</option>
                            <?php if (!empty($teachers)): ?>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>">
                                        <?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No teachers available</option>
                            <?php endif; ?>
                        </select>
                        <div class="form-text">Select a teacher to assign to this course</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Teacher</label>
                        <input type="text" class="form-control" id="currentTeacherName" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info">
                        <span id="assignTeacherBtnText">Assign Teacher</span>
                        <span id="assignTeacherBtnSpinner" class="spinner-border spinner-border-sm ms-2" style="display: none;"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showSuccessMessage(message) {
    // Create or update alert container
    let alertContainer = document.getElementById('alertContainer');
    if (!alertContainer) {
        // Create alert container if it doesn't exist
        alertContainer = document.createElement('div');
        alertContainer.id = 'alertContainer';
        const card = document.querySelector('.card');
        if (card) {
            card.parentNode.insertBefore(alertContainer, card);
        } else {
            // If no card found, add it before the first element
            const firstElement = document.querySelector('.d-flex.justify-content-between');
            if (firstElement) {
                firstElement.parentNode.insertBefore(alertContainer, firstElement.nextSibling);
            }
        }
    }
    
    alertContainer.innerHTML = `
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Auto-hide after 3 seconds
    setTimeout(() => {
        const alert = alertContainer.querySelector('.alert');
        if (alert) {
            alert.remove();
        }
    }, 3000);
}

// Use event delegation for edit and assign teacher buttons
document.addEventListener('click', function(e) {
    // Handle edit button clicks
    if (e.target.closest('.edit-course-btn')) {
        e.preventDefault();
        const btn = e.target.closest('.edit-course-btn');
        const courseData = btn.getAttribute('data-course');
        if (courseData) {
            try {
                const course = JSON.parse(courseData);
                editCourse(course);
            } catch (err) {
                console.error('Error parsing course data:', err);
            }
        }
    }
    
    // Handle assign teacher button clicks
    if (e.target.closest('.assign-teacher-btn')) {
        e.preventDefault();
        const btn = e.target.closest('.assign-teacher-btn');
        const courseData = btn.getAttribute('data-course');
        if (courseData) {
            try {
                const course = JSON.parse(courseData);
                assignTeacher(course);
            } catch (err) {
                console.error('Error parsing course data:', err);
            }
        }
    }
});

function editCourse(course) {
    document.getElementById('courseId').value = course.id;
    document.getElementById('courseTitle').value = course.title || '';
    // Extract numbers from CN format (e.g., "CN-0001" -> "0001")
    let cnValue = course.course_number || '';
    if (cnValue.startsWith('CN-')) {
        cnValue = cnValue.substring(3); // Remove "CN-" prefix
    }
    // Remove any non-numeric characters
    cnValue = cnValue.replace(/[^0-9]/g, '');
    document.getElementById('course_number').value = cnValue;
    document.getElementById('units').value = course.units || '';
    document.getElementById('editCourseErrors').style.display = 'none';
    new bootstrap.Modal(document.getElementById('editCourseModal')).show();
}

// Handle edit course form submission
document.getElementById('editCourseForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = document.getElementById('editCourseBtnText');
    const btnSpinner = document.getElementById('editCourseBtnSpinner');
    const errorDiv = document.getElementById('editCourseErrors');
    
    // Hide previous errors
    errorDiv.style.display = 'none';
    
    // Show loading state
    submitBtn.disabled = true;
    btnText.textContent = 'Saving...';
    btnSpinner.style.display = 'inline-block';
    
    // Log form data being sent
    console.log('Sending update request for course ID:', document.getElementById('courseId').value);
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    fetch('<?= base_url('academic-management/courses') ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        // Always try to parse as JSON first
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                // If not JSON, check if it's a success redirect
                if (response.ok) {
                    return { success: true, message: 'Course updated successfully!' };
                }
                console.error('Response is not JSON:', text.substring(0, 500));
                return { success: false, message: 'Server returned invalid response' };
            }
        });
    })
    .then(data => {
        // Re-enable submit button
        submitBtn.disabled = false;
        if (btnText) btnText.textContent = 'Save Changes';
        if (btnSpinner) btnSpinner.style.display = 'none';
        
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('editCourseModal'));
            if (modal) {
                modal.hide();
            }
            
            // Update the table row immediately with the new data
            const courseId = document.getElementById('courseId').value;
            const updatedData = data.data || {};
            
            // Get values from form as fallback if server data is not available
            const formTitle = document.getElementById('courseTitle').value;
            // Format CN: Add "CN-" prefix, ensure exactly 4 digits
            let formCourseNumber = document.getElementById('course_number').value;
            if (formCourseNumber) {
                // Remove any non-numeric characters
                formCourseNumber = formCourseNumber.replace(/[^0-9]/g, '');
                
                // Validate: must be exactly 4 digits
                if (formCourseNumber.length !== 4) {
                    alert('Control Number (CN) must be exactly 4 digits (e.g., 0001).');
                    document.getElementById('course_number').focus();
                    return;
                }
                
                // Pad with zeros to ensure 4 digits
                const paddedNumber = formCourseNumber.padStart(4, '0');
                formCourseNumber = 'CN-' + paddedNumber;
            }
            const formUnits = document.getElementById('units').value;
            
            console.log('=== UPDATING TABLE ===');
            console.log('Course ID:', courseId);
            console.log('Updated data from server:', updatedData);
            console.log('Form values - Title:', formTitle, 'CN:', formCourseNumber, 'Units:', formUnits);
            
            // Find the table row using data-course-id attribute
            const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
            console.log('Row found:', row ? 'YES' : 'NO');
            
            if (row) {
                // Get values to update - use server data or form value
                const newCourseNumber = updatedData.course_number !== undefined ? updatedData.course_number : formCourseNumber;
                const newTitle = updatedData.title !== undefined ? updatedData.title : formTitle;
                const newUnits = updatedData.units !== undefined ? updatedData.units : formUnits;
                
                // Update Course Number (CN)
                if (newCourseNumber !== undefined) {
                    const cnCell = row.querySelector('.course-number');
                    if (cnCell) {
                        cnCell.textContent = newCourseNumber || 'N/A';
                        console.log('✓ Updated course number to:', newCourseNumber || 'N/A');
                    } else {
                        console.error('Course number cell not found');
                    }
                }
                
                // Update Title
                if (newTitle !== undefined) {
                    const titleCell = row.querySelector('.course-title');
                    if (titleCell) {
                        titleCell.textContent = newTitle || '';
                        console.log('✓ Updated title to:', newTitle);
                    } else {
                        console.error('Title cell not found');
                    }
                }
                
                // Update Units
                if (newUnits !== undefined) {
                    const unitsCell = row.querySelector('.course-units');
                    if (unitsCell) {
                        let unitsValue = '0';
                        if (newUnits !== null && newUnits !== '' && newUnits !== undefined) {
                            unitsValue = parseFloat(newUnits).toFixed(1);
                        }
                        unitsCell.textContent = unitsValue;
                        console.log('✓ Updated units to:', unitsValue);
                    } else {
                        console.error('Units cell not found');
                    }
                }
                
                // Update the edit and assign teacher buttons with new course data
                const editBtn = row.querySelector('.edit-course-btn');
                const assignBtn = row.querySelector('.assign-teacher-btn');
                
                if (editBtn || assignBtn) {
                    // Get current instructor name from the row
                    const instructorCell = row.querySelector('.course-instructor');
                    const instructorName = instructorCell ? instructorCell.textContent.trim() : 'N/A';
                    
                    // Build updated course object using the values we just updated
                    const updatedCourse = {
                        id: parseInt(courseId),
                        title: newTitle !== undefined ? newTitle : row.querySelector('.course-title')?.textContent || '',
                        course_number: newCourseNumber !== undefined ? newCourseNumber : row.querySelector('.course-number')?.textContent || '',
                        units: newUnits !== undefined ? (newUnits !== '' ? parseFloat(newUnits) : 0) : parseFloat(row.querySelector('.course-units')?.textContent || '0'),
                        instructor_name: instructorName,
                        instructor_id: updatedData.instructor_id || null
                    };
                    
                    // Update button data attributes (no need to update onclick since we use event delegation)
                    if (editBtn) {
                        editBtn.setAttribute('data-course', JSON.stringify(updatedCourse));
                        editBtn.disabled = false; // Ensure button is enabled
                        console.log('✓ Edit button data updated');
                    }
                    
                    if (assignBtn) {
                        assignBtn.setAttribute('data-course', JSON.stringify(updatedCourse));
                        assignBtn.disabled = false; // Ensure button is enabled
                        console.log('✓ Assign Teacher button data updated');
                    }
                }
                
                console.log('Table row updated successfully!');
                
                // Show success message
                showSuccessMessage(data.message || 'Course updated successfully!');
            } else {
                console.error('Could not find table row for course ID:', courseId);
                // If row not found, reload page to show updated data
                showSuccessMessage(data.message || 'Course updated successfully! Reloading...');
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            }
        } else {
            errorDiv.textContent = data.message || 'Failed to update course.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        btnText.textContent = 'Save Changes';
        btnSpinner.style.display = 'none';
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    });
});

function assignTeacher(course) {
    document.getElementById('assignTeacherCourseId').value = course.id;
    document.getElementById('assignTeacherCourseTitle').value = course.title;
    document.getElementById('currentTeacherName').value = course.instructor_name || 'Not assigned';
    document.getElementById('teacher_id').value = course.instructor_id || '';
    document.getElementById('assignTeacherErrors').style.display = 'none';
    new bootstrap.Modal(document.getElementById('assignTeacherModal')).show();
}

// Handle assign teacher form submission
document.getElementById('assignTeacherForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const btnText = document.getElementById('assignTeacherBtnText');
    const btnSpinner = document.getElementById('assignTeacherBtnSpinner');
    const errorDiv = document.getElementById('assignTeacherErrors');
    
    // Hide previous errors
    errorDiv.style.display = 'none';
    
    // Show loading state
    submitBtn.disabled = true;
    btnText.textContent = 'Assigning...';
    btnSpinner.style.display = 'inline-block';
    
    fetch('<?= base_url('academic-management/assign-teacher') ?>', {
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
        submitBtn.disabled = false;
        btnText.textContent = 'Assign Teacher';
        btnSpinner.style.display = 'none';
        
        if (data.success) {
            const modal = bootstrap.Modal.getInstance(document.getElementById('assignTeacherModal'));
            modal.hide();
            
            // Show success message and reload
            alert(data.message || 'Teacher assigned successfully!');
            window.location.reload();
        } else {
            errorDiv.textContent = data.message || 'Failed to assign teacher.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        btnText.textContent = 'Assign Teacher';
        btnSpinner.style.display = 'none';
        console.error('Error:', error);
        errorDiv.textContent = 'An error occurred. Please try again.';
        errorDiv.style.display = 'block';
    });
});
</script>
<?= $this->endSection() ?>

