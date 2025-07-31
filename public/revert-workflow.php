<?php
/**
 * Endpoint direto para retroceder etapa do projeto
 * Permite retroceder um projeto para a etapa anterior sem passar pelo router
 */

// Suprimir qualquer saída anterior
ob_start();

// Desativar exibição de erros para evitar HTML na saída
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Iniciar a sessão primeiro, antes de qualquer output
session_start();

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Verificação de autenticação
if (!Auth::check()) {
    error_log('Erro de autenticação em revert-workflow.php: Usuário não autenticado. SESSION: ' . json_encode($_SESSION));
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificação de permissão
if (!Auth::hasPermission('projects.manage_workflow')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sem permissão para gerenciar workflow']);
    exit;
}

// Verificar se é método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Log para debug
error_log('Chamada para revert-workflow.php - POST: ' . json_encode($_POST));
error_log('Chamada para revert-workflow.php - JSON: ' . file_get_contents('php://input'));

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$projectId = $input['project_id'] ?? $_GET['id'] ?? null;

if (!$projectId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID do projeto não fornecido']);
    exit;
}

// Inicializar banco de dados
$db = new Database();

// Buscar projeto
$project = $db->find('projects', $projectId);
if (!$project) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
    exit;
}

// Verificar etapa atual
$currentStage = $project['workflow_stage'] ?? 1;

// Se já está na primeira etapa, retornar erro
if ($currentStage <= 1) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa inicial']);
    exit;
}

// Calcular etapa anterior
$newStage = $currentStage - 1;

// Preparar dados para atualização
$updateData = [
    'workflow_stage' => $newStage,
    'updated_at' => date('Y-m-d H:i:s')
];

// Se retrocedeu da etapa final, alterar status se necessário
if ($currentStage === 5) {
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
            'title' => 'Projeto retrocedeu de etapa',
            'message' => 'O projeto foi retrocedido para a etapa: ' . $stageNames[$newStage],
            'type' => 'workflow_reverted',
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $db->insert('notifications', $notification);
    } catch (\Exception $e) {
        error_log('Erro ao criar notificação: ' . $e->getMessage());
        // Não impedir o fluxo se a notificação falhar
    }
    
    // Limpar qualquer saída anterior
    ob_clean();
    
    // Definir o cabeçalho e enviar a resposta JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Projeto retrocedido para a etapa anterior: ' . $stageNames[$newStage],
        'old_stage' => $currentStage,
        'new_stage' => $newStage
    ]);
} else {
    // Limpar qualquer saída anterior
    ob_clean();
    
    // Definir o cabeçalho e enviar a resposta JSON
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar etapa do projeto']);
}

// Certifique-se de que toda a saída foi enviada
ob_end_flush();
exit;
