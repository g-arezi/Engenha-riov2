<?php
/**
 * Teste simples de autenticação
 */

// Iniciar a sessão
session_start();

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;

header('Content-Type: application/json');

echo json_encode([
    'session_id' => session_id(),
    'session_data' => $_SESSION,
    'auth_check' => Auth::check(),
    'auth_user' => Auth::user(),
    'auth_role' => Auth::role(),
    'has_permission' => Auth::hasPermission('projects.manage_workflow'),
    'cookies' => $_COOKIE,
    'headers' => getallheaders()
]);
