<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Dashboard</h1>
    <a href="<?= base_url('logout') ?>" class="btn btn-danger">Logout</a>
</div>

<div class="alert alert-success" role="alert">
    Welcome, <strong><?= esc(session('userEmail')) ?></strong>!
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">Protected Page</h5>
    </div>
    <div class="card-body">
        <p class="mb-0">This is a protected page only visible after login.</p>
    </div>
</div>
<?= $this->endSection() ?>