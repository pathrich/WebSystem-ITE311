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
