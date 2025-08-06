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
error_log('Update Status: Recebendo requisição - Session ID: ' . session_id());
error_log('Update Status: Session data: ' . json_encode($_SESSION));

try {
    // Verificar autenticação
    if (!Auth::check()) {
        error_log('Update Status: Usuário não autenticado');
        echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
        exit;
    }

    $user = Auth::user();
    error_log('Update Status: Usuário autenticado: ' . $user['name'] . ' (Role: ' . $user['role'] . ')');

    // Verificar permissão
    if (!Auth::hasPermission('projects.manage_workflow')) {
        error_log('Update Status: Usuário sem permissão para gerenciar workflow');
        echo json_encode(['success' => false, 'message' => 'Acesso negado']);
        exit;
    }

    // Obter dados do corpo da requisição
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        error_log('Update Status: Dados inválidos recebidos');
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    $projectId = $input['project_id'] ?? null;
    $status = $input['status'] ?? null;

    error_log('Update Status: Project ID: ' . $projectId . ', New Status: ' . $status);

    if (!$projectId || !$status) {
        error_log('Update Status: Project ID ou status não fornecidos');
        echo json_encode(['success' => false, 'message' => 'ID do projeto e status são obrigatórios']);
        exit;
    }

    // Validar status
    $validStatuses = ['pendente', 'ativo', 'pausado', 'concluido', 'cancelado'];
    if (!in_array($status, $validStatuses)) {
        error_log('Update Status: Status inválido: ' . $status);
        echo json_encode(['success' => false, 'message' => 'Status inválido']);
        exit;
    }

    // Conectar ao banco de dados
    $db = new Database();

    // Verificar se o projeto existe
    $project = $db->find('projects', $projectId);
    if (!$project) {
        error_log('Update Status: Projeto não encontrado: ' . $projectId);
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
        exit;
    }

    error_log('Update Status: Projeto encontrado: ' . $project['name']);

    // Atualizar o status do projeto
    $success = $db->update('projects', $projectId, [
        'status' => $status,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

    if ($success) {
        error_log('Update Status: Status atualizado com sucesso');
        
        // Log da atividade
        $statusNames = [
            'pendente' => 'Pendente',
            'ativo' => 'Ativo',
            'pausado' => 'Pausado',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado'
        ];
        
        $statusName = $statusNames[$status] ?? 'Desconhecido';
        
        // Adicionar log de atividade (se existir sistema de logs)
        try {
            $db->insert('activity_logs', [
                'user_id' => $user['id'],
                'action' => 'update_project_status',
                'description' => "Status do projeto '{$project['name']}' alterado para '{$statusName}'",
                'project_id' => $projectId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (Exception $e) {
            // Ignorar erro de log se tabela não existir
            error_log('Update Status: Erro ao criar log de atividade: ' . $e->getMessage());
        }

        echo json_encode([
            'success' => true, 
            'message' => "Status atualizado para '{$statusName}' com sucesso!",
            'new_status' => $status,
            'status_name' => $statusName
        ]);
    } else {
        error_log('Update Status: Erro ao atualizar status no banco');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
    }

} catch (Exception $e) {
    error_log('Update Status: Exceção capturada: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
}
?>
