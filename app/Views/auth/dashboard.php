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
                <div class="card-body" id="availableCoursesList">
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
            <div class="d-flex gap-2 mb-3">
                <a href="<?= base_url('users') ?>" class="btn btn-primary">
                    <i class="fas fa-users me-2"></i>Manage Users
                </a>
                <a href="<?= base_url('course/create') ?>" class="btn btn-success">
                    <i class="bi bi-plus-circle me-2"></i>Add Course
                </a>
                <a href="<?= base_url('academic-management') ?>" class="btn btn-info">
                    <i class="bi bi-gear me-2"></i>Academic Management
                </a>
                <a href="<?= base_url('academic-management/courses') ?>" class="btn btn-outline-info">
                    <i class="bi bi-person-plus me-2"></i>Assign Teachers to Courses
                </a>
                <a href="<?= base_url('courses') ?>" class="btn btn-outline-secondary">
                    <i class="bi bi-book me-2"></i>View All Courses
                </a>
            </div>
            <ul class="mb-0">
                <li>View System Reports</li>
            </ul>
        </div>
    </div>
    
    <!-- Course Management for Admin -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Course Management</h5>
            <div class="input-group input-group-sm" style="max-width: 320px;">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" id="adminCourseSearchInput" class="form-control" placeholder="Search courses...">
            </div>
        </div>
        <div class="card-body" id="adminCoursesWrapper">
            <?php if (!empty($adminCourses)): ?>
                <?php foreach ($adminCourses as $courseData): ?>
                    <div class="card mb-3 admin-course-item">
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
                                               accept=".pdf,.ppt">
                                        <div class="form-text">
                                            Allowed file types: PDF and PPT only. Maximum size: 10MB.
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('adminCourseSearchInput');
        var wrapper = document.getElementById('adminCoursesWrapper');
        if (!searchInput || !wrapper) return;

        var noResultEl = document.createElement('div');
        noResultEl.className = 'text-center text-muted py-4';
        noResultEl.style.display = 'none';
        noResultEl.innerHTML = '<i class="fas fa-search fa-2x mb-2"></i><p class="mb-0">No courses found.</p>';
        wrapper.appendChild(noResultEl);

        function applyFilter() {
            var value = (searchInput.value || '').toLowerCase();
            var cards = wrapper.querySelectorAll('.admin-course-item');
            var anyVisible = false;

            cards.forEach(function (card) {
                var text = (card.textContent || '').toLowerCase();
                var isMatch = text.indexOf(value) > -1;
                card.style.display = isMatch ? '' : 'none';
                if (isMatch) anyVisible = true;
            });

            if (value && !anyVisible) {
                noResultEl.style.display = '';
            } else {
                noResultEl.style.display = 'none';
            }
        }

        searchInput.addEventListener('keyup', applyFilter);
    });
    </script>

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
            <h5 class="mb-0">
                <i class="fas fa-chalkboard-teacher me-2"></i>Teacher Center
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <a href="<?= base_url('student-records') ?>" class="btn btn-info">
                    <i class="bi bi-people me-2"></i>Student Records
                </a>
            </div>
            <div class="alert alert-info mb-0">
                <strong><i class="fas fa-info-circle me-2"></i>Quick Actions:</strong>
                <ul class="mb-0 mt-2">
                    <li>Click <strong>"Upload Materials"</strong> on any course card to upload course materials</li>
                    <li>Click <strong>"Assignments"</strong> on any course card to create assignments</li>
                    <li>Click <strong>"Manage"</strong> to view all assignments for a course</li>
                    <li>Click <strong>"Students"</strong> to view enrolled students for a course</li>
                </ul>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Note:</strong> Courses are assigned by administrators. Contact admin if you need a course assigned to you.
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pending Enrollments for Teacher - PROMINENT DISPLAY -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header <?= !empty($pendingEnrollments) ? 'bg-warning text-dark' : 'bg-secondary text-white' ?>">
            <h5 class="mb-0">
                <i class="bi bi-clock-history me-2"></i>
                Pending Enrollment Approvals
                <?php if (!empty($pendingEnrollments)): ?>
                    <span class="badge bg-danger ms-2"><?= count($pendingEnrollments) ?> Pending</span>
                <?php else: ?>
                    <span class="badge bg-success ms-2">All Clear</span>
                <?php endif; ?>
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($pendingEnrollments)): ?>
                <div class="alert alert-warning mb-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Action Required:</strong> You have <?= count($pendingEnrollments) ?> pending enrollment request(s) waiting for your approval.
                </div>
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="pendingEnrollmentSearchInput" class="form-control" placeholder="Search pending enrollments...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover table-bordered" id="pendingEnrollmentsTable">
                        <thead class="table-warning">
                            <tr>
                                <th>#</th>
                                <th>Student Information</th>
                                <th>Course Details</th>
                                <th>Course Number</th>
                                <th>Request Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pendingEnrollmentsTbody">
                            <?php foreach ($pendingEnrollments as $index => $enrollment): ?>
                            <tr data-enrollment-id="<?= $enrollment['id'] ?>">
                                <td><strong><?= $index + 1 ?></strong></td>
                                <td>
                                    <div>
                                        <strong class="text-primary"><?= esc($enrollment['student_name']) ?></strong><br>
                                        <small class="text-muted">
                                            <i class="fas fa-envelope me-1"></i><?= esc($enrollment['student_email']) ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <strong><?= esc($enrollment['course_title']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= esc($enrollment['course_number'] ?? 'N/A') ?></span>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-calendar me-1"></i>
                                        <?= date('M d, Y', strtotime($enrollment['enrollment_date'])) ?><br>
                                        <i class="fas fa-clock me-1"></i>
                                        <?= date('H:i', strtotime($enrollment['enrollment_date'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <button type="button" class="btn btn-success approve-enrollment-btn mb-1" 
                                                data-enrollment-id="<?= $enrollment['id'] ?>"
                                                data-student-name="<?= esc($enrollment['student_name']) ?>"
                                                data-course-title="<?= esc($enrollment['course_title']) ?>">
                                            <i class="bi bi-check-circle me-1"></i> Approve
                                        </button>
                                        <button type="button" class="btn btn-danger reject-enrollment-btn" 
                                                data-enrollment-id="<?= $enrollment['id'] ?>"
                                                data-student-name="<?= esc($enrollment['student_name']) ?>"
                                                data-course-title="<?= esc($enrollment['course_title']) ?>">
                                            <i class="bi bi-x-circle me-1"></i> Reject
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted mb-0">No pending enrollment requests.</p>
                    <small class="text-muted">All enrollment requests have been processed.</small>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('pendingEnrollmentSearchInput');
        var tbody = document.getElementById('pendingEnrollmentsTbody');
        if (!input || !tbody) return;

        var noResultRow = document.createElement('tr');
        noResultRow.style.display = 'none';
        noResultRow.innerHTML = '<td colspan="6" class="text-center text-muted py-3">No pending enrollments found.</td>';
        tbody.appendChild(noResultRow);

        input.addEventListener('keyup', function () {
            var value = (this.value || '').toLowerCase();
            var rows = tbody.querySelectorAll('tr[data-enrollment-id]');
            var anyVisible = false;

            rows.forEach(function (row) {
                var text = (row.textContent || '').toLowerCase();
                var isMatch = text.indexOf(value) > -1;
                row.style.display = isMatch ? '' : 'none';
                if (isMatch) {
                    anyVisible = true;
                }
            });

            if (value && !anyVisible) {
                noResultRow.style.display = '';
            } else {
                noResultRow.style.display = 'none';
            }
        });
    });
    </script>

    <!-- Course Management for Teacher -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                <i class="fas fa-book me-2"></i>My Assigned Courses (<?= count($teacherCourses ?? []) ?>)
            </h5>
        </div>
        <div class="card-body" id="teacherCoursesWrapper">
            <?php if (!empty($teacherCourses)): ?>
                <div class="d-flex justify-content-end mb-3">
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="teacherCourseSearchInput" class="form-control" placeholder="Search your courses...">
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Click the buttons on each course card to: Upload Materials, Create Assignments, Manage Assignments, or View Students
                </p>
                <?php foreach ($teacherCourses as $courseData): ?>
                    <div class="card mb-3 teacher-dashboard-course-item">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><?= esc($courseData['course']['title']) ?></h6>
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#uploadCollapseTeacher<?= $courseData['course']['id'] ?>" aria-expanded="false" aria-controls="uploadCollapseTeacher<?= $courseData['course']['id'] ?>">
                                    <i class="fas fa-upload me-1"></i> Upload Materials
                                </button>
                                <button class="btn btn-sm btn-warning" type="button" data-bs-toggle="collapse" data-bs-target="#assignmentCollapseTeacher<?= $courseData['course']['id'] ?>" aria-expanded="false" aria-controls="assignmentCollapseTeacher<?= $courseData['course']['id'] ?>">
                                    <i class="fas fa-tasks me-1"></i> Assignments
                                </button>
                                <a href="<?= base_url('assignments/' . $courseData['course']['id']) ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-list me-1"></i> Manage
                                </a>
                                <a href="<?= base_url('student-records/course/' . $courseData['course']['id']) ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-users me-1"></i> Students
                                </a>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-plus me-1"></i> Enroll
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php foreach ($teacherCourses as $enrollCourse): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('student-records/course/' . $enrollCourse['course']['id']) ?>#enroll-student">
                                                    <i class="fas fa-book me-2"></i>
                                                    <?= esc($enrollCourse['course']['title']) ?>
                                                    <?php if (!empty($enrollCourse['course']['course_number'])): ?>
                                                        <small class="text-muted">(<?= esc($enrollCourse['course']['course_number']) ?>)</small>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                <a href="<?= base_url('course/edit/' . $courseData['course']['id']) ?>" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                            </div>
                        </div>
                        <div class="collapse" id="uploadCollapseTeacher<?= $courseData['course']['id'] ?>">
                            <div class="card-body bg-light">
                                <h6 class="mb-3 border-bottom pb-2">
                                    <i class="fas fa-file-upload me-2 text-primary"></i>
                                    Upload Course Material for: <strong><?= esc($courseData['course']['title']) ?></strong>
                                </h6>
                                <!-- Upload Form -->
                                <form action="<?= base_url('materials/upload/' . $courseData['course']['id']) ?>" method="post" enctype="multipart/form-data" class="mb-3">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="material_file_teacher<?= $courseData['course']['id'] ?>" class="form-label">
                                            <i class="fas fa-file me-1"></i>Select Material File <span class="text-danger">*</span>
                                        </label>
                                        <input type="file" class="form-control" id="material_file_teacher<?= $courseData['course']['id'] ?>" name="material_file" required
                                               accept=".pdf,.ppt">
                                        <div class="form-text">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Allowed file types: PDF and PPT only. Maximum size: 10MB.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-2"></i>Upload Material
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#uploadCollapseTeacher<?= $courseData['course']['id'] ?>">
                                        Cancel
                                    </button>
                                </form>
                                
                                <?php if (!empty($courseData['materials'])): ?>
                                    <hr>
                                    <h6 class="small text-muted mb-2">
                                        <i class="fas fa-list me-1"></i>Existing Materials (<?= count($courseData['materials']) ?>)
                                    </h6>
                                    <div class="list-group">
                                        <?php foreach ($courseData['materials'] as $material): ?>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                                    <small><?= esc($material['file_name']) ?></small>
                                                </div>
                                                <div>
                                                    <small class="text-muted me-2">
                                                        <?= date('M d, Y', strtotime($material['created_at'])) ?>
                                                    </small>
                                                    <a href="<?= base_url('materials/download/' . $material['id']) ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                    <a href="<?= base_url('materials/delete/' . $material['id']) ?>" class="btn btn-sm btn-outline-danger" 
                                                       onclick="return confirm('Delete this material?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-info mb-0">
                                        <small><i class="fas fa-info-circle me-1"></i> No materials uploaded yet for this course.</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="collapse" id="assignmentCollapseTeacher<?= $courseData['course']['id'] ?>">
                            <div class="card-body">
                                <!-- Assignment Upload Form -->
                                <form action="<?= base_url('assignments/upload/' . $courseData['course']['id']) ?>" method="post" enctype="multipart/form-data" class="mb-3">
                                    <?= csrf_field() ?>
                                    <div class="mb-3">
                                        <label for="assignment_title_teacher<?= $courseData['course']['id'] ?>" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="assignment_title_teacher<?= $courseData['course']['id'] ?>" name="title" required maxlength="255">
                                    </div>
                                    <div class="mb-3">
                                        <label for="assignment_description_teacher<?= $courseData['course']['id'] ?>" class="form-label">Description</label>
                                        <textarea class="form-control" id="assignment_description_teacher<?= $courseData['course']['id'] ?>" name="description" rows="3" maxlength="1000"></textarea>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="assignment_due_date_teacher<?= $courseData['course']['id'] ?>" class="form-label">Due Date</label>
                                            <input type="datetime-local" class="form-control" id="assignment_due_date_teacher<?= $courseData['course']['id'] ?>" name="due_date">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="assignment_max_score_teacher<?= $courseData['course']['id'] ?>" class="form-label">Max Score</label>
                                            <input type="number" class="form-control" id="assignment_max_score_teacher<?= $courseData['course']['id'] ?>" name="max_score" step="0.01" min="0" value="100">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="assignment_file_teacher<?= $courseData['course']['id'] ?>" class="form-label">Assignment File (Optional)</label>
                                        <input type="file" class="form-control" id="assignment_file_teacher<?= $courseData['course']['id'] ?>" name="assignment_file"
                                               accept=".pdf,.ppt,.pptx,.doc,.docx">
                                        <div class="form-text">
                                            Allowed file types: PDF, PPT, PPTX, DOC, DOCX. Maximum size: 10MB.
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-plus me-1"></i> Create Assignment
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-2"><?= esc($courseData['course']['description']) ?></p>
                            
                            <?php if (!empty($courseData['assignments'] ?? [])): ?>
                                <hr class="my-2">
                                <div class="assignments-list">
                                    <h6 class="small text-muted mb-2">
                                        <i class="fas fa-tasks me-1"></i> 
                                        Assignments (<?= count($courseData['assignments']) ?>)
                                    </h6>
                                    <?php foreach ($courseData['assignments'] as $assignment): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2 p-2 border rounded">
                                            <div>
                                                <strong class="small"><?= esc($assignment['title']) ?></strong>
                                                <?php if ($assignment['due_date']): ?>
                                                    <br><small class="text-muted">Due: <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?></small>
                                                <?php endif; ?>
                                                <?php if ($assignment['file_name']): ?>
                                                    <br><small class="text-info"><i class="fas fa-file me-1"></i><?= esc($assignment['file_name']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php if ($assignment['file_name']): ?>
                                                    <a href="<?= base_url('assignments/download/' . $assignment['id']) ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= base_url('assignments/delete/' . $assignment['id']) ?>" class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Are you sure you want to delete this assignment?')" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
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

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('teacherCourseSearchInput');
        if (searchInput) {
            searchInput.addEventListener('keyup', function () {
                var value = this.value.toLowerCase();
                var cards = document.querySelectorAll('#teacherCoursesWrapper .teacher-dashboard-course-item');
                cards.forEach(function (card) {
                    var text = card.textContent.toLowerCase();
                    card.style.display = text.indexOf(value) > -1 ? '' : 'none';
                });
            });
        }
    });
    </script>

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
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Enrolled Courses</h5>
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="enrolledCoursesSearchInput" class="form-control" placeholder="Search enrolled courses...">
                    </div>
                </div>
                <div class="card-body" id="enrolledCoursesWrapper">
                    <?php if (!empty($enrollments)): ?>
                        <?php foreach ($enrollments as $enrollment): ?>
                            <div class="card mb-3 border-start border-primary border-3 enrolled-course-item">
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
                                            Enrolled: <?= date('M d, Y', strtotime($enrollment['enrollment_date'] ?? $enrollment['enrolled_at'] ?? 'now')) ?>
                                        </small>
                                    </div>
                                    <?php $courseSchedules = $enrollmentSchedules[$enrollment['course_id']] ?? []; ?>
                                    <?php if (!empty($courseSchedules)): ?>
                                        <hr class="my-2">
                                        <small class="text-muted"><i class="fas fa-clock me-1"></i> Schedule:</small>
                                        <ul class="small text-muted mb-0 ps-3">
                                            <?php foreach ($courseSchedules as $schedule): ?>
                                                <?php
                                                    $startTime = !empty($schedule['start_time']) ? date('g:i A', strtotime($schedule['start_time'])) : '';
                                                    $endTime = !empty($schedule['end_time']) ? date('g:i A', strtotime($schedule['end_time'])) : '';
                                                ?>
                                                <li><?= esc($schedule['day_of_week']) ?>: <?= esc($startTime) ?> - <?= esc($endTime) ?><?= !empty($schedule['room']) ? ' (' . esc($schedule['room']) . ')' : '' ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    
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

        <!-- Pending Enrollment Requests -->
        <?php if (!empty($pendingEnrollments)): ?>
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history me-2"></i>
                        Pending Enrollment Requests (<?= count($pendingEnrollments) ?>)
                    </h5>
                    <div class="input-group input-group-sm" style="max-width: 320px;">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" id="pendingRequestsSearchInput" class="form-control" placeholder="Search pending requests...">
                    </div>
                </div>
                <div class="card-body" id="pendingRequestsWrapper">
                    <?php foreach ($pendingEnrollments as $enrollment): ?>
                        <div class="card mb-3 border-start border-warning border-3 pending-request-item">
                            <div class="card-body">
                                <h6 class="card-title text-warning"><?= esc($enrollment['title']) ?></h6>
                                <p class="card-text text-muted small"><?= esc($enrollment['description']) ?></p>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        Instructor: <?= esc($enrollment['instructor_name']) ?>
                                    </small>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>
                                        Requested: <?= date('M d, Y', strtotime($enrollment['enrollment_date'] ?? $enrollment['enrolled_at'] ?? 'now')) ?>
                                    </small>
                                </div>
                                <div class="alert alert-warning mb-0 py-2">
                                    <small><i class="bi bi-hourglass-split me-1"></i> Waiting for teacher approval</small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Enrolled Courses filter
            var enrolledInput = document.getElementById('enrolledCoursesSearchInput');
            var enrolledWrapper = document.getElementById('enrolledCoursesWrapper');
            if (enrolledInput && enrolledWrapper) {
                var enrolledNoResult = document.createElement('div');
                enrolledNoResult.className = 'text-center text-muted py-3';
                enrolledNoResult.style.display = 'none';
                enrolledNoResult.innerHTML = '<i class="fas fa-search me-1"></i>No enrolled courses found.';
                enrolledWrapper.appendChild(enrolledNoResult);

                enrolledInput.addEventListener('keyup', function () {
                    var value = (this.value || '').toLowerCase();
                    var items = enrolledWrapper.querySelectorAll('.enrolled-course-item');
                    var anyVisible = false;

                    items.forEach(function (item) {
                        var text = (item.textContent || '').toLowerCase();
                        var isMatch = text.indexOf(value) > -1;
                        item.style.display = isMatch ? '' : 'none';
                        if (isMatch) anyVisible = true;
                    });

                    if (value && !anyVisible) {
                        enrolledNoResult.style.display = '';
                    } else {
                        enrolledNoResult.style.display = 'none';
                    }
                });
            }

            // Pending Enrollment Requests filter
            var pendingInput = document.getElementById('pendingRequestsSearchInput');
            var pendingWrapper = document.getElementById('pendingRequestsWrapper');
            if (pendingInput && pendingWrapper) {
                var pendingNoResult = document.createElement('div');
                pendingNoResult.className = 'text-center text-muted py-3';
                pendingNoResult.style.display = 'none';
                pendingNoResult.innerHTML = '<i class="fas fa-search me-1"></i>No pending requests found.';
                pendingWrapper.appendChild(pendingNoResult);

                pendingInput.addEventListener('keyup', function () {
                    var value = (this.value || '').toLowerCase();
                    var items = pendingWrapper.querySelectorAll('.pending-request-item');
                    var anyVisible = false;

                    items.forEach(function (item) {
                        var text = (item.textContent || '').toLowerCase();
                        var isMatch = text.indexOf(value) > -1;
                        item.style.display = isMatch ? '' : 'none';
                        if (isMatch) anyVisible = true;
                    });

                    if (value && !anyVisible) {
                        pendingNoResult.style.display = '';
                    } else {
                        pendingNoResult.style.display = 'none';
                    }
                });
            }
        });
        </script>

        <!-- Display Available Courses section -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Available Courses</h5>
                    <div class="input-group w-50 ms-3">
                        <input type="text" id="availableSearchInput" class="form-control form-control-sm" placeholder="Search available courses..." aria-label="Search available courses">
                        <button class="btn btn-outline-light btn-sm" id="availableSearchButton" type="button">Search</button>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($availableCourses)): ?>
                        <?php foreach ($availableCourses as $course): ?>
                            <div class="card mb-3 course-item" id="course-<?php echo $course['id'] ?>">
                                <div class="card-body">
                                    <h6 class="card-title text-success"><?php echo esc($course['title']) ?></h6>
                                    <p class="card-text text-muted small"><?php echo esc($course['description']) ?></p>
                                    <?php if (!empty($course['schedules'])): ?>
                                        <div class="mb-2">
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i> Schedule:</small>
                                            <ul class="small text-muted mb-0 ps-3">
                                                <?php foreach ($course['schedules'] as $schedule): ?>
                                                    <li><?= esc($schedule['day_of_week']) ?>: <?= esc($schedule['start_time']) ?> - <?= esc($schedule['end_time']) ?><?= $schedule['room'] ? ' (' . esc($schedule['room']) . ')' : '' ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            Instructor: <?php echo esc($course['instructor_name']) ?>
                                        </small>
                                            <button class="btn btn-success btn-sm enroll-btn"
                                                data-course-id="<?php echo $course['id'] ?>"
                                                data-course-title="<?php echo esc($course['title']) ?>">
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

    <script>
    // Client-side filtering for student Available Courses (instant on first letter)
    document.addEventListener('DOMContentLoaded', function () {
        var searchInput = document.getElementById('availableSearchInput');
        var searchButton = document.getElementById('availableSearchButton');
        if (!searchInput) return;

        // Button just focuses the input; no server calls
        if (searchButton) {
            searchButton.addEventListener('click', function () {
                searchInput.focus();
                // Manually trigger filtering using current value
                applyAvailableFilter();
            });
        }

        var coursesContainer = searchInput.closest('.card').querySelector('.card-body');
        var noResultEl = null;

        function getCards() {
            // Re-query each time in case the list changes after enrollments
            return coursesContainer.querySelectorAll('.course-item');
        }

        function ensureNoResultElement() {
            if (!noResultEl) {
                noResultEl = document.createElement('div');
                noResultEl.className = 'text-center text-muted py-3 available-no-results';
                noResultEl.style.display = 'none';
                noResultEl.innerHTML = '<i class="fas fa-search me-1"></i>No available courses found for your search.';
                coursesContainer.appendChild(noResultEl);
            }
        }

        function applyAvailableFilter() {
            ensureNoResultElement();

            var value = searchInput.value.toLowerCase();
            var anyVisible = false;
            var cards = getCards();

            cards.forEach(function (card) {
                var text = card.textContent.toLowerCase();
                var isMatch = text.indexOf(value) > -1;
                card.style.display = isMatch ? '' : 'none';
                if (isMatch) {
                    anyVisible = true;
                }
            });

            if (value && !anyVisible) {
                noResultEl.style.display = '';
            } else {
                noResultEl.style.display = 'none';
            }
        }

        // Expose function in closure
        searchInput.addEventListener('keyup', applyAvailableFilter);
    });
    </script>

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
            url: '<?php echo base_url('course/enroll') ?>',
            type: 'POST',
            data: {
                course_id: courseId,
                <?php echo csrf_token() ?>: '<?php echo csrf_hash() ?>'
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
                    let scheduleHtml = '';
                    if (response.course.schedules && response.course.schedules.length > 0) {
                        scheduleHtml = '<hr class="my-2"><small class="text-muted"><i class="fas fa-clock me-1"></i> Schedule:</small><ul class="small text-muted mb-0">';
                        response.course.schedules.forEach(function(schedule) {
                            scheduleHtml += `<li>${schedule.day_of_week}: ${schedule.start_time} - ${schedule.end_time}${schedule.room ? ' (' + schedule.room + ')' : ''}</li>`;
                        });
                        scheduleHtml += '</ul>';
                    }
                    
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
                                ${scheduleHtml}
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

    // Student dashboard Available Courses search is handled via client-side filtering.
});

