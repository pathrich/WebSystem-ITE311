<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Schedule</h1>
        <p class="text-muted mb-0 small">Your weekly timetable based on assigned teacher schedules</p>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Timetable</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($rows)): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 18%">Day</th>
                            <th style="width: 20%">Time</th>
                            <th>Course</th>
                            <th style="width: 22%">Instructor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                                $start = !empty($row['start_time']) ? date('g:i A', strtotime($row['start_time'])) : '';
                                $end = !empty($row['end_time']) ? date('g:i A', strtotime($row['end_time'])) : '';
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= esc($row['day_of_week'] ?? '') ?></span></td>
                                <td><?= esc($start) ?> - <?= esc($end) ?></td>
                                <td>
                                    <div class="fw-semibold"><?= esc($row['course_title'] ?? '') ?></div>
                                </td>
                                <td><?= esc($row['instructor_name'] ?? 'N/A') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-calendar-x display-6 d-block mb-2"></i>
                <div>No schedule entries found.</div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
