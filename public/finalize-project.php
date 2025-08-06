<?php
require_once '../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Configurar headers para resposta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Verificar se é uma requisição POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Iniciar sessão se necessário
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Log da requisição para debug
error_log('Finalize Project: Recebendo requisição - Session ID: ' . session_id());
error_log('Finalize Project: Session data: ' . json_encode($_SESSION));

try {
    // Verificar autenticação
    if (!Auth::check()) {
        error_log('Finalize Project: Usuário não autenticado');
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }

    $user = Auth::user();
    error_log('Finalize Project: Usuário autenticado: ' . $user['name'] . ' (Role: ' . $user['role'] . ')');

    // Verificar permissão
    if (!Auth::hasPermission('projects.manage_workflow')) {
        error_log('Finalize Project: Usuário sem permissão para gerenciar workflow');
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    // Obter dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        error_log('Finalize Project: Dados inválidos recebidos');
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    $projectId = $input['project_id'] ?? null;

    error_log('Finalize Project: Project ID: ' . $projectId);

    if (!$projectId) {
        error_log('Finalize Project: Project ID não fornecido');
        echo json_encode(['success' => false, 'message' => 'ID do projeto é obrigatório']);
        exit;
    }

    // Conectar ao banco de dados
    $db = new Database();

    // Verificar se o projeto existe
    $project = $db->find('projects', $projectId);
    if (!$project) {
        error_log('Finalize Project: Projeto não encontrado: ' . $projectId);
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
        exit;
    }

    error_log('Finalize Project: Projeto encontrado: ' . $project['name']);

    // Verificar se o projeto está na etapa "Aprovado" (stage 5)
    $currentStage = $project['workflow_stage'] ?? 1;
    if ($currentStage != 5) {
        error_log('Finalize Project: Projeto não está na etapa "Aprovado": ' . $currentStage);
        echo json_encode(['success' => false, 'message' => 'Projeto deve estar na etapa "Aprovado" para ser finalizado']);
        exit;
    }

    // Finalizar o projeto (alterar status para "concluido")
    $success = $db->update('projects', $projectId, [
        'status' => 'concluido',
        'finalized_at' => date('Y-m-d H:i:s'),
        'finalized_by' => $user['id'],
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($success) {
        error_log('Finalize Project: Projeto finalizado com sucesso');
        
        // Log da atividade
        try {
            $db->insert('activity_logs', [
                'user_id' => $user['id'],
                'action' => 'finalize_project',
                'description' => "Projeto '{$project['name']}' foi finalizado",
                'project_id' => $projectId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Ignorar erro de log se tabela não existir
            error_log('Finalize Project: Erro ao criar log de atividade: ' . $e->getMessage());
        }

        echo json_encode([
            'success' => true, 
            'message' => "Projeto '{$project['name']}' foi finalizado com sucesso!",
            'project_id' => $projectId
        ]);
    } else {
        error_log('Finalize Project: Erro ao finalizar projeto no banco');
        echo json_encode(['success' => false, 'message' => 'Erro ao finalizar projeto']);
    }

} catch (Exception $e) {
    error_log('Finalize Project: Exceção capturada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
