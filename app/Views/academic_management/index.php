<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Academic Management</h1>
        <p class="text-muted mb-0 small">Manage academic years, semesters, terms, and course details</p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- Academic Years Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-range me-2"></i>Academic Years</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Manage academic year periods (e.g., 2024-2025)</p>
                <div class="mb-3">
                    <strong>Total:</strong> <?= count($academicYears ?? []) ?>
                </div>
                <a href="<?= base_url('academic-management/academic-years') ?>" class="btn btn-primary w-100">
                    <i class="bi bi-gear me-2"></i>Manage Academic Years
                </a>
            </div>
        </div>
    </div>

    <!-- Semesters Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Semesters</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Manage semesters within academic years</p>
                <div class="mb-3">
                    <strong>Total:</strong> <?= count($semesters ?? []) ?>
                </div>
                <a href="<?= base_url('academic-management/semesters') ?>" class="btn btn-success w-100">
                    <i class="bi bi-gear me-2"></i>Manage Semesters
                </a>
            </div>
        </div>
    </div>

    <!-- Terms Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-calendar-week me-2"></i>Terms</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Manage terms within semesters (2 terms per semester)</p>
                <div class="mb-3">
                    <strong>Total:</strong> <?= count($terms ?? []) ?>
                </div>
                <a href="<?= base_url('academic-management/terms') ?>" class="btn btn-info w-100">
                    <i class="bi bi-gear me-2"></i>Manage Terms
                </a>
            </div>
        </div>
    </div>

    <!-- Course Numbers & Units Card -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-book me-2"></i>Course Numbers & Units</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">Edit course numbers (CN) and units for all courses</p>
                <a href="<?= base_url('academic-management/courses') ?>" class="btn btn-warning w-100">
                    <i class="bi bi-pencil me-2"></i>Manage Courses
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
