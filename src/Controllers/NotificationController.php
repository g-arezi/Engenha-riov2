<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Services\NotificationService;

class NotificationController
{
    private Database $db;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->db = new Database();
        $this->notificationService = new NotificationService();
    }

    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $title = 'Notificações - Engenha Rio';
        $showSidebar = true;
        $showNavbar = true;
        $user = Auth::user();
        
        // Obter notificações do usuário
        $notifications = $this->notificationService->getUserNotifications(Auth::id());
        
        require_once __DIR__ . '/../../views/notifications/index.php';
    }

    public function markAsRead(): void
    {
        if (!Auth::check()) {
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $notificationId = $input['notification_id'] ?? null;

        if (!$notificationId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'ID da notificação não fornecido']);
            return;
        }

        $notification = $this->db->find('notifications', $notificationId);
        if (!$notification || $notification['user_id'] !== Auth::id()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Notificação não encontrada']);
            return;
        }

        $this->db->update('notifications', $notificationId, [
            'read' => true,
            'read_at' => date('Y-m-d H:i:s')
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function markAllAsRead(): void
    {
        if (!Auth::check()) {
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        $notifications = $this->db->findAll('notifications', ['user_id' => Auth::id(), 'read' => false]);
        
        foreach ($notifications as $notification) {
            $this->db->update('notifications', $notification['id'], [
                'read' => true,
                'read_at' => date('Y-m-d H:i:s')
            ]);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function getUnreadCount(): void
    {
        if (!Auth::check()) {
            header('HTTP/1.0 401 Unauthorized');
            exit;
        }

        $count = count($this->db->findAll('notifications', ['user_id' => Auth::id(), 'read' => false]));

        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
    }
}
