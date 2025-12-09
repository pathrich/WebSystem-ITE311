<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserActivityLogModel;

class UserController extends BaseController
{
    protected $userModel;
    protected $activityLogModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activityLogModel = new UserActivityLogModel();
    }

    /**
     * Check if user is admin
     */
    private function checkAdminAccess()
    {
        $session = session();
        if (!$session->get('isLoggedIn') || $session->get('userRole') !== 'admin') {
            return redirect()->to(base_url('dashboard'))->with('error', 'Access denied. Admin privileges required.');
        }
        return null;
    }

    /**
     * Display user list with pagination
     */
    public function index()
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $perPage = 10;
        $currentPage = $this->request->getGet('page') ?? 1;

        // Use proper pagination - findAll() doesn't take pagination parameters in CI4
        $users = $this->userModel->orderBy('id', 'DESC')->findAll($perPage, ($currentPage - 1) * $perPage);
        $total = $this->userModel->countAll();

        $pager = \Config\Services::pager();
        $pager->setPath('users');
        $pager->makeLinks($currentPage, $perPage, $total);

        $data = [
            'users' => $users,
            'pager' => $pager,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
        ];

        return view('users/user_list', $data);
    }

    /**
     * Show add user form
     */
    public function create()
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        return view('users/user_add');
    }

    /**
     * Store new user
     */
    public function store()
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'password_confirm' => 'required|matches[password]',
            'role' => 'required|in_list[admin,teacher,student]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userData = [
            'name' => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        $userId = $this->userModel->insert($userData);

        if ($userId) {
            // Log activity
            $session = session();
            $this->activityLogModel->insert([
                'user_id' => $session->get('userId') ?? 1,
                'action' => 'create',
                'description' => "Created user: {$userData['name']} ({$userData['username']})",
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to(base_url('users'))->with('success', 'User created successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create user.');
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $data = [
            'user' => $user,
        ];

        return view('users/user_edit', $data);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[100]',
            'username' => "required|min_length[3]|max_length[50]|is_unique[users.username,id,{$id}]",
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role' => 'required|in_list[admin,teacher,student]',
            'status' => 'required|in_list[active,inactive]',
        ];

        // Add password validation only if password is provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'required|min_length[6]';
            $rules['password_confirm'] = 'required|matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $updateData = [
            'name' => $this->request->getPost('name'),
            'username' => $this->request->getPost('username'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'status' => $this->request->getPost('status'),
        ];

        // Only update password if provided
        if ($this->request->getPost('password')) {
            $updateData['password'] = $this->request->getPost('password');
        }

        $result = $this->userModel->update($id, $updateData);
        if ($result !== false) {
            // Log activity
            $session = session();
            $changes = [];
            foreach ($updateData as $field => $value) {
                if ($field !== 'password' && $user[$field] !== $value) {
                    $changes[] = ucfirst($field) . ": {$user[$field]} → {$value}";
                } elseif ($field === 'password') {
                    $changes[] = "Password updated";
                }
            }

            if (!empty($changes)) {
                $this->activityLogModel->insert([
                    'user_id' => $session->get('userId') ?? 1,
                    'action' => 'update',
                    'description' => "Updated user {$user['name']} ({$user['username']}): " . implode(', ', $changes),
                    'ip_address' => $this->request->getIPAddress(),
                    'user_agent' => $this->request->getUserAgent(),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            return redirect()->to(base_url('users'))->with('success', 'User updated successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update user.');
    }

    /**
     * Delete user
     */
    public function delete($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        if ($this->userModel->delete($id)) {
            // Log activity
            $session = session();
            $this->activityLogModel->insert([
                'user_id' => $session->get('userId') ?? 1,
                'action' => 'delete',
                'description' => "Deleted user: {$user['name']} ({$user['username']})",
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to(base_url('users'))->with('success', 'User deleted successfully.');
        }

        return redirect()->to(base_url('users'))->with('error', 'Failed to delete user.');
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $newStatus = $user['status'] === 'active' ? 'inactive' : 'active';

        if ($this->userModel->updateStatus($id, $newStatus)) {
            // Log activity
            $session = session();
            $this->activityLogModel->insert([
                'user_id' => $session->get('userId') ?? 1,
                'action' => 'status_change',
                'description' => "Changed user status: {$user['name']} ({$user['username']}) from {$user['status']} to {$newStatus}",
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $message = $newStatus === 'active' ? 'User activated successfully.' : 'User deactivated successfully.';
            return redirect()->to(base_url('users'))->with('success', $message);
        }

        return redirect()->to(base_url('users'))->with('error', 'Failed to update user status.');
    }

    /**
     * Update user role
     */
    public function updateRole($id)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        $user = $this->userModel->find($id);
        if (!$user) {
            return redirect()->to(base_url('users'))->with('error', 'User not found.');
        }

        $newRole = $this->request->getPost('role');
        if (!$newRole || !in_array($newRole, ['admin', 'teacher', 'student'])) {
            return redirect()->to(base_url('users'))->with('error', 'Invalid role selected.');
        }

        if ($this->userModel->updateRole($id, $newRole)) {
            // Log activity
            $session = session();
            $this->activityLogModel->insert([
                'user_id' => $session->get('userId') ?? 1,
                'action' => 'role_change',
                'description' => "Changed user role: {$user['name']} ({$user['username']}) from {$user['role']} to {$newRole}",
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to(base_url('users'))->with('success', 'User role updated successfully.');
        }

        return redirect()->to(base_url('users'))->with('error', 'Failed to update user role.');
    }

    /**
     * Show user activity logs
     */
    public function activityLogs($userId = null)
    {
        $redirect = $this->checkAdminAccess();
        if ($redirect) return $redirect;

        if ($userId) {
            $logs = $this->activityLogModel->getLogsForUser($userId, 50);
            $user = $this->userModel->find($userId);
            $data = [
                'logs' => $logs,
                'user' => $user,
                'title' => "Activity Logs for {$user['name']}",
            ];
        } else {
            $logs = $this->activityLogModel->getRecentLogs(50);
            $data = [
                'logs' => $logs,
                'title' => 'Recent Activity Logs',
            ];
        }

        return view('users/activity_logs', $data);
    }
}
