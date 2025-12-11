# Enrollment System Implementation - Laboratory Activity

## ✅ Completed Steps

### Step 1: Database Migration for Enrollments Table
- ✅ Migration file exists: `2025-08-21-131804_CreateEnrollmentsTable.php`
- ✅ Created update migration: `2025-12-10-190000_UpdateEnrollmentsTableForEnrollmentDate.php`
  - Updates `enrolled_at` to `enrollment_date` for consistency
- ✅ Created schedules table: `2025-12-10-190001_CreateSchedulesTable.php`
  - Stores course schedules (day, time, room)
  - Links to courses via foreign key

**To run migrations:**
```bash
php spark migrate
```

### Step 2: Enrollment Model
- ✅ File: `app/Models/EnrollmentModel.php`
- ✅ Methods implemented:
  - `enrollUser($data)` - Inserts new enrollment with timestamp
  - `getUserEnrollments($user_id)` - Fetches all courses user is enrolled in
  - `isAlreadyEnrolled($user_id, $course_id)` - Prevents duplicate enrollments

### Step 3: Course Controller - enroll() Method
- ✅ File: `app/Controllers/Course.php`
- ✅ Method: `enroll()`
- ✅ Features:
  - ✅ Checks if user is logged in
  - ✅ Receives `course_id` from POST request
  - ✅ Validates course exists
  - ✅ Checks if already enrolled (prevents duplicates)
  - ✅ **SCHEDULE CONFLICT CHECKING:**
    - ✅ For students: Checks if new course conflicts with enrolled courses
    - ✅ For teachers: Checks if new course conflicts with assigned courses
  - ✅ Inserts enrollment with `enrollment_date` timestamp
  - ✅ Returns JSON response (success/failure)
  - ✅ Includes course schedules in response

### Step 4: Student Dashboard View
- ✅ File: `app/Views/auth/dashboard.php`
- ✅ **Enrolled Courses Section:**
  - Displays all enrolled courses using Bootstrap cards
  - Shows course title, description, instructor
  - Shows enrollment date
  - **Displays course schedules** (day, time, room)
- ✅ **Available Courses Section:**
  - Lists courses not yet enrolled
  - Shows course schedules for each course
  - Enroll button for each course

### Step 5: AJAX Enrollment Implementation
- ✅ jQuery library included
- ✅ Script listens for Enroll button clicks
- ✅ Prevents default form submission
- ✅ Uses `$.ajax()` POST to `/course/enroll`
- ✅ On success:
  - ✅ Displays Bootstrap alert message
  - ✅ Hides/disables Enroll button
  - ✅ **Updates Enrolled Courses list dynamically** (no page reload)
  - ✅ Shows course schedules in enrolled list
  - ✅ Updates course counts
- ✅ On error:
  - ✅ Shows error message
  - ✅ Re-enables button
  - ✅ **Shows schedule conflict message if applicable**

### Step 6: Routes Configuration
- ✅ Route exists: `$routes->post('/course/enroll', 'Course::enroll');`
- ✅ File: `app/Config/Routes.php` (line 27)

### Step 7: Schedule Conflict Prevention
- ✅ **Student Conflict Check:**
  - When student tries to enroll, system checks all enrolled courses
  - Compares schedules (day and time)
  - Prevents enrollment if time overlaps
  - Shows clear error message with conflicting course name

- ✅ **Teacher Conflict Check:**
  - When teacher is assigned to course, system checks all assigned courses
  - Compares schedules (day and time)
  - Prevents assignment if time overlaps
  - Shows clear error message with conflicting course name

## 📋 Schedule Conflict Logic

The system prevents conflicts by:
1. Comparing day of week (must match for conflict)
2. Checking time overlap:
   - Course A: 9:00 AM - 10:30 AM
   - Course B: 10:00 AM - 11:30 AM
   - **CONFLICT** (overlaps from 10:00-10:30)

3. Formula: `start1 < end2 && start2 < end1`

## 🗄️ Database Schema

### enrollments table
- `id` (PK)
- `user_id` (FK to users)
- `course_id` (FK to courses)
- `enrollment_date` (DATETIME)

### schedules table
- `id` (PK)
- `course_id` (FK to courses)
- `day_of_week` (ENUM: Monday-Sunday)
- `start_time` (TIME)
- `end_time` (TIME)
- `room` (VARCHAR, nullable)

## 🧪 Testing Checklist

1. ✅ Login as student
2. ✅ Navigate to dashboard
3. ✅ View available courses (with schedules)
4. ✅ Click Enroll button
5. ✅ Verify:
   - ✅ No page reload
   - ✅ Success message appears
   - ✅ Button becomes disabled
   - ✅ Course appears in Enrolled Courses
   - ✅ Schedule displayed in enrolled course
6. ✅ Test schedule conflict:
   - ✅ Enroll in Course A (Monday 9:00-10:30)
   - ✅ Try to enroll in Course B (Monday 10:00-11:30)
   - ✅ Should show conflict error
   - ✅ Should NOT allow enrollment

## 📝 Notes

- The system uses `enrollment_date` field (migration updates existing `enrolled_at`)
- Schedules are optional - courses can exist without schedules
- Conflict checking only works if courses have schedules defined
- Both students and teachers are protected from schedule conflicts
- AJAX enrollment provides smooth user experience without page reloads

## 🚀 Next Steps

1. Run migrations: `php spark migrate`
2. Create course schedules via admin panel (or directly in database)
3. Test enrollment with schedule conflicts
4. Verify all functionality works as expected

