<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;

class Materials extends BaseController
{
    protected $materialModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->materialModel = new MaterialModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Display upload form and handle file uploads for a specific course
     */
    public function upload($course_id = null)
    {
        // Check if user is logged in and is admin/teacher
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

        // Check if course exists
        $courseModel = new \App\Models\CourseModel();
        $course = $courseModel->find($course_id);
        if (!$course) {
            return redirect()->to('/dashboard')->with('error', 'Course not found.');
        }

        // Handle POST request for file upload
        if ($this->request->getMethod() === 'POST') {
            return $this->handleUpload($course_id);
        }

        // Get existing materials for this course
        $materials = $this->materialModel->getMaterialsByCourse($course_id);

        // Display upload form
        return view('materials/upload', [
            'course' => $course,
            'materials' => $materials
        ]);
    }

    /**
     * Handle file upload
     */
    private function handleUpload($course_id)
    {
        // Load upload library
        $file = $this->request->getFile('material_file');

        // Validate file
        if (!$file->isValid()) {
            return redirect()->back()->with('error', 'Invalid file upload.');
        }

        // Check file type
        $allowedTypes = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];
        if (!in_array($file->getExtension(), $allowedTypes)) {
            return redirect()->back()->with('error', 'Invalid file type. Only PDF, PPT, DOC, DOCX files are allowed.');
        }

        // Check file size (max 10MB)
        if ($file->getSize() > 10 * 1024 * 1024) {
            return redirect()->back()->with('error', 'File size too large. Maximum size is 10MB.');
        }

        // Create upload directory if it doesn't exist
        $uploadPath = WRITEPATH . 'uploads/materials/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Generate unique filename
        $newName = $file->getRandomName();
        $filePath = 'uploads/materials/' . $newName;

        // Move file to upload directory
        if ($file->move($uploadPath, $newName)) {
            // Save to database
            $data = [
                'course_id' => $course_id,
                'file_name' => $file->getName(),
                'file_path' => $filePath
            ];

            if ($this->materialModel->insertMaterial($data)) {
                return redirect()->back()->with('success', 'Material uploaded successfully!');
            } else {
                // Delete uploaded file if DB insert failed
                unlink($uploadPath . $newName);
                return redirect()->back()->with('error', 'Failed to save material to database.');
            }
        } else {
            return redirect()->back()->with('error', 'Failed to upload file.');
        }
    }

    /**
     * Delete a material
     */
    public function delete($material_id = null)
    {
        // Check if user is logged in and is admin/teacher
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to access this page.');
        }

        $userRole = $session->get('userRole');
        if ($userRole !== 'admin' && $userRole !== 'teacher') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        // Validate material_id
        if (!$material_id || !is_numeric($material_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid material ID.');
        }

        // Get material
        $material = $this->materialModel->getMaterialById($material_id);
        if (!$material) {
            return redirect()->to('/dashboard')->with('error', 'Material not found.');
        }

        // Delete file from filesystem
        $filePath = WRITEPATH . $material['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete from database
        if ($this->materialModel->deleteMaterial($material_id)) {
            return redirect()->back()->with('success', 'Material deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to delete material.');
        }
    }

    /**
     * Download a material (for enrolled students)
     */
    public function download($material_id = null)
    {
        // Check if user is logged in
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in to download materials.');
        }

        // Validate material_id
        if (!$material_id || !is_numeric($material_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid material ID.');
        }

        // Get material
        $material = $this->materialModel->getMaterialById($material_id);
        if (!$material) {
            return redirect()->to('/dashboard')->with('error', 'Material not found.');
        }

        // Get user ID
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $user_id = $user['id'];

        // Check if user is enrolled in the course
        if (!$this->enrollmentModel->isAlreadyEnrolled($user_id, $material['course_id'])) {
            return redirect()->to('/dashboard')->with('error', 'You must be enrolled in this course to download materials.');
        }

        // Check if file exists
        $filePath = WRITEPATH . $material['file_path'];
        if (!file_exists($filePath)) {
            return redirect()->to('/dashboard')->with('error', 'File not found on server.');
        }

        // Return file for download
        return $this->response->download($filePath, null, true);
    }
}
