<?php
// ajax-debug.php - Arquivo para diagnosticar problemas com requisições AJAX

// Display all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir o corretor de autenticação AJAX
require_once __DIR__ . '/ajax-auth-fix.php';

// Obter todas as informações da requisição
$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];
$contentType = $_SERVER['CONTENT_TYPE'] ?? 'none';
$headers = getallheaders();

// Resposta JSON para requisições AJAX
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'session' => [
        'id' => session_id(),
        'user_id' => $_SESSION['user_id'] ?? null,
        'user_role' => $_SESSION['user_role'] ?? null
    ],
    'request' => [
        'method' => $method,
        'url' => $url,
        'content_type' => $contentType
    ],
    'headers' => $headers,
    'cookies' => $_COOKIE,
    'timestamp' => date('Y-m-d H:i:s')
]);
