<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Services\NotificationService;

class DocumentWorkflowController
{
    private Database $db;
    private NotificationService $notificationService;

    public function __construct()
    {
        $this->db = new Database();
        $this->notificationService = new NotificationService();
    }

    public function projectDocuments(string $projectId): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.view')) {
            header('Location: /dashboard');
            exit;
        }

        $project = $this->db->find('projects', $projectId);
        if (!$project) {
            header('Location: /projects');
            exit;
        }

        // Buscar documentos do projeto
        $documents = $this->db->findAll('project_documents', ['project_id' => $projectId]);
        
        // Buscar templates de documentos
        $documentTemplates = $this->db->findAll('document_templates');
        
        // Organizar documentos por categoria/tipo
        $documentsByType = [];
        foreach ($documentTemplates as $template) {
            $documentsByType[$template['code']] = [
                'template' => $template,
                'document' => null,
                'status' => 'pendente'
            ];
        }
        
        // Mapear documentos existentes
        foreach ($documents as $doc) {
            if (isset($documentsByType[$doc['document_type']])) {
                $documentsByType[$doc['document_type']]['document'] = $doc;
                $documentsByType[$doc['document_type']]['status'] = $doc['status'];
            }
        }

        // Calcular progresso das etapas
        $stageProgress = $this->calculateStageProgress($documents);

        require_once __DIR__ . '/../../views/documents/project_workflow.php';
    }

    public function uploadDocument(): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.upload')) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeProjectDocument();
            return;
        }

        $projectId = $_GET['project_id'] ?? '';
        
        if (empty($projectId)) {
            header('Location: /projects');
            exit;
        }
        
        $project = $this->db->find('projects', $projectId);
        if (!$project) {
            header('Location: /projects');
            exit;
        }

        // Verificar se o usuário tem acesso ao projeto
        $user = Auth::user();
        $userId = Auth::id();
        
        if ($user['role'] === 'cliente' && $project['client_id'] !== $userId) {
            header('Location: /projects');
            exit;
        }
        
        if ($user['role'] === 'analista' && $project['analyst_id'] !== $userId) {
            header('Location: /projects');
            exit;
        }

        // Carregar template do projeto
        $template = null;
        $requiredDocuments = [];
        
        if (!empty($project['document_template'])) {
            $templatesFile = __DIR__ . '/../../data/document_templates.json';
            if (file_exists($templatesFile)) {
                $content = file_get_contents($templatesFile);
                $templates = json_decode($content, true) ?? [];
                $template = $templates[$project['document_template']] ?? null;
                
                if ($template && !empty($template['required_documents'])) {
                    $requiredDocuments = $template['required_documents'];
                }
            }
        }

        require_once __DIR__ . '/../../views/documents/upload.php';
    }

    public function approveDocument(string $documentId): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.approve')) {
            header('Location: /dashboard');
            exit;
        }

        $document = $this->db->find('project_documents', $documentId);
        if (!$document) {
            header('Location: /documents');
            exit;
        }

        // Aprovar documento
        $this->db->update('project_documents', $documentId, [
            'status' => 'aprovado',
            'approved_by' => Auth::id(),
            'approved_at' => date('Y-m-d H:i:s'),
            'comments' => $_POST['comments'] ?? ''
        ]);

        // Criar notificação
        $project = $this->db->find('projects', $document['project_id']);
        $this->notificationService->createDocumentNotification('document_approved', [
            'user_id' => $document['uploaded_by'],
            'project_id' => $document['project_id'],
            'document_id' => $documentId,
            'document_name' => $document['name'],
            'project_name' => $project['name'] ?? 'Projeto'
        ]);

        header('Location: /documents/project/' . $document['project_id']);
        exit;
    }

    public function rejectDocument(string $documentId): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.approve')) {
            header('Location: /dashboard');
            exit;
        }

        $document = $this->db->find('project_documents', $documentId);
        if (!$document) {
            header('Location: /documents');
            exit;
        }

        // Rejeitar documento
        $this->db->update('project_documents', $documentId, [
            'status' => 'rejeitado',
            'rejected_by' => Auth::id(),
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $_POST['rejection_reason'] ?? ''
        ]);

        // Criar notificação para o usuário que fez o upload
        $uploader = $this->db->find('users', $document['uploaded_by']);
        $project = $this->db->find('projects', $document['project_id']);
        
        if ($uploader) {
            $this->notificationService->createDocumentNotification('document_rejected', [
                'user_id' => $uploader['id'],
                'project_id' => $document['project_id'],
                'document_id' => $documentId,
                'document_name' => $document['name'],
                'project_name' => $project['name'] ?? 'Projeto',
                'reason' => $_POST['rejection_reason'] ?? ''
            ]);
        }

        header('Location: /documents/project/' . $document['project_id']);
        exit;
    }

    public function downloadDocument(string $documentId): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.download')) {
            header('Location: /dashboard');
            exit;
        }

        $document = $this->db->find('project_documents', $documentId);
        if (!$document) {
            header('Location: /documents');
            exit;
        }

        $filePath = __DIR__ . '/../../public/uploads/projects/' . $document['filename'];
        if (!file_exists($filePath)) {
            header('Location: /documents/project/' . $document['project_id']);
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $document['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    private function storeProjectDocument(): void
    {
        // Verificar se é requisição AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!isset($_FILES['document'])) {
            $message = 'Nenhum arquivo foi enviado';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            $projectId = $_POST['project_id'] ?? '';
            header('Location: /documents/project/upload?project_id=' . $projectId . '&error=' . urlencode($message));
            exit;
        }

        $file = $_FILES['document'];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'Arquivo muito grande (limite do servidor)',
                UPLOAD_ERR_FORM_SIZE => 'Arquivo muito grande (limite do formulário)',
                UPLOAD_ERR_PARTIAL => 'Upload incompleto',
                UPLOAD_ERR_NO_FILE => 'Nenhum arquivo selecionado',
                UPLOAD_ERR_NO_TMP_DIR => 'Diretório temporário não encontrado',
                UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever arquivo',
                UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensão'
            ];
            
            $message = $errorMessages[$file['error']] ?? 'Erro desconhecido no upload';
            $projectId = $_POST['project_id'] ?? '';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/project/upload?project_id=' . $projectId . '&error=' . urlencode($message));
            exit;
        }

        $originalName = $file['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        
        $uploadPath = __DIR__ . '/../../public/uploads/projects/';
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                $message = 'Erro ao criar diretório de upload';
                $projectId = $_POST['project_id'] ?? '';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
                header('Location: /documents/project/upload?project_id=' . $projectId . '&error=' . urlencode($message));
                exit;
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            $message = 'Erro ao mover arquivo para destino final';
            $projectId = $_POST['project_id'] ?? '';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/project/upload?project_id=' . $projectId . '&error=' . urlencode($message));
            exit;
        }

        $data = [
            'project_id' => $_POST['project_id'] ?? '',
            'name' => $_POST['name'] ?? $originalName,
            'description' => $_POST['description'] ?? '',
            'type' => $_POST['type'] ?? 'Outros',
            'category' => $_POST['category'] ?? 'Documento',
            'original_name' => $originalName,
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $file['type'],
            'uploaded_by' => Auth::id(),
            'status' => 'pendente',
            'stage' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validar campos obrigatórios
        if (empty($data['project_id']) || empty($data['name'])) {
            $message = 'Projeto e nome do documento são obrigatórios';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/project/upload?project_id=' . $data['project_id'] . '&error=' . urlencode($message));
            exit;
        }

        $documentId = $this->db->insert('documents', $data);

        // Criar notificação para analistas
        $project = $this->db->find('projects', $data['project_id']);
        if ($project && !empty($project['analyst_id'])) {
            $this->notificationService->createDocumentNotification('document_uploaded', [
                'user_id' => $project['analyst_id'],
                'project_id' => $data['project_id'],
                'document_id' => $documentId,
                'document_name' => $data['name'],
                'project_name' => $project['name'] ?? 'Projeto'
            ]);
        }

        $message = 'Documento enviado com sucesso!';
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $message]);
            exit;
        }
        
        header('Location: /projects/' . $data['project_id'] . '?success=' . urlencode($message));
        exit;
    }

    private function calculateStageProgress(array $documents): array
    {
        $stages = [
            'documentos' => ['pendente', 'enviado', 'aprovado'],
            'projeto' => ['pendente', 'em_analise', 'aprovado'], 
            'producao' => ['pendente', 'em_producao', 'concluido'],
            'buildup' => ['pendente', 'em_buildup', 'concluido'],
            'aprovado' => ['pendente', 'aprovado_final']
        ];

        $progress = [];
        foreach ($stages as $stage => $statuses) {
            $stageDocuments = array_filter($documents, function($doc) use ($stage) {
                return $doc['stage'] === $stage;
            });

            $total = count($stageDocuments);
            $completed = count(array_filter($stageDocuments, function($doc) {
                return in_array($doc['status'], ['aprovado', 'concluido', 'aprovado_final']);
            }));

            $progress[$stage] = [
                'total' => $total,
                'completed' => $completed,
                'percentage' => $total > 0 ? round(($completed / $total) * 100) : 0,
                'status' => $this->getStageStatus($completed, $total)
            ];
        }

        return $progress;
    }

    private function getStageStatus(int $completed, int $total): string
    {
        if ($total === 0) return 'disabled';
        if ($completed === 0) return 'pending';
        if ($completed === $total) return 'completed';
        return 'in_progress';
    }
    
    // NOVOS MÉTODOS AJAX PARA WORKFLOW DO PROJETO
    
    private function readProjects(): array
    {
        return $this->db->findAll('projects');
    }
    
    private function writeProjects(array $projects): void
    {
        foreach ($projects as $project) {
            $this->db->update('projects', $project['id'], $project);
        }
    }
    
    private function readProjectDocuments(): array
    {
        return $this->db->findAll('project_documents');
    }
    
    private function writeProjectDocuments(array $documents): void
    {
        foreach ($documents as $document) {
            $this->db->update('project_documents', $document['id'], $document);
        }
    }
    
    private function createNotification(string $projectId, string $title, string $message, string $type): void
    {
        // Criar notificação usando o método existente
        $notification = [
            'id' => uniqid(),
            'user_id' => null, // Notificação geral do projeto
            'project_id' => $projectId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('notifications', $notification);
    }
    
    public function updateStage()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('projects.manage_workflow')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $projectId = $input['project_id'] ?? null;
        $stage = $input['stage'] ?? null;

        if (!$projectId || !$stage) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            return;
        }

        try {
            $project = $this->db->find('projects', $projectId);
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }
            
            $this->db->update('projects', $projectId, [
                'workflow_stage' => (int)$stage,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Criar notificação
            $stageNames = [
                1 => 'Documentos',
                2 => 'Projeto',
                3 => 'Produção',
                4 => 'Buildup',
                5 => 'Aprovado'
            ];
            
            $this->createNotification(
                $projectId,
                'Etapa do projeto atualizada',
                'O projeto foi movido para a etapa: ' . $stageNames[$stage],
                'workflow_updated'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function updateStatus()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('projects.manage_workflow')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $projectId = $input['project_id'] ?? null;
        $status = $input['status'] ?? null;

        if (!$projectId || !$status) {
            echo json_encode(['success' => false, 'message' => 'Dados incompletos']);
            return;
        }

        try {
            $project = $this->db->find('projects', $projectId);
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }
            
            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Se o status for concluído, definir data de conclusão
            if ($status === 'concluido') {
                $updateData['end_date'] = date('Y-m-d');
                $updateData['workflow_stage'] = 5; // Aprovado
            }
            
            $this->db->update('projects', $projectId, $updateData);
            
            // Criar notificação
            $statusNames = [
                'pendente' => 'Pendente',
                'ativo' => 'Ativo',
                'pausado' => 'Pausado',
                'concluido' => 'Concluído',
                'cancelado' => 'Cancelado'
            ];
            
            $this->createNotification(
                $projectId,
                'Status do projeto atualizado',
                'O status do projeto foi alterado para: ' . $statusNames[$status],
                'status_updated'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function advance()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('projects.manage_workflow')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $projectId = $input['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto é obrigatório']);
            return;
        }

        try {
            $project = $this->db->find('projects', $projectId);
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }
            
            $currentStage = $project['workflow_stage'] ?? 1;
            
            if ($currentStage >= 5) {
                echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa final']);
                return;
            }
            
            $newStage = $currentStage + 1;
            $updateData = [
                'workflow_stage' => $newStage,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Se chegou na etapa final e status não está como concluído, marcar como ativo
            if ($newStage === 5 && $project['status'] !== 'concluido') {
                $updateData['status'] = 'ativo';
            }
            
            $this->db->update('projects', $projectId, $updateData);
            
            // Criar notificação
            $stageNames = [
                1 => 'Documentos',
                2 => 'Projeto',
                3 => 'Produção',
                4 => 'Buildup',
                5 => 'Aprovado'
            ];
            
            $this->createNotification(
                $projectId,
                'Projeto avançou de etapa',
                'O projeto foi avançado para a etapa: ' . $stageNames[$newStage],
                'workflow_advanced'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function revert()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('projects.manage_workflow')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $projectId = $input['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto é obrigatório']);
            return;
        }

        try {
            $project = $this->db->find('projects', $projectId);
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }
            
            $currentStage = $project['workflow_stage'] ?? 1;
            
            if ($currentStage <= 1) {
                echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa inicial']);
                return;
            }
            
            $newStage = $currentStage - 1;
            $updateData = [
                'workflow_stage' => $newStage,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Se retrocedeu da etapa final, alterar status se necessário
            if ($currentStage === 5) {
                $updateData['status'] = 'ativo';
            }
            
            $this->db->update('projects', $projectId, $updateData);
            
            // Criar notificação
            $stageNames = [
                1 => 'Documentos',
                2 => 'Projeto',
                3 => 'Produção',
                4 => 'Buildup',
                5 => 'Aprovado'
            ];
            
            $this->createNotification(
                $projectId,
                'Projeto retrocedeu de etapa',
                'O projeto foi retrocedido para a etapa: ' . $stageNames[$newStage],
                'workflow_reverted'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function finalize()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('projects.manage_workflow')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $projectId = $input['project_id'] ?? null;

        if (!$projectId) {
            echo json_encode(['success' => false, 'message' => 'ID do projeto é obrigatório']);
            return;
        }

        try {
            $project = $this->db->find('projects', $projectId);
            
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }
            
            $currentStage = $project['workflow_stage'] ?? 1;
            
            if ($currentStage < 5) {
                echo json_encode(['success' => false, 'message' => 'Projeto deve estar na etapa "Aprovado" para ser finalizado']);
                return;
            }
            
            $this->db->update('projects', $projectId, [
                'status' => 'concluido',
                'end_date' => date('Y-m-d'),
                'finalized_by' => Auth::user()['id'],
                'finalized_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // Criar notificação
            $this->createNotification(
                $projectId,
                'Projeto finalizado',
                'O projeto foi finalizado com sucesso por ' . Auth::user()['name'],
                'project_finalized'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    // MÉTODOS AJAX PARA APROVAÇÃO DE DOCUMENTOS
    
    public function approveDocumentAjax()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('documents.approve')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $documentId = $input['document_id'] ?? null;

        if (!$documentId) {
            echo json_encode(['success' => false, 'message' => 'ID do documento é obrigatório']);
            return;
        }

        try {
            // Buscar o documento
            $document = $this->db->find('project_documents', $documentId);
            
            if (!$document) {
                echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
                return;
            }
            
            // Atualizar status
            $this->db->update('project_documents', $documentId, [
                'status' => 'approved',
                'approved_by' => Auth::user()['id'],
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            // Criar notificação
            $this->createNotification(
                $document['project_id'],
                'Documento aprovado',
                'O documento ' . ($document['document_name'] ?? $document['name']) . ' foi aprovado',
                'document_approved'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
    
    public function rejectDocumentAjax()
    {
        header('Content-Type: application/json');
        
        if (!Auth::hasPermission('documents.approve')) {
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $documentId = $input['document_id'] ?? null;
        $comment = $input['comment'] ?? '';

        if (!$documentId) {
            echo json_encode(['success' => false, 'message' => 'ID do documento é obrigatório']);
            return;
        }

        try {
            // Buscar o documento
            $document = $this->db->find('project_documents', $documentId);
            
            if (!$document) {
                echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
                return;
            }
            
            // Atualizar status
            $this->db->update('project_documents', $documentId, [
                'status' => 'rejected',
                'approved_by' => Auth::user()['id'],
                'approved_at' => date('Y-m-d H:i:s'),
                'rejection_comment' => $comment
            ]);
            
            // Criar notificação
            $this->createNotification(
                $document['project_id'],
                'Documento rejeitado',
                'O documento ' . ($document['document_name'] ?? $document['name']) . ' foi rejeitado' . 
                ($comment ? ': ' . $comment : ''),
                'document_rejected'
            );
            
            echo json_encode(['success' => true]);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }
}
