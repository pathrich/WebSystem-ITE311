<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\ScheduleModel;
use App\Models\AcademicYearModel;
use App\Models\SemesterModel;
use App\Models\TermModel;

class Course extends BaseController
{
    protected $enrollmentModel;
    protected $courseModel;
    protected $scheduleModel;

    public function __construct()
    {
        $this->enrollmentModel = new EnrollmentModel();
        $this->courseModel = new CourseModel();
        $this->scheduleModel = new ScheduleModel();
    }

    /**
     * Search for courses via GET 'keyword'.
     * If AJAX: return JSON list of matched courses.
     * If not AJAX: return the index view with all courses.
     */
    public function search()
    {
        $keyword = $this->request->getGet('keyword');
        $keyword = is_null($keyword) ? '' : trim((string) $keyword);
        $availableOnly = (bool) $this->request->getGet('availableOnly');

        $session = session();
        $userId = $session->get('userId');
        $userEmail = $session->get('userEmail');
        if (!$userId && $userEmail) {
            // derive userId from email if not set in session
            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('email', $userEmail)->first();
            if ($user) {
                $userId = $user['id'];
            }
        }
        $userRole = strtolower((string)$session->get('userRole'));

        // Use model to search courses; if availableOnly is set and student, restrict to un-enrolled courses for that user
        if ($availableOnly && $userId && $userRole === 'student') {
            $matched = $this->courseModel->searchAvailableCoursesForUser($keyword, $userId);
        } else {
            $matched = $this->courseModel->searchCourses($keyword);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($matched);
        }

        // Non-AJAX: show index page with all courses
        $courses = $this->courseModel->getAvailableCourses();
        return view('courses/index', ['courses' => $courses]);
    }

    /**
     * Display create course form
     */
    public function create()
    {
        // Check if user is logged in and is admin only
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        // Only admins can create courses - teachers wait for admin assignment
        if ($userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Only administrators can create courses. Please contact admin to assign courses to you.');
        }

        // Get academic years, semesters, and terms for dropdown
        $academicYearModel = new AcademicYearModel();
        $semesterModel = new SemesterModel();
        $termModel = new TermModel();
        
        // Check if tables exist, if not return empty arrays
        $academicYears = [];
        $semesters = [];
        $terms = [];
        
        try {
            $academicYears = $academicYearModel->getAllAcademicYears();
        } catch (\Exception $e) {
            // Table doesn't exist yet, will be empty
            log_message('debug', 'Academic years table not found: ' . $e->getMessage());
        }
        
        try {
            $allSemesters = $semesterModel->getAllSemesters();
            // Filter to only show 1st and 2nd Semester
            $semesters = array_filter($allSemesters, function($semester) {
                $name = strtolower($semester['name'] ?? '');
                return strpos($name, '1st') !== false || 
                       strpos($name, '2nd') !== false || 
                       strpos($name, '1') !== false || 
                       strpos($name, '2') !== false || 
                       strpos($name, 'first') !== false ||
                       strpos($name, 'second') !== false ||
                       $name === 'sem 1' ||
                       $name === 'sem 2' ||
                       $name === 'semester 1' ||
                       $name === 'semester 2';
            });
            $semesters = array_values($semesters); // Re-index array
        } catch (\Exception $e) {
            // Table doesn't exist yet, will be empty
            log_message('debug', 'Semesters table not found: ' . $e->getMessage());
            $semesters = [];
        }
        
        try {
            $terms = $termModel->getAllTerms();
        } catch (\Exception $e) {
            // Table doesn't exist yet, will be empty
            log_message('debug', 'Terms table not found: ' . $e->getMessage());
        }
        
        $data = [
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'terms' => $terms,
        ];
        
        return view('courses/create', $data);
    }

