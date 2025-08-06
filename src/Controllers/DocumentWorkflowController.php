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
        
        // Gerar checklist de documentos baseado no template
        $documentChecklist = $this->getDocumentChecklist($project, $documents);
        
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

    public function updateDocumentStatus(): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.approve')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['document_id']) || !isset($data['status'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $documentId = $data['document_id'];
        $newStatus = $data['status'];
        $comments = $data['comments'] ?? '';
        $rejectionReason = $data['rejection_reason'] ?? '';

        // Validar status
        $validStatuses = ['em_analise', 'aprovado', 'rejeitado'];
        if (!in_array($newStatus, $validStatuses)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            exit;
        }

        $document = $this->db->find('project_documents', $documentId);
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
        $this->db->update('project_documents', $documentId, $updateData);

        // Criar notificação apropriada
        $project = $this->db->find('projects', $document['project_id']);
        $uploader = $this->db->find('users', $document['uploaded_by']);

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
                $this->notificationService->createDocumentNotification($notificationType, $notificationData);
            }
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Status do documento atualizado com sucesso',
            'new_status' => $newStatus
        ]);
        exit;
    }

    public function downloadDocument(string $documentId): void
    {
        // Detalhado log para diagnóstico
        error_log('=== INICIANDO DOWNLOAD DE DOCUMENTO ===');
        error_log('ID do documento: ' . $documentId);
        
        // Verificação de autenticação
        if (!Auth::check()) {
            error_log('Download falhou: Usuário não autenticado');
            if ($this->isAjaxRequest()) {
                $this->sendJsonError('Usuário não autenticado', 401);
            } else {
                header('Location: /login');
            }
            exit;
        }
        
        // Verificação de permissão
        if (!Auth::hasPermission('documents.download')) {
            error_log('Download falhou: Usuário sem permissão');
            if ($this->isAjaxRequest()) {
                $this->sendJsonError('Sem permissão para baixar documentos', 403);
            } else {
                header('Location: /dashboard');
            }
            exit;
        }

        // Busca do documento
        $document = $this->db->find('project_documents', $documentId);
        if (!$document) {
            error_log('Download falhou: Documento não encontrado no banco - ID: ' . $documentId);
            if ($this->isAjaxRequest()) {
                $this->sendJsonError('Documento não encontrado', 404);
            } else {
                header('Location: /documents');
            }
            exit;
        }

        error_log('Documento encontrado: ' . json_encode($document));
        
        // Lista de possíveis localizações para o arquivo
        $possiblePaths = [
            __DIR__ . '/../../public/uploads/projects/' . $document['filename'],
            __DIR__ . '/../../public/uploads/' . $document['filename'],
            __DIR__ . '/../../public/uploads/' . $documentId . '.pdf',
            __DIR__ . '/../../public/uploads/projects/' . $documentId . '.pdf'
        ];
        
        // Tentar todos os caminhos possíveis
        $filePath = null;
        foreach ($possiblePaths as $path) {
            error_log('Verificando caminho: ' . $path);
            if (file_exists($path)) {
                $filePath = $path;
                error_log('Arquivo encontrado em: ' . $path);
                break;
            }
        }
        
        // Se não encontrou o arquivo
        if (!$filePath) {
            error_log('Download falhou: Arquivo não encontrado em nenhum caminho');
            
            // Como último recurso, tentar encontrar qualquer arquivo no diretório com nome similar
            $alternativePath = $this->findAlternativeFile($documentId);
            if ($alternativePath) {
                error_log('Encontrado arquivo alternativo: ' . $alternativePath);
                $filePath = $alternativePath;
            } else {
                if ($this->isAjaxRequest()) {
                    $this->sendJsonError('Arquivo não encontrado. Possível problema no upload.', 404);
                } else {
                    header('Content-Type: text/html');
                    echo '<h1>Erro ao baixar documento</h1>';
                    echo '<p>O arquivo não foi encontrado no servidor. Possível problema no upload ou na configuração do sistema.</p>';
                    echo '<p><a href="/documents/project/' . $document['project_id'] . '">Voltar para a lista de documentos</a></p>';
                }
                exit;
            }
        }

        // Enviar o arquivo
        try {
            error_log('Iniciando envio do arquivo: ' . $filePath);
            
            // Verificar tamanho do arquivo
            $fileSize = filesize($filePath);
            if ($fileSize === false || $fileSize === 0) {
                throw new \Exception('Arquivo vazio ou inacessível');
            }
            
            error_log('Tamanho do arquivo: ' . $fileSize . ' bytes');
            
            // Desativar buffer de saída para evitar problemas com arquivos grandes
            if (ob_get_level()) ob_end_clean();
            
            // Configurar cabeçalhos para download
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . ($document['original_name'] ?? 'documento.pdf') . '"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . $fileSize);
            
            // Enviar o arquivo em partes para evitar problemas de memória
            $handle = fopen($filePath, 'rb');
            if ($handle === false) {
                throw new \Exception('Não foi possível abrir o arquivo');
            }
            
            // Enviar em blocos de 1MB
            $chunkSize = 1024 * 1024;
            while (!feof($handle)) {
                $buffer = fread($handle, $chunkSize);
                echo $buffer;
                flush();
            }
            fclose($handle);
            
            error_log('Download concluído com sucesso');
            exit;
        } catch (\Exception $e) {
            error_log('Erro crítico ao enviar arquivo: ' . $e->getMessage());
            if ($this->isAjaxRequest()) {
                $this->sendJsonError('Erro ao processar download: ' . $e->getMessage(), 500);
            } else {
                header('Content-Type: text/html');
                echo '<h1>Erro ao baixar documento</h1>';
                echo '<p>Ocorreu um erro durante o download: ' . $e->getMessage() . '</p>';
                echo '<p><a href="/documents/project/' . $document['project_id'] . '">Voltar para a lista de documentos</a></p>';
            }
            exit;
        }
    }
    
    // Função auxiliar para detectar requisições AJAX
    private function isAjaxRequest(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    // Função auxiliar para enviar erros JSON
    private function sendJsonError(string $message, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
    }
    
    // Função para encontrar um arquivo alternativo como último recurso
    private function findAlternativeFile(string $documentId): ?string
    {
        // Procurar em uploads/projects
        $projectsDir = __DIR__ . '/../../public/uploads/projects/';
        if (is_dir($projectsDir)) {
            $files = scandir($projectsDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($projectsDir . $file)) {
                    error_log('Arquivo encontrado em projects/: ' . $file);
                    return $projectsDir . $file;
                }
            }
        }
        
        // Procurar em uploads
        $uploadsDir = __DIR__ . '/../../public/uploads/';
        if (is_dir($uploadsDir)) {
            $files = scandir($uploadsDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && is_file($uploadsDir . $file) && !is_dir($uploadsDir . $file)) {
                    error_log('Arquivo encontrado em uploads/: ' . $file);
                    return $uploadsDir . $file;
                }
            }
        }
        
        return null;
    }

    private function storeProjectDocument(): void
    {
        // Verificar se é requisição AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
                  
        // Log para debug
        error_log('Document upload attempt - FILES: ' . json_encode($_FILES));
        error_log('Document upload attempt - POST: ' . json_encode($_POST));
        error_log('Document upload attempt - SERVER: ' . json_encode($_SERVER));
        error_log('Document upload attempt - Auth: ' . json_encode([
            'user_id' => Auth::id(),
            'has_permission' => Auth::hasPermission('documents.upload')
        ]));

        if (!isset($_FILES['document'])) {
            // Try alternate field name used in drag-and-drop
            if (isset($_FILES['file'])) {
                $_FILES['document'] = $_FILES['file'];
                error_log('Renamed file field from "file" to "document"');
            } else {
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
            error_log('Upload error: ' . $message);
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
        
        // Log para debug do caminho de upload
        error_log('Upload path: ' . $uploadPath);
        
        // Verificar existência do diretório
        if (!is_dir($uploadPath)) {
            error_log('Upload directory does not exist, attempting to create it');
            
            try {
                // Tentar criar o diretório com permissões mais abertas
                if (!mkdir($uploadPath, 0777, true)) {
                    throw new \Exception('Failed to create directory with mkdir');
                }
                
                // Verificar se o diretório foi realmente criado
                if (!is_dir($uploadPath)) {
                    throw new \Exception('Directory was not created successfully');
                }
                
                // Verificar permissões
                if (!is_writable($uploadPath)) {
                    chmod($uploadPath, 0777);
                    if (!is_writable($uploadPath)) {
                        throw new \Exception('Directory is not writable after chmod');
                    }
                }
                
                error_log('Upload directory created successfully: ' . $uploadPath);
            } catch (\Exception $e) {
                $message = 'Erro ao criar diretório de upload: ' . $e->getMessage();
                error_log($message);
                
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
        
        // Verificar permissão de escrita no diretório
        if (!is_writable($uploadPath)) {
            $message = 'Diretório de upload não tem permissão de escrita';
            error_log($message . ': ' . $uploadPath);
            
            $projectId = $_POST['project_id'] ?? '';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/project/upload?project_id=' . $projectId . '&error=' . urlencode($message));
            exit;
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            $message = 'Erro ao mover arquivo para destino final';
            error_log($message . ': from ' . $file['tmp_name'] . ' to ' . $uploadPath . $filename);
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
            'document_type' => $_POST['document_type'] ?? 'Outros',
            'category' => $_POST['category'] ?? 'Documento',
            'original_name' => $originalName,
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $file['type'],
            'uploaded_by' => Auth::id(),
            'status' => 'pendente',
            'stage' => $_POST['stage'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validar campos obrigatórios
        if (empty($data['project_id']) || empty($data['name'])) {
            $message = 'Projeto e nome do documento são obrigatórios';
            error_log($message . ': project_id=' . $data['project_id'] . ', name=' . $data['name']);
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/project/upload?project_id=' . $data['project_id'] . '&error=' . urlencode($message));
            exit;
        }

        // Adicionar um ID único para o documento se não existir
        if (!isset($data['id'])) {
            $data['id'] = uniqid('doc_');
        }
        
        $documentId = $this->db->insert('project_documents', $data);
        error_log('Document created with ID: ' . $documentId);

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
            echo json_encode([
                'success' => true, 
                'message' => $message,
                'document' => [
                    'id' => $documentId,
                    'name' => $data['name'],
                    'filename' => $filename
                ]
            ]);
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
        
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
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
        
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
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
        
        // Verificar autenticação primeiro
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
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
        
        // Verificar autenticação primeiro
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
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
        
        // Verificar autenticação primeiro
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            return;
        }
        
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
    
    /**
     * Handle drag and drop file uploads from page-functions.js
     */
    public function handleDragDropUpload(): void
    {
        header('Content-Type: application/json');
        
        try {
            // Log request information for debugging
            error_log('Drag-drop upload - REQUEST: ' . json_encode($_SERVER['REQUEST_METHOD']));
            error_log('Drag-drop upload - FILES: ' . json_encode($_FILES));
            error_log('Drag-drop upload - POST: ' . json_encode($_POST));
            
            // Check authentication
            if (!Auth::check()) {
                echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
                exit;
            }
            
            // Check permission
            if (!Auth::hasPermission('documents.upload')) {
                echo json_encode(['success' => false, 'message' => 'Sem permissão para upload de documentos']);
                exit;
            }
            
            // Check file upload
            $fileKey = isset($_FILES['document']) ? 'document' : (isset($_FILES['file']) ? 'file' : null);
            
            if (!$fileKey) {
                echo json_encode(['success' => false, 'message' => 'Nenhum arquivo foi enviado']);
                exit;
            }
            
            $file = $_FILES[$fileKey];
            
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
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            
            // Create upload directory if it doesn't exist
            $originalName = $file['name'];
            $extension = pathinfo($originalName, PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $extension;
            $uploadPath = __DIR__ . '/../../public/uploads/projects/';
            
            error_log('Drag-drop upload path: ' . $uploadPath);
            
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0777, true)) {
                    echo json_encode(['success' => false, 'message' => 'Erro ao criar diretório de upload']);
                    exit;
                }
            }
            
            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
                echo json_encode(['success' => false, 'message' => 'Erro ao mover arquivo para destino final']);
                exit;
            }
            
            // Get project ID from POST or try to extract from URL
            $projectId = $_POST['project_id'] ?? '';
            if (empty($projectId)) {
                $referer = $_SERVER['HTTP_REFERER'] ?? '';
                if (preg_match('/\/projects\/([^\/]+)/', $referer, $matches)) {
                    $projectId = $matches[1];
                    error_log('Extracted project ID from referer: ' . $projectId);
                }
            }
            
            if (empty($projectId)) {
                echo json_encode(['success' => false, 'message' => 'ID do projeto não foi fornecido']);
                exit;
            }
            
            // Prepare data to save
            $data = [
                'id' => uniqid('doc_'),
                'project_id' => $projectId,
                'name' => $originalName,
                'description' => $_POST['description'] ?? '',
                'document_type' => $_POST['document_type'] ?? 'Upload Manual',
                'type' => $_POST['type'] ?? 'Outros',
                'category' => $_POST['category'] ?? 'Documento',
                'original_name' => $originalName,
                'filename' => $filename,
                'size' => $file['size'],
                'mime_type' => $file['type'],
                'uploaded_by' => Auth::id(),
                'status' => 'pendente',
                'stage' => $_POST['stage'] ?? 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Save document to database
            $documentId = $this->db->insert('project_documents', $data);
            
            // Create notification for analysts
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
            
            // Return success response
            echo json_encode([
                'success' => true, 
                'message' => 'Documento enviado com sucesso!',
                'document' => [
                    'id' => $documentId,
                    'name' => $data['name'],
                    'filename' => $filename
                ]
            ]);
            
        } catch (\Exception $e) {
            error_log('Error in drag-drop upload: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erro no upload: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Method specifically for handling drag-and-drop file uploads from page-functions.js
     */
    public function uploadProjectFile(): void
    {
        header('Content-Type: application/json');
        error_log("Upload project file via AJAX called");
        
        // Check authentication
        if (!Auth::check()) {
            echo json_encode(['success' => false, 'message' => 'Not authenticated']);
            exit;
        }
        
        // Check permission
        if (!Auth::hasPermission('documents.upload')) {
            echo json_encode(['success' => false, 'message' => 'Permission denied']);
            exit;
        }
        
        // Debugging received data
        error_log("POST data: " . json_encode($_POST));
        error_log("FILES data: " . json_encode($_FILES));
        
        // Check if file is present
        if (!isset($_FILES['document'])) {
            // Try alternate field name used in drag-and-drop
            if (isset($_FILES['file'])) {
                $_FILES['document'] = $_FILES['file'];
            } else {
                echo json_encode(['success' => false, 'message' => 'No file uploaded']);
                exit;
            }
        }
        
        // Validate the file
        $file = $_FILES['document'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File too large (server limit)',
                UPLOAD_ERR_FORM_SIZE => 'File too large (form limit)',
                UPLOAD_ERR_PARTIAL => 'File uploaded partially',
                UPLOAD_ERR_NO_FILE => 'No file uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Temporary folder missing',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file',
                UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
            ];
            
            $message = $errorMessages[$file['error']] ?? 'Unknown upload error';
            echo json_encode(['success' => false, 'message' => $message]);
            exit;
        }
        
        // Get project ID
        $projectId = $_POST['project_id'] ?? '';
        if (empty($projectId)) {
            $referer = $_SERVER['HTTP_REFERER'] ?? '';
            if (preg_match('/\/projects\/([^\/]+)/', $referer, $matches)) {
                $projectId = $matches[1];
                error_log('Extracted project ID from referer: ' . $projectId);
            }
        }
        
        if (empty($projectId)) {
            echo json_encode(['success' => false, 'message' => 'Project ID is required']);
            exit;
        }
        
        // Process the file
        $originalName = $file['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../../public/uploads/projects/';
        
        error_log('Project file upload path: ' . $uploadPath);
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0777, true)) {
                echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
                exit;
            }
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file']);
            exit;
        }
        
        // Prepare document data
        $data = [
            'id' => uniqid('doc_'),
            'project_id' => $projectId,
            'name' => $originalName,
            'description' => $_POST['description'] ?? 'Uploaded via drag and drop',
            'document_type' => $_POST['document_type'] ?? 'Manual Upload',
            'type' => $_POST['type'] ?? 'Other',
            'category' => $_POST['category'] ?? 'Document',
            'original_name' => $originalName,
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $file['type'],
            'uploaded_by' => Auth::id(),
            'status' => 'pendente',
            'stage' => $_POST['stage'] ?? 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // Save document to database
        $documentId = $this->db->insert('project_documents', $data);
        
        // Create notification for analysts
        $project = $this->db->find('projects', $projectId);
        if ($project && !empty($project['analyst_id'])) {
            $this->notificationService->createDocumentNotification('document_uploaded', [
                'user_id' => $project['analyst_id'],
                'project_id' => $projectId,
                'document_id' => $documentId,
                'document_name' => $data['name'],
                'project_name' => $project['name'] ?? 'Project'
            ]);
        }
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Document uploaded successfully!',
            'document' => [
                'id' => $documentId,
                'name' => $data['name'],
                'filename' => $filename
            ]
        ]);
        exit;
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

    public function deleteDocument(string $documentId): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.delete')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Sem permissão para excluir documentos']);
            exit;
        }

        // Usar o método find do objeto database
        $document = $this->db->find('project_documents', $documentId);
        
        if (!$document) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
            exit;
        }
        
        // Deletar arquivo físico se existir
        $possiblePaths = [
            __DIR__ . '/../../public/uploads/projects/' . $document['filename'],
            __DIR__ . '/../../public/uploads/' . $document['filename'],
            __DIR__ . '/../../public/uploads/projects/' . $documentId . '.pdf',
            __DIR__ . '/../../public/uploads/' . $documentId . '.pdf'
        ];
        
        foreach ($possiblePaths as $filePath) {
            if (file_exists($filePath)) {
                error_log("Deletando arquivo: " . $filePath);
                unlink($filePath);
                break;
            }
        }

        // Remover do banco de dados usando o método do Database
        $deleted = $this->db->delete('project_documents', $documentId);
        
        if ($deleted) {
            // Adicionar notificação
            $this->notificationService->createDocumentNotification(
                'document_deleted',
                [
                    'user_id' => $document['uploaded_by'],
                    'project_id' => $document['project_id'],
                    'document_id' => $documentId,
                    'document_name' => $document['name'],
                    'project_name' => 'Projeto'
                ]
            );

            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Documento excluído com sucesso']);
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Falha ao excluir o documento do banco de dados']);
        }
        exit;
    }

    public function getDocumentInfo(string $documentId): void
    {
        // Iniciar a sessão se ainda não foi iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Log detalhado para diagnóstico
        error_log('=== OBTENDO INFO DO DOCUMENTO ===');
        error_log('ID do documento: ' . $documentId);
        error_log('Session ID: ' . session_id());
        error_log('Session conteúdo: ' . json_encode($_SESSION));
        
        // Garantir que os cabeçalhos de JSON sejam enviados corretamente
        header('Content-Type: application/json; charset=utf-8');
        
        // Verificar se estamos em uma requisição AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                 strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        // Log dos cabeçalhos HTTP para debugging
        error_log('HTTP Headers: ' . json_encode(getallheaders()));
        error_log('Is AJAX: ' . ($isAjax ? 'Yes' : 'No'));
        
        // Verificar autenticação de múltiplas maneiras
        $isAuthenticated = isset($_SESSION['user_id']);
        
        // Tentar usar Auth::check diretamente (deve iniciar sessão internamente)
        if (!$isAuthenticated) {
            error_log('Tentando Auth::check() como alternativa');
            $isAuthenticated = Auth::check();
            error_log('Auth::check() retornou: ' . ($isAuthenticated ? 'true' : 'false'));
        }
        
        if (!$isAuthenticated) {
            error_log('Erro: Usuário não autenticado (session user_id não existe e Auth::check() falhou)');
            echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
            exit;
        }

        try {
            $document = $this->db->find('project_documents', $documentId);
            
            if (!$document) {
                error_log('Erro: Documento não encontrado - ID: ' . $documentId);
                echo json_encode(['success' => false, 'message' => 'Documento não encontrado']);
                exit;
            }
            
            error_log('Documento encontrado: ' . json_encode($document));
            
            // Buscar informações do usuário que fez upload
            $uploader = null;
            if (!empty($document['uploaded_by'])) {
                $uploader = $this->db->find('users', $document['uploaded_by']);
            }
            
            // Buscar projeto
            $project = null;
            if (!empty($document['project_id'])) {
                $project = $this->db->find('projects', $document['project_id']);
            }

            // Montar dados de resposta
            $response = [
                'success' => true,
                'document' => [
                    'id' => $document['id'],
                    'name' => $document['name'] ?? 'Sem nome',
                    'description' => $document['description'] ?? '',
                    'original_name' => $document['original_name'] ?? $document['name'] ?? 'documento.pdf',
                    'size' => $document['size'] ?? 0,
                    'size_formatted' => isset($document['size']) ? $this->formatFileSize($document['size']) : 'N/A',
                    'mime_type' => $document['mime_type'] ?? 'application/pdf',
                    'status' => $document['status'] ?? 'pendente',
                    'status_label' => ucfirst(str_replace('_', ' ', $document['status'] ?? 'pendente')),
                    'created_at' => $document['created_at'] ?? date('Y-m-d H:i:s'),
                    'created_at_formatted' => isset($document['created_at']) ? date('d/m/Y H:i', strtotime($document['created_at'])) : date('d/m/Y H:i'),
                    'uploader' => $uploader ? $uploader['name'] : 'Usuário não encontrado',
                    'project_name' => $project ? $project['name'] : 'Projeto não encontrado',
                    'document_type' => $document['document_type'] ?? 'Outro',
                    'version' => $document['version'] ?? 1,
                    'comments' => $document['comments'] ?? '',
                    'approved_by' => $document['approved_by'] ?? null,
                    'approved_at' => $document['approved_at'] ?? null,
                    'rejected_by' => $document['rejected_by'] ?? null,
                    'rejected_at' => $document['rejected_at'] ?? null,
                    'rejection_reason' => $document['rejection_reason'] ?? null
                ]
            ];
            
            error_log('Enviando resposta: ' . json_encode($response));
            
            // Usar json_encode com opções corretas para evitar problemas de UTF-8
            echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            exit;
        } 
        catch (\Exception $e) {
            error_log('Erro ao obter informações do documento: ' . $e->getMessage());
            echo json_encode([
                'success' => false, 
                'message' => 'Erro ao processar informações do documento',
                'error' => $e->getMessage()
            ]);
            exit;
        }
    }

    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));
        
        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    /**
     * Gera checklist de documentos baseado no template do projeto
     * (Mesmo método do ProjectController para consistência)
     */
    private function getDocumentChecklist(array $project, array $projectDocuments): array
    {
        $checklist = [
            'required_documents' => [],
            'optional_documents' => [],
            'total_required' => 0,
            'completed_required' => 0,
            'completion_percentage' => 0
        ];

        // Verificar se o projeto tem template
        if (empty($project['document_template'])) {
            return $checklist;
        }

        // Carregar template
        $templatesFile = __DIR__ . '/../../data/document_templates.json';
        if (!file_exists($templatesFile)) {
            return $checklist;
        }

        $templates = json_decode(file_get_contents($templatesFile), true) ?? [];
        if (!isset($templates[$project['document_template']])) {
            return $checklist;
        }

        $template = $templates[$project['document_template']];
        if (!isset($template['required_documents'])) {
            return $checklist;
        }

        // Mapear documentos enviados por tipo/nome
        $uploadedDocuments = [];
        foreach ($projectDocuments as $doc) {
            // Verificar múltiplos campos possíveis para identificar o tipo do documento
            $docType = '';
            if (!empty($doc['document_type'])) {
                $docType = $doc['document_type'];
            } elseif (!empty($doc['type']) && $doc['type'] !== 'Template Document') {
                $docType = $doc['type'];
            } elseif (!empty($doc['name'])) {
                $docType = $doc['name'];
            }
            
            $docKey = strtolower(trim($docType));
            if (!isset($uploadedDocuments[$docKey])) {
                $uploadedDocuments[$docKey] = [];
            }
            $uploadedDocuments[$docKey][] = $doc;
        }

        // Processar documentos do template
        foreach ($template['required_documents'] as $index => $requiredDoc) {
            $docName = $requiredDoc['name'];
            $docKey = strtolower(trim($docName));
            $isRequired = $requiredDoc['required'] ?? false;
            
            // Verificar se foi enviado - buscar por diferentes chaves possíveis
            $uploadedFiles = [];
            
            // Primeira tentativa: busca exata pelo nome
            if (isset($uploadedDocuments[$docKey])) {
                $uploadedFiles = $uploadedDocuments[$docKey];
            } else {
                // Segunda tentativa: busca por correspondência parcial ou similar
                foreach ($uploadedDocuments as $key => $docs) {
                    // Verificar se a chave contém o nome do documento ou vice-versa
                    if (strpos($key, $docKey) !== false || strpos($docKey, $key) !== false) {
                        $uploadedFiles = array_merge($uploadedFiles, $docs);
                    }
                }
            }
            
            $isUploaded = !empty($uploadedFiles);
            
            // Pegar o status do documento mais recente
            $status = 'pendente';
            $lastUpload = null;
            if ($isUploaded) {
                // Ordenar por data de criação (mais recente primeiro)
                usort($uploadedFiles, function($a, $b) {
                    return strtotime($b['created_at']) - strtotime($a['created_at']);
                });
                $lastUpload = $uploadedFiles[0];
                $status = $lastUpload['status'] ?? 'pendente';
            }

            $documentStatus = [
                'name' => $docName,
                'description' => $requiredDoc['description'] ?? '',
                'required' => $isRequired,
                'format' => $requiredDoc['format'] ?? 'Todos',
                'max_size' => $requiredDoc['max_size'] ?? '50MB',
                'uploaded' => $isUploaded,
                'status' => $status,
                'upload_date' => $lastUpload ? $lastUpload['created_at'] : null,
                'file_info' => $lastUpload,
                'index' => $index
            ];

            if ($isRequired) {
                $checklist['required_documents'][] = $documentStatus;
                $checklist['total_required']++;
                if ($isUploaded && $status === 'aprovado') {
                    $checklist['completed_required']++;
                }
            } else {
                $checklist['optional_documents'][] = $documentStatus;
            }
        }

        // Calcular porcentagem de conclusão
        if ($checklist['total_required'] > 0) {
            $checklist['completion_percentage'] = round(
                ($checklist['completed_required'] / $checklist['total_required']) * 100
            );
        }

        return $checklist;
    }
}
