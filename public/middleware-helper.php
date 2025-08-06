<?php
// middleware-helper.php - Funções para garantir que o middleware funcione corretamente com chamadas AJAX

/**
 * Adiciona cabeçalho de autenticação para solicitações AJAX
 * Este arquivo deve ser carregado antes do envio de solicitações AJAX
 */

// Iniciar a sessão apenas se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obter o ID do usuário atual da sessão se existir
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    // Definir um meta-tag que pode ser usado por JavaScript para autenticar solicitações AJAX
    echo '<meta name="auth-token" content="' . $userId . '" data-role="' . ($_SESSION['user_role'] ?? 'cliente') . '">';
    
    // Adicionar script para garantir que requisições AJAX incluem cabeçalhos de autenticação
    echo '<script>
    // Configura cabeçalhos de autenticação para todas as chamadas fetch
    const originalFetch = window.fetch;
    
    window.fetch = function(url, options = {}) {
        // Obter token de autenticação do meta tag
        const authTokenMeta = document.querySelector("meta[name=\'auth-token\']");
        const authToken = authTokenMeta ? authTokenMeta.getAttribute("content") : null;
        const authRole = authTokenMeta ? authTokenMeta.getAttribute("data-role") : null;
        
        // Se não há opções, criar um objeto vazio
        if (!options) options = {};
        
        // Se não há cabeçalhos, criar um objeto vazio
        if (!options.headers) options.headers = {};
        
        // Adicionar cabeçalhos de autenticação personalizados
        if (authToken) {
            options.headers["X-Auth-User-ID"] = authToken;
            
            if (authRole) {
                options.headers["X-Auth-User-Role"] = authRole;
            }
        }
        
        // Garantir que credenciais são enviadas (cookies)
        if (!options.credentials) {
            options.credentials = "same-origin";
        }
        
        // Chamar a função fetch original com as opções atualizadas
        return originalFetch(url, options);
    };
    </script>';
}
