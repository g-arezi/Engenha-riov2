<?php
/**
 * Script de Login Automático - Para testes
 */

session_start();
require_once 'autoload.php';

use App\Core\Auth;

// Fazer login como admin
$email = 'admin@engenhario.com';
$password = 'password';

if (Auth::login($email, $password)) {
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
} else {
    echo "❌ Falha no login\n";
}
?>
