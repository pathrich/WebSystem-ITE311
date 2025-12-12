<?php

namespace App\Controllers;

use App\Models\AssignmentModel;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;

class Assignment extends BaseController
{
    protected $assignmentModel;
    protected $courseModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
        $this->courseModel = new CourseModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

    /**
     * Display assignment upload form and list assignments for a course
     */
    public function index($course_id = null)
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

        // Get course
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

        // Get assignments for this course
        $assignments = $this->assignmentModel->getAssignmentsByCourse($course_id);

        return view('assignments/index', [
            'course' => $course,
            'assignments' => $assignments
        ]);
    }

    /**
     * Handle assignment upload (POST)
     */
    public function upload($course_id = null)
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

        // Get course
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

        // Validate input
        $validation = \Config\Services::validation();
        $validation->setRules([
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'permit_empty|max_length[1000]',
            'due_date' => 'permit_empty|valid_date',
            'max_score' => 'permit_empty|numeric|greater_than[0]',
            'assignment_file' => 'permit_empty|uploaded[assignment_file]|ext_in[assignment_file,pdf,doc,docx,ppt,pptx]|max_size[assignment_file,10240]'
        ]);

        if (!$validation->run($this->request->getPost())) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $title = trim($this->request->getPost('title'));
        $description = trim($this->request->getPost('description'));
        $dueDate = $this->request->getPost('due_date');
        $maxScore = $this->request->getPost('max_score') ? (float)$this->request->getPost('max_score') : 100.00;

        $filePath = null;
        $fileName = null;

        // Handle file upload if provided
        $file = $this->request->getFile('assignment_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $uploadPath = WRITEPATH . 'uploads/assignments/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $file->getRandomName();
            if ($file->move($uploadPath, $newName)) {
                $filePath = 'uploads/assignments/' . $newName;
                $fileName = $file->getName();
            }
        }

        // Save assignment to database
        $data = [
            'course_id' => $course_id,
            'title' => $title,
            'description' => $description ?: null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null,
            'max_score' => $maxScore,
            'created_by' => $user['id']
        ];

        $insertId = $this->assignmentModel->insertAssignment($data);
        if ($insertId) {
            // Notify enrolled students (if notifications table exists)
            try {
                $db = \Config\Database::connect();
                if ($db->tableExists('notifications')) {
                    $enrolledStudents = $db->table('enrollments')
                        ->select('user_id')
                        ->where('course_id', (int) $course_id)
                        ->where('status', 'approved')
                        ->get()
                        ->getResultArray();

                    if (!empty($enrolledStudents)) {
                        $notificationModel = new NotificationModel();
                        $courseTitle = (string) ($course['title'] ?? '');
                        $message = 'New assignment posted in ' . $courseTitle . ': ' . $title;
                        $now = date('Y-m-d H:i:s');

                        foreach ($enrolledStudents as $row) {
                            $studentId = (int) ($row['user_id'] ?? 0);
                            if ($studentId > 0) {
                                $notificationModel->insert([
                                    'user_id' => $studentId,
                                    'message' => $message,
                                    'is_read' => 0,
                                    'created_at' => $now,
                                ]);
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Do not fail assignment creation if notifications cannot be created
            }

            return redirect()->back()->with('success', 'Assignment created successfully!');
        } else {
            // Delete uploaded file if DB insert failed
            if ($filePath && file_exists(WRITEPATH . $filePath)) {
                unlink(WRITEPATH . $filePath);
            }
            return redirect()->back()->withInput()->with('error', 'Failed to create assignment.');
        }
    }

    /**
     * Download assignment file
     */
    public function download($assignment_id = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        if (!$assignment_id || !is_numeric($assignment_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid assignment ID.');
        }

        $assignment = $this->assignmentModel->find($assignment_id);
        if (!$assignment || !$assignment['file_path']) {
            return redirect()->to('/dashboard')->with('error', 'Assignment file not found.');
        }

        $filePath = WRITEPATH . $assignment['file_path'];
        if (!file_exists($filePath)) {
            return redirect()->to('/dashboard')->with('error', 'File not found on server.');
        }

        return $this->response->download($filePath, null);
    }

    /**
     * Delete assignment
     */
    public function delete($assignment_id = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'teacher' && $userRole !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        if (!$assignment_id || !is_numeric($assignment_id)) {
            return redirect()->to('/dashboard')->with('error', 'Invalid assignment ID.');
        }

        $assignment = $this->assignmentModel->find($assignment_id);
        if (!$assignment) {
            return redirect()->to('/dashboard')->with('error', 'Assignment not found.');
        }

        // Verify teacher is assigned to this course (unless admin)
        $course = $this->courseModel->find($assignment['course_id']);
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        
        if ($userRole !== 'admin' && $course['instructor_id'] != $user['id']) {
            return redirect()->to('/dashboard')->with('error', 'You are not authorized to delete this assignment.');
        }

        // Delete file if exists
        if ($assignment['file_path'] && file_exists(WRITEPATH . $assignment['file_path'])) {
            unlink(WRITEPATH . $assignment['file_path']);
        }

        // Delete from database
        if ($this->assignmentModel->delete($assignment_id)) {
            return redirect()->back()->with('success', 'Assignment deleted successfully.');
        } else {
            return redirect()->back()->with('error', 'Failed to delete assignment.');
        }
    }
}

