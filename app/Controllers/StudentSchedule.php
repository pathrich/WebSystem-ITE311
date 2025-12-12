<?php

namespace App\Controllers;

use App\Models\CourseScheduleModel;
use App\Models\UserModel;

class StudentSchedule extends BaseController
{
    protected $courseScheduleModel;

    public function __construct()
    {
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

        $rows = [];
        try {
            $rows = $this->courseScheduleModel->getStudentTimetable($user['id']);
        } catch (\Throwable $e) {
            $rows = [];
        }

        $dayOrder = [
            'Monday' => 1,
            'Tuesday' => 2,
            'Wednesday' => 3,
            'Thursday' => 4,
            'Friday' => 5,
            'Saturday' => 6,
            'Sunday' => 7,
        ];

        usort($rows, function ($a, $b) use ($dayOrder) {
            $dayA = $dayOrder[$a['day_of_week'] ?? ''] ?? 99;
            $dayB = $dayOrder[$b['day_of_week'] ?? ''] ?? 99;
            if ($dayA !== $dayB) {
                return $dayA <=> $dayB;
            }
            return strcmp((string) ($a['start_time'] ?? ''), (string) ($b['start_time'] ?? ''));
        });

        return view('student/schedule', [
            'rows' => $rows,
        ]);
    }
}
