<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center mt-5">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white text-center">
                <h3>Sign In</h3>
            </div>
            <div class="card-body p-4">
                <?php if (session()->getFlashdata('register_success')): ?>
                    <div class="alert alert-success" role="alert">
                        <?= esc(session()->getFlashdata('register_success')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->getFlashdata('login_error')): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= esc(session()->getFlashdata('login_error')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= base_url('login') ?>" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required value="<?= esc(old('email')) ?>">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Login</button>
                </form>
            </div>
        </div>
        <p class="text-center mt-3 text-muted small">
            Don't have an account? <a href="<?= base_url('register') ?>" class="text-dark">Register</a>
        </p>
    </div>
</div>
<?= $this->endSection() ?>