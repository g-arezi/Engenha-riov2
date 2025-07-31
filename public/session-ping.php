<?php
// Iniciar sessão
session_start();

// Definir cabeçalhos para prevenir cache
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: application/json; charset=utf-8');

// Log para debug
error_log('Session ping recebido - Session ID: ' . session_id());
error_log('Session data: ' . json_encode($_SESSION));

// Verificar se o usuário está autenticado
$authenticated = isset($_SESSION['user_id']);

// Renovar a sessão
if ($authenticated) {
    // Regenerar ID da sessão periodicamente para segurança (a cada 5 pings)
    if (isset($_SESSION['ping_count'])) {
        $_SESSION['ping_count']++;
        if ($_SESSION['ping_count'] % 5 === 0) {
            $oldSessionId = session_id();
            session_regenerate_id(true);
            $newSessionId = session_id();
            error_log("Sessão regenerada: $oldSessionId -> $newSessionId");
        }
    } else {
        $_SESSION['ping_count'] = 1;
    }
    
    // Atualizar timestamp de última atividade
    $_SESSION['last_activity'] = time();
}

// Responder com status da autenticação
echo json_encode([
    'success' => true,
    'authenticated' => $authenticated,
    'session_id' => session_id(),
    'timestamp' => date('Y-m-d H:i:s')
]);
