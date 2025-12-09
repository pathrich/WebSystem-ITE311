<?php

namespace App\Models;

use CodeIgniter\Model;

class UserActivityLogModel extends Model
{
    protected $table = 'user_activity_logs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'action', 'description', 'ip_address', 'user_agent', 'created_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    /**
     * Get activity logs for a user
     */
    public function getLogsForUser($userId, $limit = 10, $offset = 0)
    {
        return $this->where('user_id', $userId)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit, $offset)
                   ->findAll();
    }

    /**
     * Get recent activity logs across all users
     */
    public function getRecentLogs($limit = 20)
    {
        return $this->select('user_activity_logs.*, users.name as user_name, users.username')
                   ->join('users', 'users.id = user_activity_logs.user_id')
                   ->orderBy('user_activity_logs.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get activity logs by action type
     */
    public function getLogsByAction($action, $limit = 10)
    {
        return $this->where('action', $action)
                   ->orderBy('created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Count logs for a user
     */
    public function countLogsForUser($userId)
    {
        return $this->where('user_id', $userId)->countAllResults();
    }
}
