<?php

namespace App\Controllers;

use App\Models\AssignmentSubmissionModel;
use App\Models\UserModel;

class StudentGrades extends BaseController
{
    protected $submissionModel;

    public function __construct()
    {
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

        $userModel = new UserModel();
        $user = $userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return redirect()->to('/dashboard')->with('error', 'User not found.');
        }

        $submissionsEnabled = $this->submissionModel->isReady();

        $rows = [];
        if ($submissionsEnabled) {
            try {
                $rows = $this->submissionModel->getGradesForStudent($user['id']);
            } catch (\Throwable $e) {
                $rows = [];
            }
        }

        $byCourse = [];
        foreach ($rows as $r) {
            $cid = (int) ($r['course_id'] ?? 0);
            if (!isset($byCourse[$cid])) {
                $byCourse[$cid] = [
                    'course_id' => $cid,
                    'course_title' => $r['course_title'] ?? '',
                    'instructor_name' => $r['instructor_name'] ?? 'N/A',
                    'items' => [],
                ];
            }
            $byCourse[$cid]['items'][] = $r;
        }

        return view('student/grades', [
            'courses' => array_values($byCourse),
            'submissionsEnabled' => $submissionsEnabled,
        ]);
    }
}