    /**
     * Store new course
     */
    public function store()
    {
        // Check if user is logged in and is admin only
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        // Only admins can create courses - teachers wait for admin assignment
        if ($userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied. Only administrators can create courses. Please contact admin to assign courses to you.');
        }

        // Get user ID
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $courseNumberInput = trim((string) $this->request->getPost('course_number'));
        $description = trim((string) $this->request->getPost('description'));
        $academicYearId = $this->request->getPost('academic_year_id');
        $semesterId = $this->request->getPost('semester_id');
        $termId = $this->request->getPost('term_id');

        // Format CN: Add "CN-" prefix, ensure exactly 4 digits
        $courseNumber = '';
        if (!empty($courseNumberInput)) {
            // Remove any existing CN- prefix if user somehow added it
            $numbersOnly = preg_replace('/[^0-9]/', '', $courseNumberInput);
            
            // Validate: must be exactly 4 digits
            if (strlen($numbersOnly) !== 4) {
                return redirect()->back()->withInput()->with('error', 'Control Number (CN) must be exactly 4 digits (e.g., 0001).');
            }
            
            // Pad with zeros to ensure 4 digits (e.g., 1 becomes 0001)
            $paddedNumber = str_pad($numbersOnly, 4, '0', STR_PAD_LEFT);
            $courseNumber = 'CN-' . $paddedNumber;
        }

        // Validation
        if ($title === '' || $courseNumber === '') {
            return redirect()->back()->withInput()->with('error', 'Course title and control number (CN) are required.');
        }

        // Check if CN is unique
        $existingCourse = $this->courseModel->where('course_number', $courseNumber)->first();
        if ($existingCourse) {
            return redirect()->back()->withInput()->with('error', 'Control Number (CN) already exists. Please use a different number.');
        }

        $data = [
            'title' => $title,
            'course_number' => $courseNumber,
            'description' => $description,
            'instructor_id' => $user['id'],
        ];
        
        // Only add academic_year_id, semester_id, and term_id if they exist in the table
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('courses');
        $hasAcademicYearField = false;
        $hasSemesterField = false;
        $hasTermField = false;
        
        foreach ($fields as $field) {
            if ($field->name === 'academic_year_id') {
                $hasAcademicYearField = true;
            }
            if ($field->name === 'semester_id') {
                $hasSemesterField = true;
            }
            if ($field->name === 'term_id') {
                $hasTermField = true;
            }
            if ($field->name === 'course_number') {
                $hasCourseNumberField = true;
            }
        }
        
        // Only include course_number if field exists
        if (!$hasCourseNumberField) {
            unset($data['course_number']);
        }
        
        if ($hasAcademicYearField && $academicYearId) {
            $data['academic_year_id'] = (int)$academicYearId;
        }
        if ($hasSemesterField && $semesterId) {
            $data['semester_id'] = (int)$semesterId;
        }
        if ($hasTermField && $termId) {
            $data['term_id'] = (int)$termId;
        }

        // Start database transaction
        $db->transStart();

        try {
            // Insert course
            $courseId = $this->courseModel->insert($data, true);
            
            if (!$courseId) {
                throw new \Exception('Failed to create course.');
            }

            // Handle schedules (time) - only if schedules table exists
            try {
                $scheduleDays = $this->request->getPost('schedule_day');
                $scheduleStartTimes = $this->request->getPost('schedule_start_time');
                $scheduleEndTimes = $this->request->getPost('schedule_end_time');

                if ($scheduleDays && is_array($scheduleDays)) {
                    foreach ($scheduleDays as $index => $day) {
                        if (!empty($day) && !empty($scheduleStartTimes[$index]) && !empty($scheduleEndTimes[$index])) {
                            $scheduleData = [
                                'course_id' => $courseId,
                                'day_of_week' => $day,
                                'start_time' => $scheduleStartTimes[$index],
                                'end_time' => $scheduleEndTimes[$index],
                                'room' => null, // Room field removed
                            ];
                            $this->scheduleModel->insert($scheduleData);
                        }
                    }
                }
            } catch (\Exception $e) {
                // Schedules table might not exist, log but don't fail
                log_message('debug', 'Could not save schedules: ' . $e->getMessage());
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed.');
            }

            return redirect()->to('/dashboard')->with('success', 'Course created successfully!');
        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', 'Failed to create course: ' . $e->getMessage());
        }
    }

    /**
     * Display edit course form
     */
    public function edit($course_id = null)
    {
        // Check if user is logged in and is teacher or admin
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        $userRole = $session->get('userRole');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Validate course_id
        if (!$course_id || !is_numeric($course_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid course ID.');
        }

        // Get course
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/dashboard')->with('error', 'Course not found.');
        }

        // Check if user is the instructor or admin
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        if ($userRole !== 'admin' && $course['instructor_id'] !== $user['id']) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        return view('course/edit', ['course' => $course]);
    }

