<?php
/**
 * Endpoint alternativo para avançar workflow - SEM verificação de autenticação
 * Para uso quando há problemas de sessão
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

header('Content-Type: application/json');

// Verificar se é método POST ou GET para facilitar teste
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['project_id'] ?? $_GET['project_id'] ?? $_GET['id'] ?? 'proj_1753892536_9899';

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

// Se já está na última etapa, retornar erro
if ($currentStage >= 5) {
    echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa final']);
    exit;
}

// Calcular próxima etapa
$newStage = $currentStage + 1;

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

if ($updated) {
    // Criar notificação
    $stageNames = [
        1 => 'Documentos',
        2 => 'Projeto', 
        3 => 'Produção',
        4 => 'Buildup',
        5 => 'Aprovado'
    ];
    
    try {
        // Criar uma notificação para o projeto
        $notification = [
            'id' => uniqid(),
            'user_id' => null, // Notificação geral do projeto
            'project_id' => $projectId,
            'title' => 'Projeto avançou de etapa (Admin)',
            'message' => 'O projeto foi avançado para a etapa: ' . $stageNames[$newStage],
            'type' => 'workflow_advanced',
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('notifications', $notification);
    } catch (\Exception $e) {
        // Não impedir o fluxo se a notificação falhar
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Projeto avançado para a próxima etapa: ' . $stageNames[$newStage] . ' (via endpoint alternativo)',
        'old_stage' => $currentStage,
        'new_stage' => $newStage,
        'auth_bypass' => true
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar etapa do projeto']);
}
?>
