<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Assignments - <?= esc($course['title']) ?></h1>
        <p class="text-muted mb-0 small">Course Number: <?= esc($course['course_number'] ?? 'N/A') ?></p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
    </a>
</div>

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

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-warning text-dark">
        <h5 class="mb-0">
            <i class="fas fa-plus-circle me-2"></i>Create New Assignment
        </h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('assignments/upload/' . $course['id']) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label for="title" class="form-label">Assignment Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" required maxlength="255" value="<?= old('title') ?>">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4" maxlength="1000"><?= old('description') ?></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="due_date" class="form-label">Due Date</label>
                    <input type="datetime-local" class="form-control" id="due_date" name="due_date" value="<?= old('due_date') ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="max_score" class="form-label">Max Score</label>
                    <input type="number" class="form-control" id="max_score" name="max_score" step="0.01" min="0" value="<?= old('max_score', '100') ?>">
                </div>
            </div>
            <div class="mb-3">
                <label for="assignment_file" class="form-label">Assignment File (Optional)</label>
                <input type="file" class="form-control" id="assignment_file" name="assignment_file"
                       accept=".pdf,.ppt,.pptx,.doc,.docx">
                <div class="form-text">
                    Allowed file types: PDF, PPT, PPTX, DOC, DOCX. Maximum size: 10MB.
                </div>
            </div>
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-plus me-2"></i>Create Assignment
            </button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="fas fa-tasks me-2"></i>All Assignments (<?= count($assignments) ?>)
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($assignments)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Due Date</th>
                            <th>Max Score</th>
                            <th>File</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                        <tr>
                            <td><strong><?= esc($assignment['title']) ?></strong></td>
                            <td><?= esc($assignment['description'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($assignment['due_date']): ?>
                                    <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?>
                                <?php else: ?>
                                    <span class="text-muted">No due date</span>
                                <?php endif; ?>
                            </td>
                            <td><?= number_format($assignment['max_score'], 2) ?></td>
                            <td>
                                <?php if ($assignment['file_name']): ?>
                                    <span class="badge bg-info">
                                        <i class="fas fa-file me-1"></i><?= esc($assignment['file_name']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">No file</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('M d, Y', strtotime($assignment['created_at'])) ?></td>
                            <td>
                                <div class="btn-group" role="group">
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
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-4">
                <i class="fas fa-tasks fa-3x mb-3"></i>
                <p>No assignments created yet.</p>
                <p class="small">Create your first assignment using the form above.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>

