<?php
require_once '../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Configurar headers
header('Content-Type: application/json; charset=utf-8');

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h1>Teste de Atualização de Estágio</h1>";

try {
    // Verificar autenticação
    if (!Auth::check()) {
        echo "<p style='color: red;'>❌ Usuário não autenticado</p>";
        echo "<a href='/login'>Fazer Login</a>";
        exit;
    }

    $user = Auth::user();
    echo "<p style='color: green;'>✅ Usuário autenticado: {$user['name']} (Role: {$user['role']})</p>";

    // Verificar permissão
    if (!Auth::hasPermission('projects.manage_workflow')) {
        echo "<p style='color: red;'>❌ Usuário sem permissão para gerenciar workflow</p>";
        exit;
    }

    echo "<p style='color: green;'>✅ Usuário tem permissão para gerenciar workflow</p>";

    // Conectar ao banco
    $db = new Database();
    echo "<p style='color: green;'>✅ Conectado ao banco de dados</p>";

    // Buscar projeto de teste
    $projectId = 'proj_1753892536_9899';
    $project = $db->find('projects', $projectId);
    
    if (!$project) {
        echo "<p style='color: red;'>❌ Projeto {$projectId} não encontrado</p>";
        exit;
    }

    echo "<p style='color: green;'>✅ Projeto encontrado: {$project['name']}</p>";
    echo "<p>Estágio atual: {$project['workflow_stage']}</p>";

    // Teste de atualização
    echo "<h2>Testando atualização de estágio...</h2>";
    
    $newStage = ($project['workflow_stage'] == 1) ? 2 : 1; // Alternar entre 1 e 2
    
    echo "<p>Tentando alterar de {$project['workflow_stage']} para {$newStage}...</p>";
    
    $success = $db->update('projects', $projectId, [
        'workflow_stage' => $newStage,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($success) {
        echo "<p style='color: green;'>✅ Estágio atualizado com sucesso!</p>";
        
        // Verificar se realmente foi atualizado
        $updatedProject = $db->find('projects', $projectId);
        echo "<p>Novo estágio: {$updatedProject['workflow_stage']}</p>";
    } else {
        echo "<p style='color: red;'>❌ Erro ao atualizar estágio</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erro: {$e->getMessage()}</p>";
}

echo "<br><a href='/documents/project/{$projectId}'>Voltar ao projeto</a>";
?>
