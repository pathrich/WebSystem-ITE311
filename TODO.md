# Notifications System Implementation

## Step 1: Database Setup
- [x] Create migration file CreateNotificationsTable.php with fields: id (primary, auto-increment), user_id (INT, FK to users.id), message (VARCHAR), is_read (TINYINT, default 0), created_at (DATETIME)
- [x] Run the migration to create the table (already exists)

## Step 2: Notification Model
- [x] Create NotificationModel.php with methods: getUnreadCount($userId), getNotificationsForUser($userId), markAsRead($notificationId)

## Step 3: Update Base Controller / Layout
- [x] Update BaseController.php to load NotificationModel and pass unread count to views for logged-in users
- [x] Update template.php to add notification badge placeholder in navbar

## Step 4: Notifications Controller & API
- [x] Create Notifications.php controller with get() and mark_as_read($id) methods
- [x] Add routes in Routes.php: /notifications (GET), /notifications/mark_read/:num (POST)

## Step 5: Notification UI
- [x] Update template.php to add Bootstrap dropdown in navbar with badge and list
- [x] Add jQuery AJAX to fetch notifications on page load, update badge and dropdown, handle mark as read

## Step 6: Trigger Updates
- [x] Update Course.php enroll method to create notification after successful enrollment

## Step 7: Test
- [x] Log in as student, enroll in course, verify badge, dropdown, mark as read
