<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>ITE311-DAYAO Template</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">ITE311-DAYAO</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('about') ?>">About</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('contact') ?>">Contact</a></li>
        </ul>

        <?php $isLoggedIn = (bool) session('isLoggedIn'); $role = strtolower((string) session('userRole')); ?>

        <?php if ($isLoggedIn): ?>
          <!-- User Info & Role Display -->
          <div class="navbar-text me-3">
            <span class="badge bg-primary"><?= esc(ucfirst($role)) ?></span>
            <span class="text-light ms-2"><?= esc(session('userName') ?? session('userEmail')) ?></span>
          </div>

          <!-- Role-specific Navigation -->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link fw-bold" href="<?= site_url('dashboard') ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
              </a>
            </li>

            <?php if ($role === 'admin'): ?>
              <!-- Admin Navigation -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  <i class="bi bi-gear"></i> Management
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#"><i class="bi bi-people"></i> Users</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-book"></i> Courses</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-graph-up"></i> Reports</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-shield-check"></i> System Settings</a></li>
                </ul>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-bar-chart"></i> Analytics</a>
              </li>

            <?php elseif ($role === 'teacher'): ?>
              <!-- Teacher Navigation -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  <i class="bi bi-mortarboard"></i> Teaching
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#"><i class="bi bi-collection"></i> My Classes</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-clipboard-check"></i> Assignments</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-file-earmark-text"></i> Submissions</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-plus-circle"></i> Create Assignment</a></li>
                </ul>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-chat-dots"></i> Messages</a>
              </li>

            <?php elseif ($role === 'student'): ?>
              <!-- Student Navigation -->
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                  <i class="bi bi-book"></i> Learning
                </a>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" href="#"><i class="bi bi-collection"></i> My Courses</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-clipboard-check"></i> Assignments</a></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-award"></i> Grades</a></li>
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item" href="#"><i class="bi bi-calendar-event"></i> Schedule</a></li>
                </ul>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-bell"></i> Notifications</a>
              </li>
            <?php endif; ?>

            <!-- User Account Dropdown -->
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> Account
              </a>
              <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="#"><i class="bi bi-person"></i> Profile</a></li>
                <li><a class="dropdown-item" href="#"><i class="bi bi-gear"></i> Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?= site_url('logout') ?>">
                  <i class="bi bi-box-arrow-right"></i> Logout
                </a></li>
              </ul>
            </li>
          </ul>

        <?php else: ?>
          <!-- Guest Navigation -->
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="<?= site_url('login') ?>">
                <i class="bi bi-box-arrow-in-right"></i> Login
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link btn btn-outline-light ms-2" href="<?= site_url('register') ?>">
                <i class="bi bi-person-plus"></i> Register
              </a>
            </li>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container my-4">
      <?= $this->renderSection('content') ?>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
