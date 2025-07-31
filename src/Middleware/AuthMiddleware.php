<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle(): bool
    {
        // Iniciar sessão se ainda não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            error_log('AuthMiddleware: Iniciando sessão - Session ID: ' . session_id());
        }

        // Verificação de debug
        error_log('AuthMiddleware: Verificando autenticação - Session data: ' . json_encode($_SESSION));
        
        // Verificar autenticação
        if (!Auth::check()) {
            error_log('AuthMiddleware: Usuário não autenticado - redirecionando');
            
            // Verificar se é uma requisição AJAX
            if ($this->isAjaxRequest()) {
                error_log('AuthMiddleware: Requisição AJAX - retornando código 401');
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }
            
            header('Location: /login');
            exit;
        }
        
        error_log('AuthMiddleware: Usuário autenticado com sucesso');
        return true;
    }
    
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ||
               (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
               (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
    }
}