<?php if ($role === 'teacher' && !empty($pendingEnrollments)): ?>
// Handle approve enrollment button clicks
$(document).on('click', '.approve-enrollment-btn', function() {
    const btn = $(this);
    const enrollmentId = btn.data('enrollment-id');
    const studentName = btn.data('student-name');
    const courseTitle = btn.data('course-title');
    
    if (!confirm(`Approve enrollment request from ${studentName} for ${courseTitle}?`)) {
        return;
    }
    
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Approving...');
    
    $.ajax({
        url: '<?= base_url('course/approve-enrollment') ?>',
        method: 'POST',
        data: {
            enrollment_id: enrollmentId
        },
        success: function(response) {
            if (response.success) {
                // Remove the row
                btn.closest('tr').fadeOut(300, function() {
                    $(this).remove();
                    // Reload page if no more pending enrollments
                    if ($('tbody tr').length === 0) {
                        location.reload();
                    }
                });
                
                // Show success message
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        ${response.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('body').prepend(alertHtml);
            } else {
                alert('Error: ' + response.message);
                btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Approve');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Approve');
        }
    });
});

// Handle reject enrollment button clicks
$(document).on('click', '.reject-enrollment-btn', function() {
    const btn = $(this);
    const enrollmentId = btn.data('enrollment-id');
    const studentName = btn.data('student-name');
    const courseTitle = btn.data('course-title');
    
    if (!confirm(`Reject enrollment request from ${studentName} for ${courseTitle}?`)) {
        return;
    }
    
    btn.prop('disabled', true).html('<i class="bi bi-hourglass-split me-1"></i> Rejecting...');
    
    $.ajax({
        url: '<?= base_url('course/reject-enrollment') ?>',
        method: 'POST',
        data: {
            enrollment_id: enrollmentId
        },
        success: function(response) {
            if (response.success) {
                // Remove the row
                btn.closest('tr').fadeOut(300, function() {
                    $(this).remove();
                    // Reload page if no more pending enrollments
                    if ($('tbody tr').length === 0) {
                        location.reload();
                    }
                });
                
                // Show success message
                const alertHtml = `
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        ${response.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                $('body').prepend(alertHtml);
            } else {
                alert('Error: ' + response.message);
                btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i> Reject');
            }
        },
        error: function() {
            alert('An error occurred. Please try again.');
            btn.prop('disabled', false).html('<i class="bi bi-x-circle me-1"></i> Reject');
        }
    });
});
<?php endif; ?>
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var fileInputs = document.querySelectorAll('input[type="file"][name="material_file"]');

    function validateFileInput(input) {
        var file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) return true;

        var name = (file.name || '').toLowerCase();
        var ext = name.split('.').pop();
        if (ext !== 'pdf' && ext !== 'ppt') {
            alert('Invalid file type. Only PDF and PPT files are allowed.');
            input.value = '';
            return false;
        }

        if (typeof file.size === 'number' && file.size > (10 * 1024 * 1024)) {
            alert('File size too large. Maximum size is 10MB.');
            input.value = '';
            return false;
        }

        return true;
    }

    fileInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            validateFileInput(input);
        });

        var form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (!validateFileInput(input)) {
                    e.preventDefault();
                }
            });
        }
    });
});
</script>
<?php echo $this->endSection() ?>
