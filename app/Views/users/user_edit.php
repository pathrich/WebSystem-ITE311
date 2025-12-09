<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-user-edit me-2"></i>Edit User: <?= esc($user['name']) ?>
                    </h5>
                </div>
                <div class="card-body">
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

                    <!-- Display validation errors -->
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('users/update/' . $user['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user me-1"></i>Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                                       id="name" name="name" value="<?= old('name', $user['name']) ?>" required>
                                <div class="invalid-feedback">
                                    <?= isset($errors['name']) ? $errors['name'] : '' ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">
                                    <i class="fas fa-at me-1"></i>Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>"
                                       id="username" name="username" value="<?= old('username', $user['username']) ?>" required>
                                <div class="invalid-feedback">
                                    <?= isset($errors['username']) ? $errors['username'] : '' ?>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email Address <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                   id="email" name="email" value="<?= old('email', $user['email']) ?>" required>
                            <div class="invalid-feedback">
                                <?= isset($errors['email']) ? $errors['email'] : '' ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock me-1"></i>New Password
                                </label>
                                <input type="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                                       id="password" name="password">
                                <div class="invalid-feedback">
                                    <?= isset($errors['password']) ? $errors['password'] : '' ?>
                                </div>
                                <div class="form-text">Leave blank to keep current password</div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="password_confirm" class="form-label">
                                    <i class="fas fa-lock me-1"></i>Confirm New Password
                                </label>
                                <input type="password" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>"
                                       id="password_confirm" name="password_confirm">
                                <div class="invalid-feedback">
                                    <?= isset($errors['password_confirm']) ? $errors['password_confirm'] : '' ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label">
                                    <i class="fas fa-user-tag me-1"></i>Role <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>"
                                        id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="student" <?= old('role', $user['role']) === 'student' ? 'selected' : '' ?>>Student</option>
                                    <option value="teacher" <?= old('role', $user['role']) === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                                    <option value="admin" <?= old('role', $user['role']) === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <div class="invalid-feedback">
                                    <?= isset($errors['role']) ? $errors['role'] : '' ?>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">
                                    <i class="fas fa-toggle-on me-1"></i>Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select <?= isset($errors['status']) ? 'is-invalid' : '' ?>"
                                        id="status" name="status" required>
                                    <option value="active" <?= old('status', $user['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="inactive" <?= old('status', $user['status']) === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                                <div class="invalid-feedback">
                                    <?= isset($errors['status']) ? $errors['status'] : '' ?>
                                </div>
                            </div>
                        </div>

                        <!-- User Info Display -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">Created</small>
                                    <strong><?= date('M d, Y H:i', strtotime($user['created_at'])) ?></strong>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-light p-3 rounded">
                                    <small class="text-muted d-block">Last Updated</small>
                                    <strong><?= isset($user['updated_at']) ? date('M d, Y H:i', strtotime($user['updated_at'])) : 'Never' ?></strong>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= base_url('users') ?>" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Users
                            </a>
                            <div>
                                <a href="<?= base_url('users/activity-logs/' . $user['id']) ?>" class="btn btn-outline-info me-2">
                                    <i class="fas fa-history me-1"></i>View Logs
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Password confirmation validation
    $('#password_confirm').on('keyup', function() {
        const password = $('#password').val();
        const confirmPassword = $(this).val();

        if (password && password !== confirmPassword) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Passwords do not match');
        } else {
            $(this).removeClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('');
        }
    });

    // Username availability check (optional AJAX)
    $('#username').on('blur', function() {
        const username = $(this).val();
        const originalUsername = '<?= $user['username'] ?>';

        if (username !== originalUsername && username.length >= 3) {
            // You can add AJAX call here to check username availability
            console.log('Checking username availability:', username);
        }
    });
});
</script>

<?= $this->endSection() ?>
