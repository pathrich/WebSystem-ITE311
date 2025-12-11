<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\UserModel;

class StudentRecords extends BaseController
{
    protected $courseModel;
    protected $enrollmentModel;
    protected $userModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->userModel = new UserModel();
    }

    /**
     * Display student records for a teacher's courses
     */
    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();

        // Get teacher's courses
        $teacherCourses = $this->courseModel->where('instructor_id', $user['id'])->findAll();

        // Get enrolled students for each course
        $courseStudents = [];
        foreach ($teacherCourses as $course) {
            $enrollments = $this->enrollmentModel
                ->select('enrollments.*, users.name as student_name, users.email as student_email, users.id as student_id')
                ->join('users', 'users.id = enrollments.user_id')
                ->where('enrollments.course_id', $course['id'])
                ->where('enrollments.status', 'approved')
                ->orderBy('users.name', 'ASC')
                ->findAll();

            $courseStudents[$course['id']] = [
                'course' => $course,
                'students' => $enrollments
            ];
        }

        return view('student_records/index', [
            'courseStudents' => $courseStudents,
            'teacherCourses' => $teacherCourses
        ]);
    }

    /**
     * View students for a specific course
     */
    public function course($course_id = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        if (!$course_id || !is_numeric($course_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid course ID.');
        }

        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/dashboard')->with('error', 'Course not found.');
        }

        // Verify teacher is assigned to this course (unless admin)
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        if ($userRole !== 'admin' && $course['instructor_id'] != $user['id']) {
            return redirect()->to('/dashboard')->with('error', 'You are not assigned to this course.');
        }

        // Get enrolled students
        $enrollments = $this->enrollmentModel
            ->select('enrollments.*, users.name as student_name, users.email as student_email, users.id as student_id')
            ->join('users', 'users.id = enrollments.user_id')
            ->where('enrollments.course_id', $course_id)
            ->where('enrollments.status', 'approved')
            ->orderBy('users.name', 'ASC')
            ->findAll();

        // Get all students (not enrolled in this course) for dropdown
        $enrolledStudentIds = array_column($enrollments, 'student_id');
        $allStudents = $this->userModel
            ->where('role', 'student')
            ->where('status', 'active')
            ->orderBy('name', 'ASC')
            ->findAll();
        
        // Filter out already enrolled students
        $availableStudents = [];
        foreach ($allStudents as $student) {
            if (!in_array($student['id'], $enrolledStudentIds)) {
                $availableStudents[] = $student;
            }
        }

        return view('student_records/course', [
            'course' => $course,
            'students' => $enrollments,
            'availableStudents' => $availableStudents
        ]);
    }

    /**
     * Unenroll a student from a course (Teacher/Admin only)
     */
    public function unenroll()
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
                'message' => 'Access denied. Only teachers and admins can unenroll students.'
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

        // Verify teacher is assigned to this course (unless admin)
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        if ($userRole !== 'admin' && $course['instructor_id'] != $user['id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not authorized to unenroll students from this course.'
            ]);
        }

        // Get student info for notification
        $student = $this->userModel->find($enrollment['user_id']);

        // Delete enrollment
        if ($this->enrollmentModel->delete($enrollment_id)) {
            // Create notification for student
            $notificationModel = new \App\Models\NotificationModel();
            $studentNotification = [
                'user_id' => $enrollment['user_id'],
                'message' => 'You have been unenrolled from ' . $course['title'] . ' by ' . ($user['name'] ?? 'the instructor') . '.',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $notificationModel->insert($studentNotification);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Student unenrolled successfully.'
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to unenroll student.'
            ]);
        }
    }

    /**
     * Enroll a student to a course (Teacher/Admin only)
     */
    public function enrollStudent()
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
                'message' => 'Access denied. Only teachers and admins can enroll students.'
            ]);
        }

        $student_id = $this->request->getPost('student_id');
        $course_id = $this->request->getPost('course_id');

        if (!$student_id || !is_numeric($student_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Please select a student.'
            ]);
        }

        if (!$course_id || !is_numeric($course_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid course ID.'
            ]);
        }

        // Verify course exists
        $course = $this->courseModel->find($course_id);
        if (!$course) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        // Verify teacher is assigned to this course (unless admin)
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        if ($userRole !== 'admin' && $course['instructor_id'] != $user['id']) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'You are not authorized to enroll students in this course.'
            ]);
        }

        // Verify student exists and is active
        $student = $this->userModel->where('id', $student_id)->where('role', 'student')->where('status', 'active')->first();
        if (!$student) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Student not found or inactive.'
            ]);
        }

        // Check if already enrolled
        if ($this->enrollmentModel->isAlreadyEnrolled($student_id, $course_id)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Student is already enrolled in this course.'
            ]);
        }

        // Check for schedule conflicts with student's enrolled courses
        $scheduleModel = new \App\Models\ScheduleModel();
        $enrolledCourses = $this->enrollmentModel
            ->select('course_id')
            ->where('user_id', $student_id)
            ->where('status', 'approved')
            ->findAll();
        
        $conflictingCourses = [];
        foreach ($enrolledCourses as $enrollment) {
            if ($scheduleModel->hasScheduleConflict($course_id, $enrollment['course_id'])) {
                $conflictingCourse = $this->courseModel->find($enrollment['course_id']);
                if ($conflictingCourse) {
                    $conflictingCourses[] = $conflictingCourse['title'];
                }
            }
        }

        if (!empty($conflictingCourses)) {
            $conflictList = implode(', ', $conflictingCourses);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Schedule conflict detected! This course conflicts with the student\'s enrolled courses: ' . $conflictList . '. Please check the schedules and try again.'
            ]);
        }

        // Enroll student (auto-approved since teacher is enrolling)
        $enrollmentData = [
            'user_id' => $student_id,
            'course_id' => $course_id,
            'enrollment_date' => date('Y-m-d H:i:s'),
            'status' => 'approved' // Auto-approved when teacher enrolls
        ];

        if ($this->enrollmentModel->enrollUser($enrollmentData)) {
            // Get the newly created enrollment to get its ID
            $enrollment = $this->enrollmentModel
                ->where('user_id', $student_id)
                ->where('course_id', $course_id)
                ->orderBy('id', 'DESC')
                ->first();
            
            // Ensure status is approved (double-check)
            if ($enrollment && $enrollment['status'] !== 'approved') {
                $this->enrollmentModel->updateStatus($enrollment['id'], 'approved');
            }

            // Create notification for student
            $notificationModel = new \App\Models\NotificationModel();
            $studentNotification = [
                'user_id' => $student_id,
                'message' => 'You have been enrolled in ' . $course['title'] . ' by ' . ($user['name'] ?? 'the instructor') . '.',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $notificationModel->insert($studentNotification);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Student enrolled successfully.',
                'student' => [
                    'id' => $student['id'],
                    'name' => $student['name'],
                    'email' => $student['email']
                ]
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to enroll student.'
            ]);
        }
    }
}

