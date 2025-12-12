<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Edit Course</h1>
        <p class="text-muted mb-0 small">Update the course details below</p>
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

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Course Information</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('course/update/' . ($course['id'] ?? '')) ?>" method="post">
            <?= csrf_field() ?>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Control Number (CN)</label>
                    <input type="text" class="form-control" value="<?= esc($course['course_number'] ?? '') ?>" disabled>
                    <div class="form-text">Control Number cannot be edited.</div>
                </div>
                <div class="col-md-6">
                    <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" value="<?= esc($course['title'] ?? '') ?>" required
                           pattern="[A-Za-z ]+" title="Course title must contain letters and spaces only."
                           oninput="this.value = this.value.replace(/[^A-Za-z ]/g, '');">
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                <textarea class="form-control" id="description" name="description" rows="4" required><?= esc($course['description'] ?? '') ?></textarea>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
