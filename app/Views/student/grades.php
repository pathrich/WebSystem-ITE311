<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Grades</h1>
        <p class="text-muted mb-0 small">Grades based on your submitted assignments</p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($submissionsEnabled) && !$submissionsEnabled): ?>
    <div class="alert alert-warning" role="alert">
        Grades are not available yet because the database table for submissions is missing. Please run migrations.
    </div>
<?php endif; ?>

<?php if (!empty($courses)): ?>
    <?php foreach ($courses as $course): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <div>
                    <h5 class="mb-0"><?= esc($course['course_title'] ?? '') ?></h5>
                    <small class="opacity-75"><i class="bi bi-person me-1"></i><?= esc($course['instructor_name'] ?? 'N/A') ?></small>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($course['items'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Assignment</th>
                                    <th style="width: 18%">Submitted</th>
                                    <th style="width: 18%">Score</th>
                                    <th style="width: 24%">Feedback</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($course['items'] as $r): ?>
                                    <?php
                                        $submittedAt = !empty($r['submitted_at']) ? date('M d, Y g:i A', strtotime($r['submitted_at'])) : 'N/A';
                                        $max = isset($r['max_score']) ? (float) $r['max_score'] : 0.0;
                                        $score = $r['score'];
                                        $scoreText = ($score === null) ? 'Not graded' : (number_format((float)$score, 2) . ' / ' . number_format($max, 2));
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= esc($r['assignment_title'] ?? '') ?></div>
                                            <?php if (!empty($r['due_date'])): ?>
                                                <div class="text-muted small">Due: <?= esc(date('M d, Y g:i A', strtotime($r['due_date']))) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($submittedAt) ?></td>
                                        <td>
                                            <?php if ($score === null): ?>
                                                <span class="badge bg-secondary">Not graded</span>
                                            <?php else: ?>
                                                <span class="badge bg-success"><?= esc($scoreText) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($r['feedback'])): ?>
                                                <div class="small"><?= esc($r['feedback']) ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-muted">No grades yet.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-award display-6 d-block mb-2"></i>
            <div>No grades found.</div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
