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
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">Admin Tools</div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Manage Users</li>
                <li>Manage Courses</li>
                <li>View System Reports</li>
            </ul>
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
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">Teacher Center</div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Create/Manage Assignments</li>
                <li>Grade Submissions</li>
                <li>Communicate with Students</li>
            </ul>
        </div>
    </div>

<?php elseif ($role === 'student'): ?>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Enrolled Courses</h6>
                    <h3 class="mb-0"><?= esc($stats['student']['enrolled'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted">Due Soon</h6>
                    <h3 class="mb-0"><?= esc($stats['student']['dueSoon'] ?? 0) ?></h3>
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
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">Student Hub</div>
        <div class="card-body">
            <ul class="mb-0">
                <li>View Courses</li>
                <li>Submit Assignments</li>
                <li>Track Grades</li>
            </ul>
        </div>
    </div>

<?php else: ?>
    <div class="alert alert-warning">Your account does not have a role assigned.</div>
<?php endif; ?>
<?= $this->endSection() ?>

