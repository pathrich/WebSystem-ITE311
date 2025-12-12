<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'message', 'is_read', 'created_at'];

    protected $tableReady;

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Get the count of unread notifications for a user
     */
    public function getUnreadCount($userId)
    {
        if (!$this->isReady()) {
            return 0;
        }

        try {
            return $this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get the latest 5 notifications for a user
     */
    public function getNotificationsForUser($userId)
    {
        if (!$this->isReady()) {
            return [];
        }

        try {
            return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit(5)->findAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        if (!$this->isReady()) {
            return false;
        }

        try {
            return $this->update($notificationId, ['is_read' => 1]);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get all notifications for a user
     */
    public function getAllNotificationsForUser($userId)
    {
        if (!$this->isReady()) {
            return [];
        }

        try {
            return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public function isReady(): bool
    {
        if ($this->tableReady !== null) {
            return (bool) $this->tableReady;
        }

        try {
            $db = \Config\Database::connect();
            $this->tableReady = $db->tableExists($this->table);
        } catch (\Throwable $e) {
            $this->tableReady = false;
        }

        return (bool) $this->tableReady;
    }
}
