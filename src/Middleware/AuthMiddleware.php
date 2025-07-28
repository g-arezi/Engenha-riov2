<?php

namespace App\Middleware;

use App\Core\Auth;

class AuthMiddleware
{
    public function handle(): bool
    {
        if (!Auth::check()) {
            // Verificar se é uma requisição AJAX
            if ($this->isAjaxRequest()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Não autenticado']);
                exit;
            }
            
            header('Location: /login');
            exit;
        }
        
        return true;
    }
    
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest' ||
               (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
    }
}
