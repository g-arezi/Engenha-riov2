<?php
/**
 * Script de Login Automático - DESATIVADO PARA TESTES
 * 
 * Este arquivo foi modificado para desabilitar o login automático.
 * Agora redireciona para a página de login padrão.
 */

// Redirecionar para a página de login
header('Location: /login');
exit;

// Código original comentado abaixo
// session_start();
// 
// // Auto login para teste
// require_once __DIR__ . '/../autoload.php';
// 
// use App\Core\Auth;
// 
// if (!Auth::check()) {
//     // Fazer login automático com o usuário admin
//     $_SESSION['user_id'] = 'admin';
//     $_SESSION['user_role'] = 'administrador';
//     
//     echo "<p>✅ Login automático realizado com sucesso!</p>";
//     echo "<p>Usuário: admin</p>";
//     echo "<p>Role: administrador</p>";
// } else {
//     echo "<p>✅ Usuário já está logado!</p>";
//     $user = Auth::user();
//     echo "<p>Usuário: " . $user['name'] . "</p>";
//     echo "<p>Role: " . $user['role'] . "</p>";
// }
// 
// echo "<p><a href='/documents/upload'>Ir para Upload</a></p>";
// echo "<p><a href='/documents'>Ir para Documentos</a></p>";
// 
// echo "<h3>Permissões do Usuário</h3>";
// $permissions = [
//     'documents.upload' => Auth::hasPermission('documents.upload'),
//     'documents.view' => Auth::hasPermission('documents.view'),
//     'documents.download' => Auth::hasPermission('documents.download'),
//     'documents.approve' => Auth::hasPermission('documents.approve')
// ];
// 
// foreach ($permissions as $perm => $has) {
//     echo "<p>{$perm}: " . ($has ? '✅ Sim' : '❌ Não') . "</p>";
// }
?>
