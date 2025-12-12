<?php

namespace App\Controllers;

use App\Models\AssignmentModel;
use App\Models\AssignmentSubmissionModel;
use App\Models\EnrollmentModel;
use App\Models\UserModel;

class StudentAssignments extends BaseController
{
    protected $assignmentModel;
    protected $enrollmentModel;
    protected $submissionModel;

    public function __construct()
    {
        $this->assignmentModel = new AssignmentModel();
        $this->enrollmentModel = new EnrollmentModel();
        $this->submissionModel = new AssignmentSubmissionModel();
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

        $submissionsEnabled = $this->submissionModel->isReady();

        $userModel = new UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $courses = $this->enrollmentModel->getApprovedEnrollments($user['id']);
        $courseIds = array_values(array_filter(array_unique(array_map('intval', array_column($courses, 'course_id')))));

        $assignments = [];
        if (!empty($courseIds)) {
            $assignments = $this->assignmentModel->getAssignmentsForCourses($courseIds);
        }

        $assignmentIds = array_values(array_filter(array_unique(array_map('intval', array_column($assignments, 'id')))));
        $submissionsByAssignment = $submissionsEnabled
            ? $this->submissionModel->getSubmissionsForStudentByAssignments($user['id'], $assignmentIds)
            : [];

        foreach ($assignments as &$a) {
            $aid = (int) ($a['id'] ?? 0);
            $a['submission'] = $submissionsByAssignment[$aid] ?? null;
        }
        unset($a);

        $byCourse = [];
        foreach ($courses as $c) {
            $cid = (int) ($c['course_id'] ?? 0);
            $byCourse[$cid] = [
                'course_id' => $cid,
                'course_title' => $c['title'] ?? '',
                'instructor_name' => $c['instructor_name'] ?? 'N/A',
                'assignments' => [],
            ];
        }

        foreach ($assignments as $a) {
            $cid = (int) ($a['course_id'] ?? 0);
            if (!isset($byCourse[$cid])) {
                $byCourse[$cid] = [
                    'course_id' => $cid,
                    'course_title' => $a['course_title'] ?? '',
                    'instructor_name' => $a['instructor_name'] ?? 'N/A',
                    'assignments' => [],
                ];
            }
            $byCourse[$cid]['assignments'][] = $a;
        }

        return view('student/assignments', [
            'courses' => array_values($byCourse),
            'submissionsEnabled' => $submissionsEnabled,
        ]);
    }

    public function submit($assignmentId = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'student') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        if (!$this->submissionModel->isReady()) {
            return redirect()->to('/assignments')->with('error', 'Assignments submission is not available yet. Please run database migrations.');
        }

