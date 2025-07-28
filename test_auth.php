<?php
/**
 * Teste de Autenticação - Engenha Rio
 */

require_once 'autoload.php';

use App\Core\Auth;

session_start();

echo "=== TESTE DE AUTENTICAÇÃO ===\n\n";

// Verificar se há sessão ativa
echo "Sessão ID: " . session_id() . "\n";
echo "Sessões ativas: \n";
var_dump($_SESSION);

echo "\n";

// Testar Auth
$auth = new Auth();

if (Auth::check()) {
    $user = Auth::user();
    echo "✅ Usuário autenticado:\n";
    echo "- ID: " . $user['id'] . "\n";
    echo "- Nome: " . $user['name'] . "\n";
    echo "- Email: " . $user['email'] . "\n";
    echo "- Tipo: " . $user['type'] . "\n";
    
    // Testar permissões
    $permissions = ['projects.view', 'projects.edit', 'projects.manage_workflow', 'admin.users'];
    foreach ($permissions as $permission) {
        $hasPermission = Auth::hasPermission($permission);
        echo ($hasPermission ? "✅" : "❌") . " Permissão {$permission}: " . ($hasPermission ? "SIM" : "NÃO") . "\n";
    }
    
} else {
    echo "❌ Usuário não autenticado\n";
    echo "Redirecionando para login...\n";
}

echo "\n=== FIM DO TESTE ===\n";
