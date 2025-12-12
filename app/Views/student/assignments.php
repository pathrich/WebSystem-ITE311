<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Assignments</h1>
        <p class="text-muted mb-0 small">Assignments for your enrolled courses</p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($submissionsEnabled) && !$submissionsEnabled): ?>
    <div class="alert alert-warning" role="alert">
        Assignment submission is not available yet because the database table is missing. Please run migrations.
    </div>
<?php endif; ?>

<?php if (!empty($courses)): ?>
    <?php foreach ($courses as $course): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h5 class="mb-0"><?= esc($course['course_title'] ?? '') ?></h5>
                        <small class="opacity-75"><i class="bi bi-person me-1"></i><?= esc($course['instructor_name'] ?? 'N/A') ?></small>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <?php if (!empty($course['assignments'])): ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th style="width: 18%">Due</th>
                                    <th style="width: 18%">Attachment</th>
                                    <th style="width: 16%">Status</th>
                                    <th style="width: 20%">Your File</th>
                                    <th style="width: 20%">Upload</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($course['assignments'] as $a): ?>
                                    <?php
                                        $due = !empty($a['due_date']) ? date('M d, Y g:i A', strtotime($a['due_date'])) : 'N/A';
                                        $sub = $a['submission'] ?? null;
                                        $submittedAt = (!empty($sub['submitted_at'])) ? date('M d, Y g:i A', strtotime($sub['submitted_at'])) : null;
                                        $graded = isset($sub['score']) && $sub['score'] !== null;
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="fw-semibold"><?= esc($a['title'] ?? '') ?></div>
                                            <?php if (!empty($a['description'])): ?>
                                                <div class="text-muted small"><?= esc($a['description']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= esc($due) ?></td>
                                        <td>
                                            <?php if (!empty($a['file_path'])): ?>
                                                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('assignments/download/' . $a['id']) ?>">
                                                    <i class="bi bi-download me-1"></i>Download
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sub): ?>
                                                <span class="badge bg-success">Submitted</span>
                                                <div class="text-muted small"><?= esc($submittedAt ?? '') ?></div>
                                                <?php if ($graded): ?>
                                                    <div class="small fw-semibold mt-1">Score: <?= esc(number_format((float)$sub['score'], 2)) ?> / <?= esc(number_format((float)($a['max_score'] ?? 0), 2)) ?></div>
                                                <?php else: ?>
                                                    <div class="text-muted small mt-1">Not graded</div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Not submitted</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($sub && !empty($sub['file_name'])): ?>
                                                <div class="small"><?= esc($sub['file_name']) ?></div>
                                                <a class="btn btn-sm btn-outline-secondary mt-1" href="<?= base_url('assignments/submission/' . $sub['id']) ?>">
                                                    <i class="bi bi-download me-1"></i>Download
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php $collapseId = 'attempt-' . (int) ($a['id'] ?? 0); ?>
                                            <?php if (!empty($sub)): ?>
                                                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#<?= esc($collapseId) ?>" aria-expanded="false" aria-controls="<?= esc($collapseId) ?>" <?= (isset($submissionsEnabled) && !$submissionsEnabled) ? 'disabled' : '' ?>>
                                                    <i class="bi bi-arrow-repeat me-1"></i>New Attempt
                                                </button>
                                                <div class="collapse mt-2" id="<?= esc($collapseId) ?>">
                                                    <form method="post" action="<?= base_url('assignments/submit/' . $a['id']) ?>" enctype="multipart/form-data" class="d-flex gap-2">
                                                        <?= csrf_field() ?>
                                                        <input type="file" name="submission_file" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.ppt,.pptx" required <?= (isset($submissionsEnabled) && !$submissionsEnabled) ? 'disabled' : '' ?>>
                                                        <button type="submit" class="btn btn-sm btn-primary" <?= (isset($submissionsEnabled) && !$submissionsEnabled) ? 'disabled' : '' ?>>
                                                            <i class="bi bi-upload me-1"></i>Submit
                                                        </button>
                                                    </form>
                                                    <div class="text-muted small mt-1">Allowed: pdf, doc, docx, ppt, pptx</div>
                                                </div>
                                            <?php else: ?>
                                                <form method="post" action="<?= base_url('assignments/submit/' . $a['id']) ?>" enctype="multipart/form-data" class="d-flex gap-2">
                                                    <?= csrf_field() ?>
                                                    <input type="file" name="submission_file" class="form-control form-control-sm" accept=".pdf,.doc,.docx,.ppt,.pptx" required <?= (isset($submissionsEnabled) && !$submissionsEnabled) ? 'disabled' : '' ?>>
                                                    <button type="submit" class="btn btn-sm btn-primary" <?= (isset($submissionsEnabled) && !$submissionsEnabled) ? 'disabled' : '' ?>>
                                                        <i class="bi bi-upload me-1"></i>Upload
                                                    </button>
                                                </form>
                                                <div class="text-muted small mt-1">Allowed: pdf, doc, docx, ppt, pptx</div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-muted">No assignments yet.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center text-muted py-5">
            <i class="bi bi-clipboard-x display-6 d-block mb-2"></i>
            <div>No enrolled courses found.</div>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
