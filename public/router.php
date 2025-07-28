<?php
// Router para servidor PHP embutido
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Se é um arquivo estático que existe, serve diretamente
if (file_exists(__DIR__ . $path) && is_file(__DIR__ . $path)) {
    return false; // Serve o arquivo estático
}

// Caso contrário, redireciona para index.php
require_once __DIR__ . '/index.php';
