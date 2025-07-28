<?php
/**
 * Script de Login Automático - Para testes
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'autoload.php';

use App\Core\Auth;

// Fazer login como admin
$email = 'admin@engenhario.com';
$password = 'password';

$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (Auth::login($email, $password)) {
    if (!$isAjax) {
        echo "✅ Login realizado com sucesso!\n";
        echo "Usuário: " . Auth::user()['name'] . "\n";
        echo "Tipo: " . (Auth::user()['role'] ?? Auth::user()['type'] ?? 'N/A') . "\n";
        echo "Session ID: " . session_id() . "\n";
        
        // Verificar permissões
        $permissions = ['documents.upload', 'documents.view'];
        foreach ($permissions as $permission) {
            $has = Auth::hasPermission($permission);
            echo ($has ? "✅" : "❌") . " {$permission}: " . ($has ? "SIM" : "NÃO") . "\n";
        }
    }
} else {
    if (!$isAjax) {
        echo "❌ Falha no login\n";
    }
}
?>
