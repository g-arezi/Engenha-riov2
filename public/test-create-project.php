<?php
/**
 * Script para testar criação de projeto via API
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Iniciar sessão
session_start();

// Simular usuário logado como admin
$_SESSION['user_id'] = 'admin';
$_SESSION['user_role'] = 'administrador';

// Dados do projeto de teste
$_POST = [
    'name' => 'Teste Workflow Corrigido - ' . date('H:i:s'),
    'description' => 'Projeto para testar se workflow_stage é criado como número 1',
    'budget_number' => '2025.0' . rand(100, 999) . '.V1',
    'project_type' => 'residencial',
    'document_template' => 'projeto_teste_06_08',
    'client_id' => '6887cdaf0cf3a8.51328972',
    'analyst_id' => '6887cb7c384b75.22527396',
    'status' => 'pendente',
    'priority' => 'media',
    'deadline' => '2025-08-15'
];

// Importar e usar o controller
require_once __DIR__ . '/../src/Controllers/ProjectController.php';

$controller = new \App\Controllers\ProjectController();

// Capturar a saída para evitar redirecionamento
ob_start();

try {
    $controller->create();
    $output = ob_get_contents();
} catch (\Exception $e) {
    $output = "Erro: " . $e->getMessage();
} finally {
    ob_end_clean();
}

// Verificar se o projeto foi criado
$db = new \App\Core\Database();
$projects = $db->findAll('projects');

// Encontrar o projeto mais recente
uasort($projects, function($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

$latestProject = reset($projects);

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Teste de criação de projeto concluído',
    'latest_project' => [
        'id' => $latestProject['id'],
        'name' => $latestProject['name'],
        'workflow_stage' => $latestProject['workflow_stage'],
        'workflow_stage_type' => gettype($latestProject['workflow_stage']),
        'created_at' => $latestProject['created_at']
    ],
    'output' => $output
], JSON_PRETTY_PRINT);
?>
