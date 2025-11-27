<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificationModel;
use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    protected $notificationModel;
    protected $userModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
        $this->userModel = new UserModel();
    }

    /**
     * Get notifications for the logged-in user
     */
    public function get()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $user = $this->userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        $unreadCount = $this->notificationModel->getUnreadCount($user['id']);
        $notifications = $this->notificationModel->getNotificationsForUser($user['id']);

        return $this->response->setJSON([
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function mark_as_read($id = null)
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (!$id || !is_numeric($id)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid notification ID']);
        }

        $user = $this->userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return $this->response->setJSON(['success' => false, 'message' => 'User not found']);
        }

        // Check if notification belongs to user
        $notification = $this->notificationModel->find($id);
        if (!$notification || $notification['user_id'] != $user['id']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Notification not found']);
        }

        if ($this->notificationModel->markAsRead($id)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Notification marked as read']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to mark as read']);
        }
    }

    /**
     * Get all notifications for the logged-in user
     */
    public function get_all()
    {
        $session = session();
        if (!$session->get('isLoggedIn')) {
            return $this->response->setJSON(['error' => 'Unauthorized'], ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $user = $this->userModel->where('email', $session->get('userEmail'))->first();
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'], ResponseInterface::HTTP_NOT_FOUND);
        }

        $notifications = $this->notificationModel->getNotificationsForUser($user['id']);

        return $this->response->setJSON([
            'notifications' => $notifications
        ]);
    }
}
