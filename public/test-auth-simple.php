<?php
/**
 * Teste simples de autenticação e avançar workflow
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

header('Content-Type: application/json');

// Inicializar
$auth = new Auth();
$db = new Database();

echo json_encode([
    'session_status' => session_status(),
    'session_id' => session_id(),
    'session_data' => $_SESSION ?? null,
    'is_authenticated' => $auth->check(),
    'user_data' => $auth->check() ? $auth->user() : null,
    'has_workflow_permission' => $auth->hasPermission('projects.manage_workflow'),
    'server_time' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
?>
