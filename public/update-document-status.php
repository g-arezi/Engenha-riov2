<?php
/**
 * Endpoint direto para atualização de status de documento
 * Permite a atualização de status de documentos sem passar pelo router
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Verificação de autenticação
if (!Auth::check()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Verificação de permissão
if (!Auth::hasPermission('documents.approve')) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Sem permissão']);
    exit;
}

// Verificar se é método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Obter ID do documento
$documentId = $_GET['id'] ?? null;
if (!$documentId) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID do documento não fornecido']);
    exit;
}

// Obter dados da requisição
$input = json_decode(file_get_contents('php://input'), true);
$newStatus = $input['status'] ?? '';
$comments = $input['comments'] ?? '';
$rejectionReason = $input['rejection_reason'] ?? '';

// Validar status
$validStatuses = ['em_analise', 'aprovado', 'rejeitado'];
if (!in_array($newStatus, $validStatuses)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit;
}

// Inicializar banco de dados
$db = new Database();

// Buscar documento
$document = $db->find('project_documents', $documentId);
if (!$document) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
    exit;
}

// Preparar dados para atualização
$updateData = [
    'status' => $newStatus,
    'updated_at' => date('Y-m-d H:i:s')
];

// Adicionar campos específicos baseados no status
switch ($newStatus) {
    case 'aprovado':
        $updateData['approved_by'] = Auth::id();
        $updateData['approved_at'] = date('Y-m-d H:i:s');
        $updateData['comments'] = $comments;
        // Limpar campos de rejeição
        $updateData['rejected_by'] = null;
        $updateData['rejected_at'] = null;
        $updateData['rejection_reason'] = null;
        break;
        
    case 'rejeitado':
        $updateData['rejected_by'] = Auth::id();
        $updateData['rejected_at'] = date('Y-m-d H:i:s');
        $updateData['rejection_reason'] = $rejectionReason;
        // Limpar campos de aprovação
        $updateData['approved_by'] = null;
        $updateData['approved_at'] = null;
        $updateData['comments'] = $comments;
        break;
        
    case 'em_analise':
        // Limpar todos os campos de aprovação/rejeição
        $updateData['approved_by'] = null;
        $updateData['approved_at'] = null;
        $updateData['rejected_by'] = null;
        $updateData['rejected_at'] = null;
        $updateData['rejection_reason'] = null;
        $updateData['comments'] = $comments;
        break;
}

// Atualizar documento
$updated = $db->update('project_documents', $documentId, $updateData);

if ($updated) {
    // Criar notificação se necessário
    try {
        $notificationService = new \App\Services\NotificationService();
        $project = $db->find('projects', $document['project_id']);
        $uploader = $db->find('users', $document['uploaded_by']);

        if ($uploader) {
            $notificationType = '';
            $notificationData = [
                'user_id' => $uploader['id'],
                'project_id' => $document['project_id'],
                'document_id' => $documentId,
                'document_name' => $document['name'],
                'project_name' => $project['name'] ?? 'Projeto'
            ];

            switch ($newStatus) {
                case 'aprovado':
                    $notificationType = 'document_approved';
                    break;
                case 'rejeitado':
                    $notificationType = 'document_rejected';
                    $notificationData['reason'] = $rejectionReason;
                    break;
                case 'em_analise':
                    $notificationType = 'document_under_review';
                    break;
            }

            if ($notificationType) {
                $notificationService->createDocumentNotification($notificationType, $notificationData);
            }
        }
    } catch (\Exception $e) {
        // Apenas logamos o erro, mas não impedimos o fluxo
        error_log('Erro ao criar notificação: ' . $e->getMessage());
    }

    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => 'Status do documento atualizado com sucesso',
        'new_status' => $newStatus
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status do documento']);
}
exit;