    /**
     * Update course
     */
    public function update($course_id = null)
    {
        // Check if user is logged in and is teacher or admin
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        $userRole = $session->get('userRole');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Validate course_id
        if (!$course_id || !is_numeric($course_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid course ID.');
        }

        // Get course
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/dashboard')->with('error', 'Course not found.');
        }

        // Check if user is the instructor or admin
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        if ($userRole !== 'admin' && $course['instructor_id'] !== $user['id']) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));

        if ($title === '' || $description === '') {
            return redirect()->back()->withInput()->with('error', 'All fields are required.');
        }

        $data = [
            'title' => $title,
            'description' => $description
        ];

        if ($this->courseModel->update($course_id, $data)) {
            return redirect()->to('/dashboard')->with('success', 'Course updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update course.');
        }
    }

    /**
     * Delete course
     */
    public function delete($course_id = null)
    {
        // Check if user is logged in and is teacher or admin
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        $userRole = $session->get('userRole');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Validate course_id
        if (!$course_id || !is_numeric($course_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid course ID.');
        }

        // Get course
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/dashboard')->with('error', 'Course not found.');
        }

        // Check if user is the instructor or admin
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        if ($userRole !== 'admin' && $course['instructor_id'] !== $user['id']) {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        if ($this->courseModel->delete($course_id)) {
            return redirect()->to('/dashboard')->with('success', 'Course deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to delete course.');
        }
    }

    /**
     * Handle the AJAX request for course enrollment
     */
    public function enroll()
    {
        // Check if the user is logged in
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in to enroll in courses.'
            ]);
        }

        // Get user ID from session
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        if (!$user) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        $user_id = $user['id'];

        // Receive the course_id from the POST request
        $course_id = $this->request->getPost('course_id');

        // Validate course ID
        if (!$course_id || !is_numeric($course_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID.'
            ]);
        }

        // Check if course exists
        if (!$this->courseModel->where('id', $course_id)->first()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Check if the user is already enrolled
        if ($this->enrollmentModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are already enrolled in this course.'
            ]);
        }

        // Check user role for schedule conflict
        $userRole = strtolower($user['role'] ?? 'student');
        
        if ($userRole === 'student') {
            // Check if student has schedule conflict with enrolled courses
            $conflictCheck = $this->scheduleModel->checkStudentScheduleConflict($user_id, $course_id);
            if ($conflictCheck['has_conflict']) {
                $conflictingCourse = $this->courseModel->find($conflictCheck['conflicting_course_id']);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Schedule conflict detected! This course conflicts with your enrolled course: ' . ($conflictingCourse['title'] ?? 'Unknown') . '. Please choose a different course or time slot.'
                ]);
            }
        } elseif ($userRole === 'teacher') {
            // Check if teacher has schedule conflict with assigned courses
            $conflictCheck = $this->scheduleModel->checkTeacherScheduleConflict($user_id, $course_id);
            if ($conflictCheck['has_conflict']) {
                $conflictingCourse = $this->courseModel->find($conflictCheck['conflicting_course_id']);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Schedule conflict detected! This course conflicts with your assigned course: ' . ($conflictingCourse['title'] ?? 'Unknown') . '. Please choose a different course or time slot.'
                ]);
            }
        }

        // Insert the new enrollment record with the current timestamp
        // Set status to 'pending' for students (requires teacher approval)
        $enrollmentData = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrollment_date' => date('Y-m-d H:i:s'),
            'status' => ($userRole === 'student') ? 'pending' : 'approved' // Students need approval, teachers/admins auto-approved
        ];

        if ($this->enrollmentModel->enrollUser($enrollmentData)) {
            // Get course information for response with instructor name
            $course = $this->courseModel->select('courses.*, users.name as instructor_name')
                ->join('users', 'users.id = courses.instructor_id')
                ->where('courses.id', $course_id)
                ->first();
            
            // Get course schedules for display
            $schedules = $this->scheduleModel->getSchedulesByCourse($course_id);
            $course['schedules'] = $schedules;

            // Create notification for the user
            $notificationModel = new \App\Models\NotificationModel();
            
            if ($userRole === 'student') {
                // For students, enrollment is pending approval
                $notificationData = [
                    'user_id' => $user_id,
                    'message' => 'Your enrollment request for ' . $course['title'] . ' is pending teacher approval.',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $notificationModel->insert($notificationData);
                
                // Also notify the teacher
                if (!empty($course['instructor_id'])) {
                    $teacherNotification = [
                        'user_id' => $course['instructor_id'],
                        'message' => 'New enrollment request from ' . ($user['name'] ?? $user['email']) . ' for ' . $course['title'] . '.',
                        'created_at' => date('Y-m-d H:i:s')
                    ];
                    $notificationModel->insert($teacherNotification);
                }
                
                $successMessage = 'Enrollment request submitted for ' . $course['title'] . '. Waiting for teacher approval.';
            } else {
                // For teachers/admins, auto-approved
                $notificationData = [
                    'user_id' => $user_id,
                    'message' => 'You have been enrolled in ' . $course['title'] . '.',
                    'created_at' => date('Y-m-d H:i:s')
                ];
                $notificationModel->insert($notificationData);
                $successMessage = 'Successfully enrolled in ' . $course['title'] . '!';
            }

            // Return a JSON response indicating success
            return $this->response->setJSON([
                'success' => true,
                'message' => $successMessage,
                'course' => $course,
                'status' => $enrollmentData['status']
            ]);
        } else {
            // Return a JSON response indicating failure
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to enroll in course. Please try again.'
            ]);
        }
    }

    /**
     * Approve a pending enrollment (Teacher only)
     */
    public function approveEnrollment()
    {
        $this->response->setContentType('application/json');
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ]);
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only teachers can approve enrollments.'
            ]);
        }

        $enrollment_id = $this->request->getPost('enrollment_id');
        if (!$enrollment_id || !is_numeric($enrollment_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid enrollment ID.'
            ]);
        }

        // Get enrollment details
        $enrollment = $this->enrollmentModel->find($enrollment_id);
        if (!$enrollment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enrollment not found.'
            ]);
        }

        // Get course to verify teacher
        $course = $this->courseModel->find($enrollment['course_id']);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Get current user
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        // Verify teacher is assigned to this course (unless admin)
        if ($userRole !== 'admin' && $course['instructor_id'] != $user['id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not authorized to approve enrollments for this course.'
            ]);
        }

        // Update enrollment status
        if ($this->enrollmentModel->updateStatus($enrollment_id, 'approved')) {
            // Create notification for student
            $notificationModel = new \App\Models\NotificationModel();
            $studentNotification = [
                'user_id' => $enrollment['user_id'],
                'message' => 'Your enrollment request for ' . $course['title'] . ' has been approved.',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $notificationModel->insert($studentNotification);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Enrollment approved successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to approve enrollment.'
            ]);
        }
    }

    /**
     * Reject a pending enrollment (Teacher only)
     */
    public function rejectEnrollment()
    {
        $this->response->setContentType('application/json');
        
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You must be logged in.'
            ]);
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Access denied. Only teachers can reject enrollments.'
            ]);
        }

        $enrollment_id = $this->request->getPost('enrollment_id');
        if (!$enrollment_id || !is_numeric($enrollment_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid enrollment ID.'
            ]);
        }

        // Get enrollment details
        $enrollment = $this->enrollmentModel->find($enrollment_id);
        if (!$enrollment) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Enrollment not found.'
            ]);
        }

        // Get course to verify teacher
        $course = $this->courseModel->find($enrollment['course_id']);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Get current user
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        // Verify teacher is assigned to this course (unless admin)
        if ($userRole !== 'admin' && $course['instructor_id'] != $user['id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not authorized to reject enrollments for this course.'
            ]);
        }

        // Update enrollment status
        if ($this->enrollmentModel->updateStatus($enrollment_id, 'rejected')) {
            // Create notification for student
            $notificationModel = new \App\Models\NotificationModel();
            $studentNotification = [
                'user_id' => $enrollment['user_id'],
                'message' => 'Your enrollment request for ' . $course['title'] . ' has been rejected.',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $notificationModel->insert($studentNotification);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Enrollment rejected successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to reject enrollment.'
            ]);
        }
    }
}
