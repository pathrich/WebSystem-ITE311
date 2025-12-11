<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Student Records</h1>
        <p class="text-muted mb-0 small">View and manage students in your courses</p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if (empty($teacherCourses)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
            <p class="text-muted">No courses assigned to you.</p>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($teacherCourses as $course): ?>
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><?= esc($course['title']) ?></h5>
                        <small>Course Number: <?= esc($course['course_number'] ?? 'N/A') ?></small>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3"><?= esc($course['description'] ?? 'No description') ?></p>
                        
                        <?php 
                        $students = isset($courseStudents[$course['id']]) ? $courseStudents[$course['id']]['students'] : [];
                        $studentCount = count($students);
                        ?>
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-info">
                                <i class="fas fa-users me-1"></i>
                                <?= $studentCount ?> Student<?= $studentCount != 1 ? 's' : '' ?>
                            </span>
                            <div class="btn-group" role="group">
                                <a href="<?= base_url('student-records/course/' . $course['id']) ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>View Students
                                </a>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-user-plus me-1"></i>Enroll Student
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <?php foreach ($teacherCourses as $enrollCourse): ?>
                                            <li>
                                                <a class="dropdown-item" href="<?= base_url('student-records/course/' . $enrollCourse['id']) ?>#enroll-student">
                                                    <i class="fas fa-book me-2"></i>
                                                    <?= esc($enrollCourse['title']) ?>
                                                    <?php if (!empty($enrollCourse['course_number'])): ?>
                                                        <small class="text-muted">(<?= esc($enrollCourse['course_number']) ?>)</small>
                                                    <?php endif; ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($studentCount > 0): ?>
                            <hr>
                            <h6 class="small text-muted mb-2">Enrolled Students:</h6>
                            <ul class="list-unstyled mb-0">
                                <?php foreach (array_slice($students, 0, 5) as $enrollment): ?>
                                    <li class="small mb-1">
                                        <i class="fas fa-user text-primary me-1"></i>
                                        <?= esc($enrollment['student_name']) ?>
                                        <small class="text-muted">(<?= esc($enrollment['student_email']) ?>)</small>
                                    </li>
                                <?php endforeach; ?>
                                <?php if ($studentCount > 5): ?>
                                    <li class="small text-muted">
                                        <i class="fas fa-ellipsis-h me-1"></i>
                                        and <?= $studentCount - 5 ?> more...
                                    </li>
                                <?php endif; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-info mb-0">
                                <small><i class="fas fa-info-circle me-1"></i> No students enrolled yet.</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>

