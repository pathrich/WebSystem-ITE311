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
  <?php $isLoggedIn = (bool) session('isLoggedIn'); $role = strtolower((string) session('userRole')); ?>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
      <a class="navbar-brand" href="#">ITE311-DAYAO</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <?php if (!$isLoggedIn): ?>
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="<?= site_url('/') ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('about') ?>">About</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= site_url('contact') ?>">Contact</a></li>
        </ul>
        <?php endif; ?>

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
              <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notifications-toggle">
                  <i class="bi bi-bell"></i> Notifications
                  <?php if (isset($unreadNotificationsCount) && $unreadNotificationsCount > 0): ?>
                    <span class="badge bg-danger" id="notification-badge"><?= $unreadNotificationsCount ?></span>
                  <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" id="notification-dropdown">
                  <li><h6 class="dropdown-header">Notifications</h6></li>
                  <!-- Notifications will be loaded here via AJAX -->
                  <li><hr class="dropdown-divider"></li>
                  <li><a class="dropdown-item text-center" href="#">View All</a></li>
                </ul>
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

  <!-- Notifications Modal -->
  <div class="modal fade" id="notificationsModal" tabindex="-1" aria-labelledby="notificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="notificationsModalLabel">All Notifications</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="notifications-modal-body">
          <!-- Notifications will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

  <!-- jQuery for AJAX -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  <!-- Notifications Script -->
  <script>
    $(document).ready(function() {
      // Function to fetch and update notifications
      window.fetchNotifications = function() {
        $.ajax({
          url: '<?= site_url('notifications') ?>',
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            // Update badge
            var badge = $('#notification-badge');
            if (data.unread_count > 0) {
              if (badge.length === 0) {
                // Create the badge if it doesn't exist
                $('#notifications-toggle').append('<span class="badge bg-danger ms-1" id="notification-badge">' + data.unread_count + '</span>');
              } else {
                badge.text(data.unread_count).show();
              }
            } else {
              if (badge.length > 0) {
                badge.hide();
              }
            }

            // Update dropdown
            var dropdownHtml = '<li><h6 class="dropdown-header">Notifications</h6></li>';
            if (data.notifications.length > 0) {
              data.notifications.forEach(function(notification) {
                var readClass = notification.is_read == 1 ? 'text-muted' : '';
                var markReadBtn = notification.is_read == 1 ? '' : '<button class="btn btn-sm btn-outline-secondary ms-2 mark-read-btn" data-id="' + notification.id + '">Mark Read</button>';
                dropdownHtml += '<li class="d-flex justify-content-between align-items-center"><a class="dropdown-item ' + readClass + '" href="#" data-id="' + notification.id + '">' +
                  '<small>' + notification.message + '</small><br>' +
                  '<small class="text-muted">' + new Date(notification.created_at).toLocaleString() + '</small>' +
                  '</a>' + markReadBtn + '</li>';
              });
            } else {
              dropdownHtml += '<li><a class="dropdown-item text-muted" href="#">No notifications</a></li>';
            }
            dropdownHtml += '<li><hr class="dropdown-divider"></li>';
            dropdownHtml += '<li><a class="dropdown-item text-center" href="#" data-bs-toggle="modal" data-bs-target="#notificationsModal">View All</a></li>';

            $('#notification-dropdown').html(dropdownHtml);
          },
          error: function() {
            console.log('Error fetching notifications');
          }
        });
      }

      // Fetch notifications on page load
      fetchNotifications();

      // Refresh notifications every 60 seconds
      setInterval(fetchNotifications, 60000);

      // Handle mark as read
      $(document).on('click', '#notification-dropdown .dropdown-item[data-id]', function(e) {
        e.preventDefault();
        var notificationId = $(this).data('id');
        var $item = $(this);

        $.ajax({
          url: '<?= site_url('notifications/mark_read') ?>/' + notificationId,
          method: 'POST',
          dataType: 'json',
          success: function(data) {
            if (data.success) {
              $item.addClass('text-muted');
              fetchNotifications(); // Refresh to update badge
            } else {
              alert('Failed to mark as read: ' + data.message);
            }
          },
          error: function() {
            alert('Error marking notification as read');
          }
        });
      });

      // Handle mark as read button
      $(document).on('click', '.mark-read-btn', function(e) {
        e.preventDefault();
        var notificationId = $(this).data('id');
        var $btn = $(this);
        var $item = $btn.closest('li').find('.dropdown-item');

        $.ajax({
          url: '<?= site_url('notifications/mark_read') ?>/' + notificationId,
          method: 'POST',
          dataType: 'json',
          success: function(data) {
            if (data.success) {
              $item.addClass('text-muted');
              $btn.remove(); // Remove the button
              fetchNotifications(); // Refresh to update badge
            } else {
              alert('Failed to mark as read: ' + data.message);
            }
          },
          error: function() {
            alert('Error marking notification as read');
          }
        });
      });

      // Handle View All modal
      $('#notificationsModal').on('show.bs.modal', function() {
        $.ajax({
          url: '<?= site_url('notifications/all') ?>',
          method: 'GET',
          dataType: 'json',
          success: function(data) {
            var modalHtml = '';
            if (data.notifications.length > 0) {
              data.notifications.forEach(function(notification) {
                var readClass = notification.is_read == 1 ? 'text-muted' : '';
                var markReadBtn = notification.is_read == 1 ? '<span class="badge bg-success">Read</span>' : '<button class="btn btn-sm btn-outline-secondary mark-read-modal-btn" data-id="' + notification.id + '">Mark as Read</button>';
                modalHtml += '<div class="notification-item p-3 border-bottom ' + readClass + '">' +
                  '<div class="d-flex justify-content-between align-items-start">' +
                  '<div>' +
                  '<p class="mb-1">' + notification.message + '</p>' +
                  '<small class="text-muted">' + new Date(notification.created_at).toLocaleString() + '</small>' +
                  '</div>' +
                  '<div>' + markReadBtn + '</div>' +
                  '</div>' +
                  '</div>';
              });
            } else {
              modalHtml = '<p class="text-center text-muted">No notifications</p>';
            }
            $('#notifications-modal-body').html(modalHtml);
          },
          error: function() {
            $('#notifications-modal-body').html('<p class="text-center text-danger">Error loading notifications</p>');
          }
        });
      });

      // Handle mark as read in modal
      $(document).on('click', '.mark-read-modal-btn', function(e) {
        e.preventDefault();
        var notificationId = $(this).data('id');
        var $btn = $(this);
        var $item = $btn.closest('.notification-item');

        $.ajax({
          url: '<?= site_url('notifications/mark_read') ?>/' + notificationId,
          method: 'POST',
          dataType: 'json',
          success: function(data) {
            if (data.success) {
              $item.addClass('text-muted');
              $btn.replaceWith('<span class="badge bg-success">Read</span>');
              fetchNotifications(); // Refresh to update badge
            } else {
              alert('Failed to mark as read: ' + data.message);
            }
          },
          error: function() {
            alert('Error marking notification as read');
          }
        });
      });
    });
  </script>
</body>
</html>
