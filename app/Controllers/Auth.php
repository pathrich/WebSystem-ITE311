<?php

namespace App\Controllers;

class Auth extends BaseController
{

    public function login()
    {
        $session = session();
        if ($session->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/login');
    }

    public function attempt()
    {
        $request = $this->request;
        $email = trim((string) $request->getPost('email'));
        $password = (string) $request->getPost('password');

        // Try database user first
        $userModel = new \App\Models\UserModel();
        $user = $userModel->where('email', $email)->first();

        // Check if user exists and is active
        if ($user && $user['status'] === 'active' && password_verify($password, $user['password'])) {
            $session = session();
            $session->set([
                'isLoggedIn' => true,
                'userId' => $user['id'],
                'userEmail' => $email,
                'userName' => $user['name'] ?? '',
                'userRole' => $user['role'] ?? 'student',
            ]);
            return redirect()->to(base_url('dashboard'));
        }

        // Provide specific error message for deactivated accounts
        if ($user && $user['status'] === 'inactive') {
            return redirect()->back()->with('login_error', 'Your account has been deactivated. Please contact an administrator.');
        }

        return redirect()->back()->with('login_error', 'Invalid credentials');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        return redirect()->to(base_url('login'));
    }

    public function register()
    {
        $session = session();
        if ($session->get('isLoggedIn')) {
            return redirect()->to(base_url('dashboard'));
        }

        return view('auth/register');
    }

    public function store()
    {
        $name = trim((string) $this->request->getPost('name'));
        $email = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');
        $passwordConfirm = (string) $this->request->getPost('password_confirm');

        if ($name === '' || $email === '' || $password === '' || $passwordConfirm === '') {
            return redirect()->back()->withInput()->with('register_error', 'All fields are required.');
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('register_error', 'Invalid email address.');
        }

        if ($password !== $passwordConfirm) {
            return redirect()->back()->withInput()->with('register_error', 'Passwords do not match.');
        }

        $userModel = new \App\Models\UserModel();

        // Check for existing email
        if ($userModel->where('email', $email)->first()) {
            return redirect()->back()->withInput()->with('register_error', 'Email is already registered.');
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $userId = $userModel->insert([
            'name' => $name,
            'email' => $email,
            'role' => 'student',
            'password' => $passwordHash,
        ], true);

        if (! $userId) {
            return redirect()->back()->withInput()->with('register_error', 'Registration failed.');
        }

        // Redirect to login with success message
        return redirect()
            ->to(base_url('login'))
            ->with('register_success', 'Account created successfully. Please log in.');
    }

    public function dashboard()
    {
        $session = session();
        if (! $session->get('isLoggedIn')) {
            return redirect()->to(base_url('login'));
        }

        try {
            $userModel = new \App\Models\UserModel();
            $user = $userModel->where('email', $session->get('userEmail'))->first();

            // Debug: Check if user exists and has correct ID
            if (!$user) {
                log_message('error', 'Dashboard: User not found for email: ' . $session->get('userEmail'));
                $session->destroy();
                return redirect()->to(base_url('login'))->with('error', 'User session invalid. Please login again.');
            }

            // Check if user account is still active
            if ($user['status'] !== 'active') {
                $session->destroy();
                return redirect()->to(base_url('login'))->with('login_error', 'Your account has been deactivated. Please contact an administrator.');
            }

            // Get role from database - prioritize database over session
            $userRole = !empty($user['role']) ? $user['role'] : ($session->get('userRole') ?? 'student');
            
            // Special handling: if user email contains 'teacher' or name is 'Teacher User', force teacher role
            $userEmail = strtolower($user['email'] ?? '');
            $userName = $user['name'] ?? '';
            
            if (strpos($userEmail, 'teacher') !== false || $userName === 'Teacher User') {
                if ($userRole !== 'teacher') {
                    $userRole = 'teacher';
                    // Update database
                    $userModel->update($user['id'], ['role' => 'teacher']);
                }
            }
            
            // If role is empty string, check if it's a teacher user first
            if (empty($userRole) || trim($userRole) === '') {
                if (strpos($userEmail, 'teacher') !== false || $userName === 'Teacher User') {
                    $userRole = 'teacher';
                    $userModel->update($user['id'], ['role' => 'teacher']);
                } else {
                    $userRole = 'student';
                    $userModel->update($user['id'], ['role' => 'student']);
                }
            }
            
            // Update session with current role from database
            $session->set('userRole', $userRole);
            
            $data = [
                'userName'  => $user['name'] ?? $session->get('userEmail'),
                'userEmail' => $user['email'] ?? $session->get('userEmail'),
                'userRole'  => $userRole,
                'stats'     => [],
                'enrollments' => [],
                'availableCourses' => [],
            ];

            // Load role-specific data
            $role = strtolower($data['userRole']);

            if ($role === 'student') {
                // Load enrollment data for students
                $enrollmentModel = new \App\Models\EnrollmentModel();
                $courseModel = new \App\Models\CourseModel();
                $materialModel = new \App\Models\MaterialModel();

                // Debug: Log user ID and check enrollments
                log_message('debug', 'Dashboard: Loading enrollments for user ID: ' . $user['id']);

                // Get approved and pending enrollments separately
                $data['enrollments'] = $enrollmentModel->getApprovedEnrollments($user['id']);
                $data['pendingEnrollments'] = $enrollmentModel->getPendingEnrollments($user['id']);
                $data['availableCourses'] = $courseModel->getAvailableCoursesForUser($user['id']);

                // Debug: Log enrollment count
                log_message('debug', 'Dashboard: Found ' . count($data['enrollments']) . ' enrollments for user ID: ' . $user['id']);

                // Load materials for enrolled courses
                $data['courseMaterials'] = [];
                foreach ($data['enrollments'] as $enrollment) {
                    $materials = $materialModel->getMaterialsByCourse($enrollment['course_id']);
                    if (!empty($materials)) {
                        $data['courseMaterials'][$enrollment['course_id']] = [
                            'course' => $enrollment,
                            'materials' => $materials
                        ];
                    }
                }

                // Student stats
                $data['stats']['student'] = [
                    'enrolled' => count($data['enrollments']),
                    'average' => 'N/A'
                ];

            } elseif ($role === 'teacher') {
                // Load teacher data
                $courseModel = new \App\Models\CourseModel();
                $materialModel = new \App\Models\MaterialModel();
                $enrollmentModel = new \App\Models\EnrollmentModel();
                $assignmentModel = new \App\Models\AssignmentModel();

                $teacherCourses = $courseModel->where('instructor_id', $user['id'])->findAll();

                // Load pending enrollments for teacher's courses
                $data['pendingEnrollments'] = $enrollmentModel->getPendingEnrollmentsForTeacher($user['id']);

                // Count total assignments
                $totalAssignments = 0;
                foreach ($teacherCourses as $course) {
                    $assignments = $assignmentModel->getAssignmentsByCourse($course['id']);
                    $totalAssignments += count($assignments);
                }

                // Load materials and assignments for teacher's courses
                $data['teacherCourses'] = [];
                foreach ($teacherCourses as $course) {
                    $materials = $materialModel->getMaterialsByCourse($course['id']);
                    $assignments = $assignmentModel->getAssignmentsByCourse($course['id']);
                    $data['teacherCourses'][] = [
                        'course' => $course,
                        'materials' => $materials,
                        'assignments' => $assignments
                    ];
                }

                $data['stats']['teacher'] = [
                    'classes' => count($teacherCourses),
                    'assignments' => $totalAssignments,
                    'submissions' => 0,
                    'pendingEnrollments' => count($data['pendingEnrollments'])
                ];

            } elseif ($role === 'admin') {
                // Load admin data
                $userModel = new \App\Models\UserModel();
                $courseModel = new \App\Models\CourseModel();
                $materialModel = new \App\Models\MaterialModel();

                $totalUsers = $userModel->countAllResults();
                $totalCourses = $courseModel->countAllResults();

                // Load all courses with materials for admin
                $allCourses = $courseModel->select('courses.*, users.name as instructor_name')
                    ->join('users', 'users.id = courses.instructor_id')
                    ->findAll();

                $data['adminCourses'] = [];
                foreach ($allCourses as $course) {
                    $materials = $materialModel->getMaterialsByCourse($course['id']);
                    $data['adminCourses'][] = [
                        'course' => $course,
                        'materials' => $materials
                    ];
                }

                $data['stats']['admin'] = [
                    'users' => $totalUsers,
                    'courses' => $totalCourses,
                    'reports' => 0
                ];
            }

            // Load notifications count for logged-in users
            $data = $this->prepareViewData($data);

            return view('auth/dashboard', $data);
        } catch (Exception $e) {
            // If there's any error, show a simple error message
            return "Dashboard error: " . $e->getMessage();
        }
    }
}