<?php
// Se é um arquivo estático que existe, serve diretamente
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Se o caminho é para o debug de sessão, incluir esse arquivo
if ($path === '/session_debug.php') {
    include __DIR__ . '/../session_debug.php';
    exit;
}

// Caso contrário, continuar com o roteamento normal
if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false; // Serve o arquivo estático
}

// Include o roteador normal
require_once __DIR__ . '/router.php';