        $assignmentId = (int) $assignmentId;
        if ($assignmentId <= 0) {
            return redirect()->back()->with('error', 'Invalid assignment.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $assignment = $this->assignmentModel->find($assignmentId);
        if (!$assignment) {
            return redirect()->back()->with('error', 'Assignment not found.');
        }

        $enrolled = $this->enrollmentModel
            ->where('user_id', $user['id'])
            ->where('course_id', $assignment['course_id'])
            ->where('status', 'approved')
            ->first();

        if (!$enrolled) {
            return redirect()->back()->with('error', 'You are not enrolled in this course.');
        }

        $maxSizeMb = 20;
        $maxSizeBytes = $maxSizeMb * 1024 * 1024;
        $phpUploadMax = $this->toBytes((string) ini_get('upload_max_filesize'));
        $phpPostMax = $this->toBytes((string) ini_get('post_max_size'));
        $phpMax = 0;
        if ($phpUploadMax > 0 && $phpPostMax > 0) {
            $phpMax = min($phpUploadMax, $phpPostMax);
        } elseif ($phpUploadMax > 0) {
            $phpMax = $phpUploadMax;
        } elseif ($phpPostMax > 0) {
            $phpMax = $phpPostMax;
        }
        $effectiveMaxBytes = ($phpMax > 0) ? min($maxSizeBytes, $phpMax) : $maxSizeBytes;
        $effectiveMaxMb = max(1, (int) floor($effectiveMaxBytes / (1024 * 1024)));

        $file = $this->request->getFile('submission_file');

        if (!$file) {
            return redirect()->back()->with('error', 'Please upload a file.');
        }

        if ($file->hasMoved()) {
            return redirect()->back()->with('error', 'Upload failed. Please try again.');
        }

        if (!$file->isValid()) {
            $err = $file->getError();
            if (in_array($err, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
                return redirect()->back()->with('error', 'File too large. Max ' . $effectiveMaxMb . 'MB. If it still fails, increase upload_max_filesize and post_max_size in php.ini.');
            }
            if ($err === UPLOAD_ERR_NO_FILE) {
                return redirect()->back()->with('error', 'Please upload a file.');
            }
            return redirect()->back()->with('error', 'Please upload a valid file.');
        }

        $ext = strtolower((string) $file->getClientExtension());
        $allowed = ['pdf', 'doc', 'docx', 'ppt', 'pptx'];
        if (!in_array($ext, $allowed, true)) {
            return redirect()->back()->with('error', 'Invalid file type. Allowed: pdf, doc, docx, ppt, pptx.');
        }

        $size = (int) $file->getSize();
        if ($size <= 0) {
            return redirect()->back()->with('error', 'Upload failed. Please try again.');
        }
        if ($size > $effectiveMaxBytes) {
            return redirect()->back()->with('error', 'File too large. Max ' . $effectiveMaxMb . 'MB.');
        }

        $uploadPath = WRITEPATH . 'uploads/assignment_submissions/' . (int) $user['id'] . '/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $existing = $this->submissionModel->getSubmissionForStudent($assignmentId, $user['id']);
        if ($existing && !empty($existing['file_path'])) {
            $oldPath = WRITEPATH . $existing['file_path'];
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
        }

        $newName = $file->getRandomName();
        if (!$file->move($uploadPath, $newName)) {
            return redirect()->back()->with('error', 'Failed to upload file.');
        }

        $relativePath = 'uploads/assignment_submissions/' . (int) $user['id'] . '/' . $newName;
        $originalName = $file->getClientName();

        $saveData = [
            'assignment_id' => $assignmentId,
            'student_id' => (int) $user['id'],
            'file_path' => $relativePath,
            'file_name' => $originalName,
            'submitted_at' => date('Y-m-d H:i:s'),
        ];

        $ok = $this->submissionModel->upsertSubmission($saveData);
        if (!$ok) {
            $full = WRITEPATH . $relativePath;
            if (file_exists($full)) {
                @unlink($full);
            }
            return redirect()->back()->with('error', 'Failed to save submission.');
        }

        return redirect()->back()->with('success', 'Submission uploaded successfully.');
    }

    private function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        if (is_numeric($value)) {
            return (int) $value;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) substr($value, 0, -1);
        if ($number <= 0) {
            return 0;
        }

        switch ($unit) {
            case 'g':
                return (int) ($number * 1024 * 1024 * 1024);
            case 'm':
                return (int) ($number * 1024 * 1024);
            case 'k':
                return (int) ($number * 1024);
            default:
                return (int) $number;
        }
    }

    public function downloadSubmission($submissionId = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'You must be logged in.');
        }

        $userRole = strtolower($session->get('userRole') ?? '');
        if ($userRole !== 'student') {
            return redirect()->to('/dashboard')->with('error', 'Access denied.');
        }

        if (!$this->submissionModel->isReady()) {
            return redirect()->to('/assignments')->with('error', 'Assignments submission is not available yet. Please run database migrations.');
        }

        $submissionId = (int) $submissionId;
        if ($submissionId <= 0) {
            return redirect()->to('/dashboard')->with('error', 'Invalid submission ID.');
        }

        $userModel = new UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $submission = $this->submissionModel->find($submissionId);
        if (!$submission || (int) ($submission['student_id'] ?? 0) !== (int) $user['id']) {
            return redirect()->to('/dashboard')->with('error', 'Submission not found.');
        }

        if (empty($submission['file_path'])) {
            return redirect()->to('/dashboard')->with('error', 'File not found.');
        }

        $filePath = WRITEPATH . $submission['file_path'];
        if (!file_exists($filePath)) {
            return redirect()->to('/dashboard')->with('error', 'File not found on server.');
        }

        return $this->response->download($filePath, null);
    }
}
