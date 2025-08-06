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
error_log('Update Stage: Recebendo requisição - Session ID: ' . session_id());
error_log('Update Stage: Session data: ' . json_encode($_SESSION));

try {
    // Verificar autenticação
    if (!Auth::check()) {
        error_log('Update Stage: Usuário não autenticado');
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }

    $user = Auth::user();
    error_log('Update Stage: Usuário autenticado: ' . $user['name'] . ' (Role: ' . $user['role'] . ')');

    // Verificar permissão
    if (!Auth::hasPermission('projects.manage_workflow')) {
        error_log('Update Stage: Usuário sem permissão para gerenciar workflow');
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    // Obter dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        error_log('Update Stage: Dados inválidos recebidos');
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    $projectId = $input['project_id'] ?? null;
    $stage = $input['stage'] ?? null;

    error_log('Update Stage: Project ID: ' . $projectId . ', New Stage: ' . $stage);

    if (!$projectId || !$stage) {
        error_log('Update Stage: Project ID ou stage não fornecidos');
        echo json_encode(['success' => false, 'message' => 'ID do projeto e etapa são obrigatórios']);
        exit;
    }

    // Validar stage
    $stage = intval($stage);
    if ($stage < 1 || $stage > 5) {
        error_log('Update Stage: Stage inválido: ' . $stage);
        echo json_encode(['success' => false, 'message' => 'Etapa inválida']);
        exit;
    }

    // Conectar ao banco de dados
    $db = new Database();

    // Verificar se o projeto existe
    $project = $db->find('projects', $projectId);
    if (!$project) {
        error_log('Update Stage: Projeto não encontrado: ' . $projectId);
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
        exit;
    }

    error_log('Update Stage: Projeto encontrado: ' . $project['name']);

    // Atualizar a etapa do projeto
    $success = $db->update('projects', $projectId, [
        'workflow_stage' => $stage,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($success) {
        error_log('Update Stage: Etapa atualizada com sucesso');
        
        // Log da atividade
        $stageNames = [
            1 => 'Documentos',
            2 => 'Projeto', 
            3 => 'Produção',
            4 => 'Buildup',
            5 => 'Aprovado'
        ];
        
        $stageName = $stageNames[$stage] ?? 'Desconhecida';
        
        // Adicionar log de atividade (se existir sistema de logs)
        try {
            $db->insert('activity_logs', [
                'user_id' => $user['id'],
                'action' => 'update_workflow_stage',
                'description' => "Etapa do projeto '{$project['name']}' alterada para '{$stageName}'",
                'project_id' => $projectId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Ignorar erro de log se tabela não existir
            error_log('Update Stage: Erro ao criar log de atividade: ' . $e->getMessage());
        }

        echo json_encode([
            'success' => true, 
            'message' => "Etapa atualizada para '{$stageName}' com sucesso!",
            'new_stage' => $stage,
            'stage_name' => $stageName
        ]);
    } else {
        error_log('Update Stage: Erro ao atualizar etapa no banco');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar etapa']);
    }

} catch (Exception $e) {
    error_log('Update Stage: Exceção capturada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
