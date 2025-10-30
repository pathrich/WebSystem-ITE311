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
        return $this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
    }

    /**
     * Get the latest 5 notifications for a user
     */
    public function getNotificationsForUser($userId)
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->limit(5)->findAll();
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Get all notifications for a user
     */
    public function getAllNotificationsForUser($userId)
    {
        return $this->where('user_id', $userId)->orderBy('created_at', 'DESC')->findAll();
    }
}
