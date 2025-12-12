<?php

namespace App\Controllers;

use App\Models\CourseScheduleModel;
use App\Models\EnrollmentModel;
use App\Models\UserModel;

class StudentCourses extends BaseController
{
    protected $enrollmentModel;
    protected $courseScheduleModel;

    public function __construct()
    {
        $this->enrollmentModel = new EnrollmentModel();
        $this->courseScheduleModel = new CourseScheduleModel();
    }

    public function index()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'student') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $enrolledCourses = $this->enrollmentModel->getApprovedEnrollments($user['id']);
        $courseIds = array_column($enrolledCourses, 'course_id');

        $schedulesByCourse = [];
        try {
            $schedulesByCourse = $this->courseScheduleModel->getSchedulesForCourses($courseIds);
        } catch (\Throwable $e) {
            $schedulesByCourse = [];
        }

        foreach ($enrolledCourses as &$course) {
            $cid = (int) ($course['course_id'] ?? 0);
            $course['schedules'] = $schedulesByCourse[$cid] ?? [];
        }
        unset($course);

        return view('student/my_courses', [
            'courses' => $enrolledCourses,
        ]);
    }
}
