<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Student Records - <?= esc($course['title']) ?></h1>
        <p class="text-muted mb-0 small">Course Number: <?= esc($course['course_number'] ?? 'N/A') ?></p>
    </div>
    <div>
        <a href="<?= base_url('student-records') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to All Courses
        </a>
        <a href="<?= base_url('dashboard') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-home me-2"></i>Dashboard
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4" id="enroll-student">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="fas fa-user-plus me-2"></i>Enroll Student
        </h5>
    </div>
    <div class="card-body">
        <form id="enrollStudentForm" class="row g-3">
            <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
            <div class="col-md-8">
                <label for="student_id" class="form-label">Select Student</label>
                <select class="form-select" id="student_id" name="student_id" required>
                    <option value="">-- Choose a student --</option>
                    <?php foreach ($availableStudents as $student): ?>
                        <option value="<?= $student['id'] ?>">
                            <?= esc($student['name']) ?> (<?= esc($student['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-success w-100" id="enrollBtn">
                    <i class="fas fa-user-plus me-2"></i>Enroll Student
                </button>
            </div>
        </form>
        <?php if (empty($availableStudents)): ?>
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle me-2"></i>All active students are already enrolled in this course.
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>Enrolled Students (<?= count($students) ?>)
        </h5>
        <span class="badge bg-light text-dark"><?= count($students) ?> Total</span>
    </div>
    <div class="card-body">
        <?php if (!empty($students)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Enrollment Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $index => $enrollment): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td>
                                <strong><?= esc($enrollment['student_name']) ?></strong>
                            </td>
                            <td><?= esc($enrollment['student_email']) ?></td>
                            <td>
                                <?= date('M d, Y', strtotime($enrollment['enrollment_date'] ?? $enrollment['enrolled_at'] ?? 'now')) ?>
                            </td>
                            <td>
                                <span class="badge bg-success"><?= esc(ucfirst($enrollment['status'] ?? 'approved')) ?></span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="<?= base_url('users/edit/' . $enrollment['student_id']) ?>" class="btn btn-sm btn-outline-primary" title="View Profile">
                                        <i class="fas fa-user"></i> Profile
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger unenroll-student-btn" 
                                            data-enrollment-id="<?= $enrollment['id'] ?>"
                                            data-student-name="<?= esc($enrollment['student_name']) ?>"
                                            data-course-title="<?= esc($course['title']) ?>"
                                            title="Unenroll Student">
                                        <i class="fas fa-user-times"></i> Unenroll
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
                <i class="fas fa-users fa-3x mb-3"></i>
                <p>No students enrolled in this course yet.</p>
                <p class="small">Students will appear here once they enroll and are approved.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Handle enroll student form submission
    $('#enrollStudentForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const btn = $('#enrollBtn');
        const studentId = $('#student_id').val();
        
        if (!studentId) {
            alert('Please select a student.');
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enrolling...');
        
        $.ajax({
            url: '<?= base_url('student-records/enroll') ?>',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('.card-body').first().prepend(alertHtml);
                    
                    // Reset form
                    form[0].reset();
                    
                    // Reload page to show new student in table
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-user-plus me-2"></i>Enroll Student');
                }
            },
            error: function(xhr) {
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMsg);
                btn.prop('disabled', false).html('<i class="fas fa-user-plus me-2"></i>Enroll Student');
            }
        });
    });

    // Handle unenroll button clicks
    $(document).on('click', '.unenroll-student-btn', function() {
        const btn = $(this);
        const enrollmentId = btn.data('enrollment-id');
        const studentName = btn.data('student-name');
        const courseTitle = btn.data('course-title');
        
        if (!confirm(`Are you sure you want to unenroll ${studentName} from ${courseTitle}?`)) {
            return;
        }
        
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: '<?= base_url('student-records/unenroll') ?>',
            method: 'POST',
            data: {
                enrollment_id: enrollmentId
            },
            success: function(response) {
                if (response.success) {
                    // Remove the row with fade effect
                    btn.closest('tr').fadeOut(300, function() {
                        $(this).remove();
                        
                        // Update the count in the header
                        const currentCount = parseInt($('.card-header .badge').text());
                        const newCount = currentCount - 1;
                        $('.card-header .badge').text(newCount + ' Total');
                        $('.card-header h5').html('<i class="fas fa-users me-2"></i>Enrolled Students (' + newCount + ')');
                        
                        // Show success message
                        const alertHtml = `
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                ${response.message}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        `;
                        $('.card-body').prepend(alertHtml);
                        
                        // Reload page if no more students
                        if (newCount === 0) {
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    });
                } else {
                    alert('Error: ' + response.message);
                    btn.prop('disabled', false).html('<i class="fas fa-user-times"></i> Unenroll');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
                btn.prop('disabled', false).html('<i class="fas fa-user-times"></i> Unenroll');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

