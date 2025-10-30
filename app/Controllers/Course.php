<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;

class Course extends BaseController
{
    protected $enrollmentModel;
    protected $courseModel;

    public function __construct()
    {
        $this->enrollmentModel = new EnrollmentModel();
        $this->courseModel = new CourseModel();
    }

    /**
     * Display create course form
     */
    public function create()
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

        return view('course/create');
    }

    /**
     * Store new course
     */
    public function store()
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

        // Get user ID
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $title = trim((string) $this->request->getPost('title'));
        $description = trim((string) $this->request->getPost('description'));

        if ($title === '' || $description === '') {
            return redirect()->back()->withInput()->with('error', 'All fields are required.');
        }

        $data = [
            'title' => $title,
            'description' => $description,
            'instructor_id' => $user['id']
        ];

        if ($this->courseModel->insert($data)) {
            return redirect()->to('/dashboard')->with('success', 'Course created successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to create course.');
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

        // Insert the new enrollment record with the current timestamp
        $enrollmentData = [
            'user_id' => $user_id,
            'course_id' => $course_id
        ];

        if ($this->enrollmentModel->enrollUser($enrollmentData)) {
            // Get course information for response
            $course = $this->courseModel->where('id', $course_id)->first();

            // Create notification for the user
            $notificationModel = new \App\Models\NotificationModel();
            $notificationData = [
                'user_id' => $user_id,
                'message' => 'You have been enrolled in ' . $course['title'] . '.',
                'created_at' => date('Y-m-d H:i:s')
            ];
            $notificationModel->insert($notificationData);

            // Return a JSON response indicating success
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Successfully enrolled in ' . $course['title'] . '!',
                'course' => $course
            ]);
        } else {
            // Return a JSON response indicating failure
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to enroll in course. Please try again.'
            ]);
        }
    }
}
