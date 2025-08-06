<?php
session_start();
require_once '../autoload.php';

use App\Core\Auth;

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Debug de Autenticação</h2>";

echo "<h3>1. Informações da Sessão:</h3>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Session Status:</strong> " . session_status() . "</p>";
echo "<p><strong>$_SESSION:</strong></p>";
echo "<pre>" . json_encode($_SESSION, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>2. Cookies:</h3>";
echo "<p><strong>$_COOKIE:</strong></p>";
echo "<pre>" . json_encode($_COOKIE, JSON_PRETTY_PRINT) . "</pre>";

echo "<h3>3. Teste Auth::check():</h3>";
$isAuthenticated = Auth::check();
if ($isAuthenticated) {
    echo "<p style='color: green;'>✅ Auth::check() = true (usuário autenticado)</p>";
} else {
    echo "<p style='color: red;'>❌ Auth::check() = false (usuário NÃO autenticado)</p>";
}

echo "<h3>4. Teste Auth::user():</h3>";
$user = Auth::user();
if ($user) {
    echo "<p style='color: green;'>✅ Usuário encontrado:</p>";
    echo "<pre>" . json_encode($user, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<p style='color: red;'>❌ Nenhum usuário encontrado</p>";
}

echo "<h3>5. Teste de Role e Permissões:</h3>";
if ($user) {
    $role = Auth::role();
    echo "<p><strong>Role:</strong> $role</p>";
    
    echo "<p><strong>Métodos de verificação:</strong></p>";
    echo "<p>isAdmin(): " . (Auth::isAdmin() ? 'true' : 'false') . "</p>";
    echo "<p>isAnalista(): " . (Auth::isAnalista() ? 'true' : 'false') . "</p>";
    echo "<p>isCoordenador(): " . (Auth::isCoordenador() ? 'true' : 'false') . "</p>";
    echo "<p>isCliente(): " . (Auth::isCliente() ? 'true' : 'false') . "</p>";
    
    echo "<p><strong>Permissão projects.manage_workflow:</strong> " . (Auth::hasPermission('projects.manage_workflow') ? 'true' : 'false') . "</p>";
} else {
    echo "<p>❌ Não é possível verificar role e permissões - usuário não autenticado</p>";
}

echo "<h3>6. Verificar arquivo de usuários:</h3>";
try {
    $usersFile = '../data/users.json';
    if (file_exists($usersFile)) {
        $usersData = json_decode(file_get_contents($usersFile), true);
        echo "<p>✅ Arquivo users.json encontrado</p>";
        echo "<p><strong>Usuários no sistema:</strong></p>";
        foreach ($usersData as $userId => $userData) {
            echo "<p>ID: $userId | Email: {$userData['email']} | Role: {$userData['role']} | Status: {$userData['status']}</p>";
        }
    } else {
        echo "<p>❌ Arquivo users.json não encontrado</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>Erro ao ler usuários: " . $e->getMessage() . "</p>";
}

echo "<h3>7. Links para testar:</h3>";
echo "<p><a href='/login' target='_blank'>Ir para Login</a></p>";
echo "<p><a href='/dashboard' target='_blank'>Ir para Dashboard</a></p>";
echo "<p><a href='/documents/project/proj_1753892536_9899' target='_blank'>Ir para Projeto</a></p>";
?>
