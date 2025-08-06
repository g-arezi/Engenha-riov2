<?php
/**
 * Versão simplificada do advance-workflow para teste
 * Remove verificações de autenticação para debug
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

header('Content-Type: application/json');

// Log para debug
error_log('advance-workflow-simple.php - Iniciado');

// Verificar se é método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['project_id'] ?? $_GET['id'] ?? 'proj_1753892536_9899';

error_log('advance-workflow-simple.php - Project ID: ' . $projectId);

if (!$projectId) {
    echo json_encode(['success' => false, 'message' => 'ID do projeto não fornecido']);
    exit;
}

// Inicializar banco de dados
$db = new Database();

// Buscar projeto
$project = $db->find('projects', $projectId);
if (!$project) {
    echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
    exit;
}

// Verificar etapa atual
$currentStage = $project['workflow_stage'] ?? 1;
error_log('advance-workflow-simple.php - Current stage: ' . $currentStage);

// Se já está na última etapa, retornar erro
if ($currentStage >= 5) {
    echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa final']);
    exit;
}

// Calcular próxima etapa
$newStage = $currentStage + 1;
error_log('advance-workflow-simple.php - New stage: ' . $newStage);

// Preparar dados para atualização
$updateData = [
    'workflow_stage' => $newStage,
    'updated_at' => date('Y-m-d H:i:s')
];

// Se chegou na etapa final e status não está como concluído, marcar como ativo
if ($newStage === 5 && $project['status'] !== 'concluido') {
    $updateData['status'] = 'ativo';
}

// Atualizar projeto
$updated = $db->update('projects', $projectId, $updateData);
error_log('advance-workflow-simple.php - Update result: ' . ($updated ? 'true' : 'false'));

if ($updated) {
    // Criar notificação
    $stageNames = [
        1 => 'Documentos',
        2 => 'Projeto',
        3 => 'Produção',
        4 => 'Buildup',
        5 => 'Aprovado'
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Projeto avançado para a próxima etapa: ' . $stageNames[$newStage],
        'old_stage' => $currentStage,
        'new_stage' => $newStage
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar etapa do projeto']);
}

error_log('advance-workflow-simple.php - Finalizado');
?>
