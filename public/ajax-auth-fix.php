<?php
// ajax-auth-fix.php
// Este arquivo será incluído no início de qualquer endpoint AJAX que precise de autenticação

// Garantir que a sessão está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Helper function para logs
function ajaxLog($message) {
    error_log("[AJAX-AUTH] " . $message);
}

// Registrar informações iniciais para debug
ajaxLog("Session ID: " . session_id());
ajaxLog("Session Path: " . session_save_path());
ajaxLog("Cookie Params: " . json_encode(session_get_cookie_params()));

// Verificar se os cookies estão funcionando
if (empty($_COOKIE)) {
    ajaxLog("WARNING: No cookies received");
} else {
    ajaxLog("Cookies received: " . implode(', ', array_keys($_COOKIE)));
    if (isset($_COOKIE[session_name()])) {
        ajaxLog("Session cookie found with value: " . $_COOKIE[session_name()]);
    } else {
        ajaxLog("WARNING: Session cookie not found");
    }
}

// Debug da sessão
ajaxLog("SESSION DATA: " . json_encode($_SESSION, JSON_PRETTY_PRINT));

// Fix para o problema de autenticação em chamadas AJAX
// Se não houver user_id na sessão, mas houver no cookie, restaura a sessão
if (!isset($_SESSION['user_id']) && isset($_COOKIE['user_id'])) {
    ajaxLog("Restaurando user_id da sessão a partir do cookie: " . $_COOKIE['user_id']);
    $_SESSION['user_id'] = $_COOKIE['user_id'];
    
    // Se houver cookie para role, também restaura
    if (isset($_COOKIE['user_role'])) {
        $_SESSION['user_role'] = $_COOKIE['user_role'];
        ajaxLog("Restaurando user_role da sessão a partir do cookie: " . $_COOKIE['user_role']);
    } else {
        // Define um papel padrão para evitar problemas de permissão
        $_SESSION['user_role'] = 'cliente';
        ajaxLog("Definindo user_role padrão: cliente");
    }
    
    ajaxLog("Sessão restaurada do cookie");
}

// Verificação alternativa - se ainda não tiver user_id mas estamos em desenvolvimento
if (!isset($_SESSION['user_id']) && (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || $_SERVER['SERVER_NAME'] === '127.0.0.1')) {
    // Em ambiente de desenvolvimento, permitir acesso para teste
    // Isso só deve ser usado durante o desenvolvimento!
    ajaxLog("DEBUG MODE: Setting default user_id for local development");
    $_SESSION['user_id'] = 'dev_user';
    $_SESSION['user_role'] = 'administrador';
}

// Registrar sessão após possíveis modificações
ajaxLog("FINAL SESSION DATA: " . json_encode($_SESSION, JSON_PRETTY_PRINT));
?>
