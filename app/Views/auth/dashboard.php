<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Dashboard</h1>
        <p class="text-muted mb-0 small">Role: <span class="badge bg-secondary text-uppercase"><?= esc($userRole ?? 'guest') ?></span></p>
    </div>
    <a href="<?= base_url('logout') ?>" class="btn btn-danger">Logout</a>
</div>

<div class="alert alert-success" role="alert">
    Welcome, <strong><?= esc($userName ?? session('userEmail')) ?></strong>!
    <span class="ms-2 text-muted">(<?= esc($userEmail ?? '') ?>)</span>
</div>

<!-- Display flash messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php $role = strtolower((string)($userRole ?? '')); ?>

<?php if ($role === 'admin'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Total Users</h6>
                    <h3 class="mb-0"><?= esc($stats['admin']['users'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Courses</h6>
                    <h3 class="mb-0"><?= esc($stats['admin']['courses'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Reports</h6>
                    <h3 class="mb-0"><?= esc($stats['admin']['reports'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Admin Tools</h5>
        </div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Manage Users</li>
                <li>Manage Courses</li>
                <li>View System Reports</li>
            </ul>
        </div>
    </div>
    
    <!-- Course Management for Admin -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Course Management</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($adminCourses)): ?>
                <?php foreach ($adminCourses as $courseData): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= esc($courseData['course']['title']) ?></h6>
                            <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#uploadCollapseAdmin<?= $courseData['course']['id'] ?>" aria-expanded="false" aria-controls="uploadCollapseAdmin<?= $courseData['course']['id'] ?>">
                                <i class="fas fa-upload me-1"></i> Upload Materials
                            </button>
                        </div>
                        <div class="collapse" id="uploadCollapseAdmin<?= $courseData['course']['id'] ?>">
                            <div class="card-body">
                                <!-- Upload Form -->
                                <form action="<?= base_url('materials/upload/' . $courseData['course']['id']) ?>" method="post" enctype="multipart/form-data" class="mb-3">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="material_file_admin<?= $courseData['course']['id'] ?>" class="form-label">Select Material File</label>
                                        <input type="file" class="form-control" id="material_file_admin<?= $courseData['course']['id'] ?>" name="material_file" required
                                               accept=".pdf,.ppt,.pptx,.doc,.docx">
                                        <div class="form-text">
                                            Allowed file types: PDF, PPT, PPTX, DOC, DOCX. Maximum size: 10MB.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm">Upload</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2"><?= esc($courseData['course']['description']) ?></p>
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i> Instructor: <?= esc($courseData['course']['instructor_name']) ?>
                            </small>
                            
                            <?php if (!empty($courseData['materials'])): ?>
                                <hr class="my-2">
                                <div class="materials-list">
                                    <h6 class="small text-muted mb-2">
                                        <i class="fas fa-file-alt me-1"></i> 
                                        Materials (<?= count($courseData['materials']) ?>)
                                    </h6>
                                    <?php foreach ($courseData['materials'] as $material): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small>
                                                <i class="fas fa-file-pdf text-danger me-1"></i>
                                                <?= esc($material['file_name']) ?>
                                            </small>
                                            <div>
                                                <small class="text-muted me-2">
                                                    <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                                </small>
                                                <a href="<?= base_url('materials/delete/' . $material['id']) ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this material?')">
                                                    <i class="fas fa-trash me-1"></i>Delete
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <hr class="my-2">
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> No materials uploaded yet</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>No courses available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($role === 'teacher'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Classes</h6>
                    <h3 class="mb-0"><?= esc($stats['teacher']['classes'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Assignments</h6>
                    <h3 class="mb-0"><?= esc($stats['teacher']['assignments'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Submissions</h6>
                    <h3 class="mb-0"><?= esc($stats['teacher']['submissions'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Teacher Center</h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-3">
                <a href="<?= base_url('course/create') ?>" class="btn btn-success btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Course
                </a>
            </div>
            <ul class="mb-0">
                <li>Create/Manage Assignments</li>
                <li>Grade Submissions</li>
                <li>Communicate with Students</li>
            </ul>
        </div>
    </div>
    
    <!-- Course Management for Teacher -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Course Management</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($teacherCourses)): ?>
                <?php foreach ($teacherCourses as $courseData): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= esc($courseData['course']['title']) ?></h6>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#uploadCollapseTeacher<?= $courseData['course']['id'] ?>" aria-expanded="false" aria-controls="uploadCollapseTeacher<?= $courseData['course']['id'] ?>">
                                    <i class="fas fa-upload me-1"></i> Upload Materials
                                </button>
                                <a href="<?= base_url('course/edit/' . $courseData['course']['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="collapse" id="uploadCollapseTeacher<?= $courseData['course']['id'] ?>">
                            <div class="card-body">
                                <!-- Upload Form -->
                                <form action="<?= base_url('materials/upload/' . $courseData['course']['id']) ?>" method="post" enctype="multipart/form-data" class="mb-3">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="material_file_teacher<?= $courseData['course']['id'] ?>" class="form-label">Select Material File</label>
                                        <input type="file" class="form-control" id="material_file_teacher<?= $courseData['course']['id'] ?>" name="material_file" required
                                               accept=".pdf,.ppt,.pptx,.doc,.docx">
                                        <div class="form-text">
                                            Allowed file types: PDF, PPT, PPTX, DOC, DOCX. Maximum size: 10MB.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm">Upload</button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2"><?= esc($courseData['course']['description']) ?></p>
                            
                            <?php if (!empty($courseData['materials'])): ?>
                                <hr class="my-2">
                                <div class="materials-list">
                                    <h6 class="small text-muted mb-2">
                                        <i class="fas fa-file-alt me-1"></i> 
                                        Materials (<?= count($courseData['materials']) ?>)
                                    </h6>
                                    <?php foreach ($courseData['materials'] as $material): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <small>
                                                <i class="fas fa-file-pdf text-danger me-1"></i>
                                                <?= esc($material['file_name']) ?>
                                            </small>
                                            <div>
                                                <small class="text-muted me-2">
                                                    <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                                </small>
                                                <a href="<?= base_url('materials/delete/' . $material['id']) ?>"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Are you sure you want to delete this material?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <hr class="my-2">
                                <small class="text-muted"><i class="fas fa-info-circle me-1"></i> No materials uploaded yet</small>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-folder-open fa-3x mb-3"></i>
                    <p>No courses assigned to you.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php elseif ($role === 'student'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Enrolled Courses</h6>
                    <h3 class="mb-0"><?= esc(count($enrollments ?? [])) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Available Courses</h6>
                    <h3 class="mb-0"><?= esc(count($availableCourses ?? [])) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Average</h6>
                    <h3 class="mb-0"><?= esc($stats['student']['average'] ?? 'N/A') ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Display Enrolled Courses section -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Enrolled Courses</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($enrollments)): ?>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <div class="card mb-3 border-start border-primary border-3">
                                <div class="card-body">
                                    <h6 class="card-title text-primary"><?= esc($enrollment['title']) ?></h6>
                                    <p class="card-text text-muted small"><?= esc($enrollment['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Instructor: <?= esc($enrollment['instructor_name']) ?>
                                        </small>
                                        <small class="text-muted">
                                            <i class="fas fa-calendar me-1"></i>
                                            Enrolled: <?= date('M d, Y', strtotime($enrollment['enrolled_at'])) ?>
                                        </small>
                                    </div>
                                    
                                    <!-- Display Materials for this course -->
                                    <?php if (isset($courseMaterials[$enrollment['course_id']])): ?>
                                        <hr class="my-2">
                                        <div class="materials-section">
                                            <h6 class="text-muted small mb-2"><i class="fas fa-file-alt me-1"></i> Course Materials:</h6>
                                            <div class="list-group list-group-flush">
                                                <?php foreach ($courseMaterials[$enrollment['course_id']]['materials'] as $material): ?>
                                                    <div class="list-group-item px-0 py-2 border-0">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                                                <small><?= esc($material['file_name']) ?></small>
                                                            </div>
                                                            <a href="<?= base_url('materials/download/' . $material['id']) ?>"
                                                               class="btn btn-sm btn-outline-primary"
                                                               title="Download <?= esc($material['file_name']) ?>">
                                                                <i class="fas fa-download me-1"></i>Download
                                                            </a>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fas fa-clock me-1"></i>
                                                            <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-book-open fa-3x mb-3"></i>
                            <p>You haven't enrolled in any courses yet.</p>
                            <p class="small">Browse available courses to get started!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Display Available Courses section -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Available Courses</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableCourses)): ?>
                        <?php foreach ($availableCourses as $course): ?>
                            <div class="card mb-3" id="course-<?= $course['id'] ?>">
                                <div class="card-body">
                                    <h6 class="card-title text-success"><?= esc($course['title']) ?></h6>
                                    <p class="card-text text-muted small"><?= esc($course['description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Instructor: <?= esc($course['instructor_name']) ?>
                                        </small>
                                        <button class="btn btn-success btn-sm enroll-btn" 
                                                data-course-id="<?= $course['id'] ?>"
                                                data-course-title="<?= esc($course['title']) ?>">
                                            <i class="fas fa-plus me-1"></i>Enroll
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                            <p>You're enrolled in all available courses!</p>
                            <p class="small">Great job staying on top of your learning.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-warning">Your account does not have a role assigned.</div>
<?php endif; ?>

<!-- jQuery and AJAX functionality -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Listen for a click on the Enroll button
    $('.enroll-btn').on('click', function(e) {
        // Prevent the default form submission behavior
        e.preventDefault();
        
        const button = $(this);
        const courseId = button.data('course-id');
        const courseTitle = button.data('course-title');
        const courseCard = button.closest('.card');
        
        // Disable button and show loading state
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Enrolling...');
        
        // Use $.post() to send the course_id to the /course/enroll URL
        $.ajax({
            url: '<?= base_url('course/enroll') ?>',
            type: 'POST',
            data: {
                course_id: courseId,
                <?= csrf_token() ?>: '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // On a successful response from the server:
                    // Displays a Bootstrap alert message
                    const alertHtml = `
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('body').prepend(alertHtml);
                    
                    // Auto-hide alert after 5 seconds
                    setTimeout(function() {
                        $('.alert').fadeOut();
                    }, 5000);
                    
                    // Updates the Enrolled Courses list dynamically without reloading the page
                    const enrolledHtml = `
                        <div class="card mb-3 border-start border-primary border-3">
                            <div class="card-body">
                                <h6 class="card-title text-primary">${response.course.title}</h6>
                                <p class="card-text text-muted small">${response.course.description}</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        Instructor: ${response.course.instructor_name || 'Unknown'}
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Enrolled: ${new Date().toLocaleDateString('en-US', {month: 'short', day: 'numeric', year: 'numeric'})}
                                    </small>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Remove empty state if exists and add new enrollment
                    const enrolledList = $('.col-lg-6:first .card-body');
                    if (enrolledList.find('.text-center').length) {
                        enrolledList.html('');
                    }
                    enrolledList.prepend(enrolledHtml);
                    
                    // Hides or disables the Enroll button for that course
                    courseCard.fadeOut(300, function() {
                        $(this).remove();
                    });
                    
                    // Update course counts
                    const enrolledCount = parseInt($('.col-md-4:first h3').text()) + 1;
                    $('.col-md-4:first h3').text(enrolledCount);

                    const availableCount = parseInt($('.col-md-4:nth-child(2) h3').text()) - 1;
                    $('.col-md-4:nth-child(2) h3').text(availableCount);

                    // Refresh notifications after enrollment
                    if (typeof window.fetchNotifications === 'function') {
                        window.fetchNotifications();
                    }
                    
                } else {
                    // Show error message
                    const alertHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    `;
                    $('body').prepend(alertHtml);
                    
                    // Re-enable button
                    button.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Enroll');
                }
            },
            error: function(xhr, status, error) {
                // Show error message
                const alertHtml = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        An error occurred while enrolling. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('body').prepend(alertHtml);
                
                // Re-enable button
                button.prop('disabled', false).html('<i class="fas fa-plus me-1"></i>Enroll');
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

