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
        if ($user && password_verify($password, $user['password'])) {
            $session = session();
            $userRole = $user['role'] ?? 'student';
            $session->set([
                'isLoggedIn' => true,
                'userEmail' => $email,
                'userName' => $user['name'] ?? '',
                'userRole' => $userRole,
            ]);

            // Redirect based on role
            $role = strtolower($userRole);
            if ($role === 'teacher') {
                return redirect()->to(base_url('teacher/dashboard'));
            } elseif ($role === 'admin') {
                return redirect()->to(base_url('admin/dashboard'));
            }

            // default: student
            return redirect()->to(base_url('announcements'));
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

            $data = [
                'userName'  => $user['name'] ?? $session->get('userEmail'),
                'userEmail' => $user['email'] ?? $session->get('userEmail'),
                'userRole'  => $user['role'] ?? $session->get('userRole') ?? 'student',
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
                
                $data['enrollments'] = $enrollmentModel->getUserEnrollments($user['id']);
                $data['availableCourses'] = $courseModel->getAvailableCoursesForUser($user['id']);
                
                // Student stats
                $data['stats']['student'] = [
                    'enrolled' => count($data['enrollments']),
                    'average' => 'N/A'
                ];
                
            } elseif ($role === 'teacher') {
                // Load teacher data
                $courseModel = new \App\Models\CourseModel();
                $teacherCourses = $courseModel->where('instructor_id', $user['id'])->findAll();
                
                $data['stats']['teacher'] = [
                    'classes' => count($teacherCourses),
                    'assignments' => 0,
                    'submissions' => 0
                ];
                
            } elseif ($role === 'admin') {
                // Load admin data
                $userModel = new \App\Models\UserModel();
                $courseModel = new \App\Models\CourseModel();
                
                $totalUsers = $userModel->countAllResults();
                $totalCourses = $courseModel->countAllResults();
                
                $data['stats']['admin'] = [
                    'users' => $totalUsers,
                    'courses' => $totalCourses,
                    'reports' => 0
                ];
            }

            return view('auth/dashboard', $data);
        } catch (Exception $e) {
            // If there's any error, show a simple error message
            return "Dashboard error: " . $e->getMessage();
        }
    }
}