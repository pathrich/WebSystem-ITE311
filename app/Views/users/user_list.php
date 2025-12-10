<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">User Management</h1>
        <p class="text-muted mb-0 small">Manage system users, roles, and permissions</p>
    </div>
    <div>
        <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New User
        </a>
        <a href="<?= base_url('users/activity-logs') ?>" class="btn btn-outline-secondary ms-2">
            <i class="fas fa-history me-2"></i>Activity Logs
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

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Users (<?= $pager->getTotal() ?>)</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($users)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= esc($user['id']) ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle bg-primary text-white me-2" style="width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold;">
                                            <?= strtoupper(substr(esc($user['name']), 0, 1)) ?>
                                        </div>
                                        <?= esc($user['name']) ?>
                                    </div>
                                </td>
                                <td><?= esc($user['username']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td>
                                    <span class="badge bg-<?= $user['role'] === 'admin' ? 'danger' : ($user['role'] === 'manager' ? 'warning' : ($user['role'] === 'staff' ? 'info' : 'success')) ?>">
                                        <?= ucfirst(esc($user['role'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst(esc($user['status'])) ?>
                                    </span>
                                </td>
                                <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <?php if ($user['role'] !== 'admin'): ?>
                                            <?php if (empty($user['deleted_at'])): ?>
                                                <a href="<?= base_url('users/edit/' . $user['id']) ?>" class="btn btn-sm btn-outline-primary" title="Edit User">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= base_url('users/activity-logs/' . $user['id']) ?>" class="btn btn-sm btn-outline-info" title="View Activity Logs">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                                <form method="post" action="<?= base_url('users/delete/' . $user['id']) ?>" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete user \"<?= esc($user['name']) ?>\"? This action cannot be undone.');">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete User">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="post" action="<?= base_url('users/restore/' . $user['id']) ?>" style="display: inline;">
                                                    <?= csrf_field() ?>
                                                    <button type="submit" class="btn btn-sm btn-outline-success" title="Restore User">
                                                        <i class="fas fa-undo"></i> Restore
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($pager->getPageCount() > 1): ?>
                <div class="d-flex justify-content-center mt-4">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center text-muted py-5">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h5>No users found</h5>
                <p>Start by adding your first user.</p>
                <a href="<?= base_url('users/create') ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add New User
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>



<!-- Status Toggle Modal -->
<div class="modal fade" id="toggleStatusModal" tabindex="-1" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="toggleStatusModalLabel">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to <span id="statusAction"></span> user "<span id="statusUserName"></span>"?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" id="confirmStatusBtn" class="btn btn-primary">Confirm</a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Toggle status functionality
    $('.toggle-status-btn').on('click', function() {
        const userId = $(this).data('user-id');
        const currentStatus = $(this).data('current-status');
        const userName = $(this).closest('tr').find('td:nth-child(2)').text().trim();

        const action = currentStatus === 'active' ? 'deactivate' : 'activate';
        const actionText = currentStatus === 'active' ? 'deactivate' : 'activate';

        $('#statusAction').text(actionText);
        $('#statusUserName').text(userName);
        $('#confirmStatusBtn').attr('href', '<?= base_url('users/toggle-status/') ?>' + userId);

        const modal = new bootstrap.Modal(document.getElementById('toggleStatusModal'));
        modal.show();
    });
});
</script>

<?= $this->endSection() ?>
