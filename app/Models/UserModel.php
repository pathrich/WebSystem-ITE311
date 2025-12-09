<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';

    protected $returnType = 'array';

    protected $allowedFields = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'status',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = false; // timestamps handled by DB defaults in migration

    // Validation rules
    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'username' => 'required|min_length[3]|max_length[50]|is_unique[users.username,id,{id}]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[6]',
        'role' => 'required|in_list[admin,teacher,student]',
        'status' => 'required|in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'name' => [
            'required' => 'Name is required',
            'min_length' => 'Name must be at least 2 characters long',
            'max_length' => 'Name cannot exceed 100 characters',
        ],
        'username' => [
            'required' => 'Username is required',
            'min_length' => 'Username must be at least 3 characters long',
            'max_length' => 'Username cannot exceed 50 characters',
            'is_unique' => 'This username is already taken',
        ],
        'email' => [
            'required' => 'Email is required',
            'valid_email' => 'Please enter a valid email address',
            'is_unique' => 'This email is already registered',
        ],
        'password' => [
            'required' => 'Password is required',
            'min_length' => 'Password must be at least 6 characters long',
        ],
        'role' => [
            'required' => 'Role is required',
            'in_list' => 'Invalid role selected',
        ],
        'status' => [
            'required' => 'Status is required',
            'in_list' => 'Invalid status selected',
        ],
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        }
        return $data;
    }

    /**
     * Get users with pagination
     */
    public function getUsersPaginated($perPage = 10, $page = 1)
    {
        return $this->paginate($perPage, 'default', $page);
    }

    /**
     * Get user by username
     */
    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get active users
     */
    public function getActiveUsers()
    {
        return $this->where('status', 'active')->findAll();
    }

    /**
     * Update user status
     */
    public function updateStatus($userId, $status)
    {
        return $this->update($userId, ['status' => $status]);
    }

    /**
     * Update user role
     */
    public function updateRole($userId, $role)
    {
        return $this->update($userId, ['role' => $role]);
    }

    /**
     * Check if email exists (excluding current user for updates)
     */
    public function emailExists($email, $excludeId = null)
    {
        $query = $this->where('email', $email);
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        return $query->first() !== null;
    }

    /**
     * Check if username exists (excluding current user for updates)
     */
    public function usernameExists($username, $excludeId = null)
    {
        $query = $this->where('username', $username);
        if ($excludeId) {
            $query->where('id !=', $excludeId);
        }
        return $query->first() !== null;
    }

    /**
     * Log user activity
     */
    public function logActivity($userId, $action, $description, $ipAddress = null, $userAgent = null)
    {
        $activityLogModel = new \App\Models\UserActivityLogModel();
        return $activityLogModel->insert([
            'user_id' => $userId,
            'action' => $action,
            'description' => $description,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Get user activity logs
     */
    public function getActivityLogs($userId, $limit = 10)
    {
        $activityLogModel = new \App\Models\UserActivityLogModel();
        return $activityLogModel->where('user_id', $userId)
                               ->orderBy('created_at', 'DESC')
                               ->limit($limit)
                               ->findAll();
    }
}


