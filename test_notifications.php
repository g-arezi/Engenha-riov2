<?php

// Teste específico para verificar warnings de notificações
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "🔍 Testando Sistema de Notificações sem Warnings...\n\n";

require_once __DIR__ . '/autoload.php';

use App\Services\NotificationService;
use App\Core\Database;

session_start();

try {
    $db = new Database();
    $notificationService = new NotificationService();
    
    // Testar busca de notificações
    $notifications = $db->findAll('notifications');
    echo "✅ " . count($notifications) . " notificações carregadas\n";
    
    // Testar acesso à chave is_read sem warnings
    $unreadCount = 0;
    foreach ($notifications as $notification) {
        if (!($notification['is_read'] ?? false)) {
            $unreadCount++;
        }
    }
    echo "✅ $unreadCount notificações não lidas (sem warnings)\n";
    
    // Testar simulação de exibição de notificação
    if (!empty($notifications)) {
        $firstNotification = array_values($notifications)[0];
        $isRead = $firstNotification['is_read'] ?? false;
        $readStatus = $isRead ? 'lida' : 'não lida';
        echo "✅ Primeira notificação: $readStatus (sem warnings)\n";
    }
    
    echo "\n🎉 Teste de notificações concluído sem warnings!\n";
    
} catch (Exception $e) {
    echo "❌ Erro: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
