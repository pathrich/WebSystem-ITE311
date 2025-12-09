<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Activity Logs</h1>
        <p class="text-muted mb-0 small">Monitor user activities and system changes</p>
    </div>
    <div>
        <a href="<?= base_url('users') ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Users
        </a>
    </div>
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

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="action" class="form-label">Action Type</label>
                <select name="action" id="action" class="form-select">
                    <option value="">All Actions</option>
                    <option value="create" <?= (isset($_GET['action']) && $_GET['action'] === 'create') ? 'selected' : '' ?>>Create</option>
                    <option value="update" <?= (isset($_GET['action']) && $_GET['action'] === 'update') ? 'selected' : '' ?>>Update</option>
                    <option value="delete" <?= (isset($_GET['action']) && $_GET['action'] === 'delete') ? 'selected' : '' ?>>Delete</option>
                    <option value="status_change" <?= (isset($_GET['action']) && $_GET['action'] === 'status_change') ? 'selected' : '' ?>>Status Change</option>
                    <option value="role_change" <?= (isset($_GET['action']) && $_GET['action'] === 'role_change') ? 'selected' : '' ?>>Role Change</option>
                </select>
            </div>
            <div class="col-md-4">
                <label for="date_from" class="form-label">Date From</label>
                <input type="date" name="date_from" id="date_from" class="form-control" value="<?= $_GET['date_from'] ?? '' ?>">
            </div>
            <div class="col-md-4">
                <label for="date_to" class="form-label">Date To</label>
                <input type="date" name="date_to" id="date_to" class="form-control" value="<?= $_GET['date_to'] ?? '' ?>">
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>Filter Logs
                </button>
                <a href="<?= base_url('users/activity-logs') ?>" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-times me-2"></i>Clear Filters
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Activity Logs -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i><?= esc($title) ?></h5>
    </div>
    <div class="card-body">
        <?php if (!empty($logs)): ?>
            <div class="activity-timeline">
                <?php foreach ($logs as $log): ?>
                    <div class="activity-item mb-4 pb-4 border-bottom">
                        <div class="d-flex">
                            <div class="activity-icon me-3">
                                <?php
                                $iconClass = 'fas fa-circle';
                                $badgeClass = 'bg-secondary';

                                switch ($log['action']) {
                                    case 'create':
                                        $iconClass = 'fas fa-plus-circle';
                                        $badgeClass = 'bg-success';
                                        break;
                                    case 'update':
                                        $iconClass = 'fas fa-edit';
                                        $badgeClass = 'bg-primary';
                                        break;
                                    case 'delete':
                                        $iconClass = 'fas fa-trash';
                                        $badgeClass = 'bg-danger';
                                        break;
                                    case 'status_change':
                                        $iconClass = 'fas fa-toggle-on';
                                        $badgeClass = 'bg-warning';
                                        break;
                                    case 'role_change':
                                        $iconClass = 'fas fa-user-tag';
                                        $badgeClass = 'bg-info';
                                        break;
                                }
                                ?>
                                <div class="badge rounded-pill <?= $badgeClass ?> p-2">
                                    <i class="<?= $iconClass ?>"></i>
                                </div>
                            </div>
                            <div class="activity-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h6 class="mb-1">
                                            <?php if (isset($log['user_name'])): ?>
                                                <a href="<?= base_url('users/activity-logs/' . $log['user_id']) ?>" class="text-decoration-none">
                                                    <?= esc($log['user_name']) ?> (<?= esc($log['username']) ?>)
                                                </a>
                                            <?php else: ?>
                                                User ID: <?= esc($log['user_id']) ?>
                                            <?php endif; ?>
                                        </h6>
                                        <p class="mb-1 text-muted small">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('M d, Y H:i:s', strtotime($log['created_at'])) ?>
                                        </p>
                                    </div>
                                    <span class="badge <?= $badgeClass ?> text-uppercase small">
                                        <?= str_replace('_', ' ', esc($log['action'])) ?>
                                    </span>
                                </div>
                                <p class="mb-2"><?= esc($log['description']) ?></p>
                                <div class="activity-meta text-muted small">
                                    <span><i class="fas fa-globe me-1"></i>IP: <?= esc($log['ip_address']) ?></span>
                                    <span class="ms-3"><i class="fas fa-desktop me-1"></i>Browser: <?= esc(substr($log['user_agent'], 0, 50)) ?>...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-history fa-3x mb-3"></i>
                <h5>No activity logs found</h5>
                <p>Activity logs will appear here when users perform actions.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.activity-timeline {
    position: relative;
}

.activity-timeline::before {
    content: '';
    position: absolute;
    left: 30px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}

.activity-item {
    position: relative;
}

.activity-item:last-child {
    border-bottom: none !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}

.activity-icon {
    position: relative;
    z-index: 1;
}

.activity-content {
    margin-left: 1rem;
}
</style>

<?= $this->endSection() ?>
