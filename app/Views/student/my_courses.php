<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">My Courses</h1>
        <p class="text-muted mb-0 small">Courses you are currently enrolled in</p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-3">
    <?php if (!empty($courses)): ?>
        <?php foreach ($courses as $course): ?>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-0"><?= esc($course['title'] ?? '') ?></h5>
                                <small class="opacity-75">
                                    <i class="bi bi-person me-1"></i>
                                    <?= esc($course['instructor_name'] ?? 'N/A') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3"><?= esc($course['description'] ?? '') ?></p>

                        <h6 class="mb-2"><i class="bi bi-clock me-2"></i>Schedule</h6>
                        <?php if (!empty($course['schedules'])): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($course['schedules'] as $s): ?>
                                    <?php
                                        $start = !empty($s['start_time']) ? date('g:i A', strtotime($s['start_time'])) : '';
                                        $end = !empty($s['end_time']) ? date('g:i A', strtotime($s['end_time'])) : '';
                                    ?>
                                    <span class="badge bg-info text-dark">
                                        <?= esc($s['day_of_week'] ?? '') ?>: <?= esc($start) ?> - <?= esc($end) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-muted small">No schedule assigned.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-book display-6 d-block mb-2"></i>
                    <div>No enrolled courses found.</div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
