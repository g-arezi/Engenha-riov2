<?php

namespace App\Services;

use App\Core\Database;

class NotificationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function createDocumentNotification(string $type, array $data): void
    {
        $notification = [
            'type' => $type,
            'title' => $this->getNotificationTitle($type),
            'message' => $this->getNotificationMessage($type, $data),
            'user_id' => $data['user_id'] ?? null,
            'project_id' => $data['project_id'] ?? null,
            'document_id' => $data['document_id'] ?? null,
            'is_read' => false,
            'priority' => $this->getNotificationPriority($type)
        ];

        $this->db->insert('notifications', $notification);
    }

    public function getNotificationTitle(string $type): string
    {
        $titles = [
            'document_uploaded' => 'Documento Enviado',
            'document_approved' => 'Documento Aprovado',
            'document_rejected' => 'Documento Rejeitado',
            'stage_completed' => 'Etapa Concluída',
            'project_status_changed' => 'Status do Projeto Alterado'
        ];

        return $titles[$type] ?? 'Notificação';
    }

    public function getNotificationMessage(string $type, array $data): string
    {
        switch ($type) {
            case 'document_uploaded':
                return "Novo documento '{$data['document_name']}' foi enviado para o projeto '{$data['project_name']}'";
            case 'document_approved':
                return "Documento '{$data['document_name']}' foi aprovado";
            case 'document_rejected':
                return "Documento '{$data['document_name']}' foi rejeitado. Motivo: {$data['reason']}";
            case 'stage_completed':
                return "Etapa '{$data['stage_name']}' foi concluída no projeto '{$data['project_name']}'";
            case 'project_status_changed':
                return "Status do projeto '{$data['project_name']}' foi alterado para '{$data['new_status']}'";
            default:
                return 'Nova notificação disponível';
        }
    }

    public function getNotificationPriority(string $type): string
    {
        $priorities = [
            'document_uploaded' => 'normal',
            'document_approved' => 'normal',
            'document_rejected' => 'high',
            'stage_completed' => 'high',
            'project_status_changed' => 'normal'
        ];

        return $priorities[$type] ?? 'normal';
    }

    public function getUserNotifications(string $userId, bool $unreadOnly = false): array
    {
        $criteria = ['user_id' => $userId];
        if ($unreadOnly) {
            $criteria['is_read'] = false;
        }

        $notifications = $this->db->findAll('notifications', $criteria);
        
        // Ordenar por data de criação (mais recentes primeiro)
        usort($notifications, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });

        return $notifications;
    }

    public function markAsRead(string $notificationId): bool
    {
        return $this->db->update('notifications', $notificationId, ['is_read' => true]);
    }

    public function markAllAsRead(string $userId): void
    {
        $notifications = $this->db->findAll('notifications', ['user_id' => $userId, 'is_read' => false]);
        
        foreach ($notifications as $notification) {
            $this->markAsRead($notification['id']);
        }
    }

    public function addNotification(string $userId, string $title, string $message, string $type = 'info', string $priority = 'medium'): void
    {
        $notification = [
            'id' => 'notif_' . uniqid(),
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'priority' => $priority,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('notifications', $notification);
    }
}
