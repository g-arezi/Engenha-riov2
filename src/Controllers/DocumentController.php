<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class DocumentController
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index(): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.view')) {
            header('Location: /dashboard');
            exit;
        }

        // Obter o usuário atual e seu papel
        $currentUser = Auth::user();
        $userId = Auth::id();
        $userRole = $currentUser['role'] ?? '';
        
        // Se for administrador, analista ou coordenador, pode ver todos os documentos ou filtrados por projeto
        $isAdmin = in_array($userRole, ['administrador', 'analista', 'coordenador']);
        
        // Inicialmente, buscamos todos os documentos
        $documents = [];
        
        if ($isAdmin) {
            // Administradores, analistas e coordenadores veem:
            // 1. Seus próprios documentos
            // 2. Documentos dos projetos a que estão vinculados
            // 3. (Opcional) Se um projeto específico for selecionado via query string, apenas documentos desse projeto

            // Verificar se está filtrando por projeto específico
            $filterProjectId = $_GET['project_id'] ?? null;
            
            if ($filterProjectId) {
                // Filtra por projeto específico se solicitado
                $documents = $this->db->findAll('documents', ['project_id' => $filterProjectId]);
            } else {
                // Buscar projetos vinculados ao usuário (como analista, coordenador ou criador)
                $userProjects = $this->db->findAll('projects', [
                    'OR' => [
                        'analyst_id' => $userId,
                        'created_by' => $userId
                    ]
                ]);
                
                $projectIds = array_column($userProjects, 'id');
                
                // Buscar documentos vinculados aos projetos do usuário ou enviados pelo usuário
                if (!empty($projectIds)) {
                    $allDocs = $this->db->findAll('documents');
                    foreach ($allDocs as $doc) {
                        // Incluir documentos enviados pelo usuário
                        if ($doc['uploaded_by'] === $userId) {
                            $documents[] = $doc;
                            continue;
                        }
                        
                        // Incluir documentos vinculados aos projetos do usuário
                        if (!empty($doc['project_id']) && in_array($doc['project_id'], $projectIds)) {
                            $documents[] = $doc;
                        }
                    }
                } else {
                    // Se não tiver projetos, mostrar apenas os documentos enviados pelo próprio usuário
                    $documents = $this->db->findAll('documents', ['uploaded_by' => $userId]);
                }
            }
        } else {
            // Clientes comuns veem apenas seus próprios documentos
            $documents = $this->db->findAll('documents', ['uploaded_by' => $userId]);
        }
        
        // Buscar os projetos para exibir nomes em vez de IDs
        $projects = [];
        $allProjects = $this->db->findAll('projects');
        foreach ($allProjects as $project) {
            $projects[$project['id']] = $project;
        }
        
        // Buscar nomes de usuários para exibição
        $users = [];
        $allUsers = $this->db->findAll('users');
        foreach ($allUsers as $user) {
            $users[$user['id']] = $user;
        }
        
        require_once __DIR__ . '/../../views/documents/index.php';
    }

    public function upload(): void
    {
        // Iniciar sessão se não estiver ativa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!Auth::check() || !Auth::hasPermission('documents.upload')) {
            header('Location: /login?redirect=' . urlencode('/documents/upload'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $user = Auth::user();
        $userId = Auth::id();
        
        // Buscar projetos do usuário (se for cliente) ou todos (se for admin/analista)
        if ($user['role'] === 'cliente') {
            $projects = $this->db->findAll('projects', ['client_id' => $userId]);
        } else {
            $projects = $this->db->findAll('projects');
        }
        
        // Carregar templates para mostrar documentos requeridos
        $templates = $this->loadTemplates();
        
        // Se um projeto específico foi selecionado, buscar seus documentos requeridos
        $selectedProject = null;
        $requiredDocuments = [];
        if (isset($_GET['project_id'])) {
            $selectedProject = $this->db->find('projects', $_GET['project_id']);
            if ($selectedProject && !empty($selectedProject['document_template'])) {
                $template = $templates[$selectedProject['document_template']] ?? null;
                if ($template && !empty($template['required_documents'])) {
                    $requiredDocuments = $template['required_documents'];
                }
            }
        }
        
        require_once __DIR__ . '/../../views/documents/upload.php';
    }

    private function loadTemplates(): array
    {
        $file = __DIR__ . '/../../data/document_templates.json';
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    public function download(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.download')) {
            header('Location: /documents');
            exit;
        }

        $document = $this->db->find('documents', $id);
        if (!$document) {
            header('Location: /documents');
            exit;
        }

        $filePath = __DIR__ . '/../../public/uploads/' . $document['filename'];
        if (!file_exists($filePath)) {
            header('Location: /documents');
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $document['original_name'] . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    public function delete(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('documents.delete')) {
            header('Location: /documents');
            exit;
        }

        $document = $this->db->find('documents', $id);
        if ($document) {
            $filePath = __DIR__ . '/../../public/uploads/' . $document['filename'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->db->delete('documents', $id);
        }

        header('Location: /documents');
        exit;
    }

    private function store(): void
    {
        // Verificar se é requisição AJAX
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                  strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        // Log para debug
        error_log("Upload attempt - FILES: " . json_encode($_FILES));
        error_log("Upload attempt - POST: " . json_encode($_POST));
        error_log("Upload attempt - Session: " . json_encode($_SESSION));
        error_log("Upload attempt - Auth user: " . json_encode(Auth::user()));

        // Verificar se usuário está autenticado
        if (!Auth::check()) {
            $message = 'Usuário não autenticado';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message, 'redirect' => '/login']);
                exit;
            }
            header('Location: /login');
            exit;
        }

        // Verificar permissões
        if (!Auth::hasPermission('documents.upload')) {
            $message = 'Sem permissão para upload de documentos';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents?error=' . urlencode($message));
            exit;
        }

        if (!isset($_FILES['document'])) {
            $message = 'Nenhum arquivo foi enviado';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/upload?error=' . urlencode($message));
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
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/upload?error=' . urlencode($message));
            exit;
        }

        $originalName = $file['name'];
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        
        $uploadPath = __DIR__ . '/../../public/uploads/';
        if (!is_dir($uploadPath)) {
            if (!mkdir($uploadPath, 0755, true)) {
                $message = 'Erro ao criar diretório de upload';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $message]);
                    exit;
                }
                header('Location: /documents/upload?error=' . urlencode($message));
                exit;
            }
        }

        if (!move_uploaded_file($file['tmp_name'], $uploadPath . $filename)) {
            $message = 'Erro ao mover arquivo para destino final';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $message]);
                exit;
            }
            header('Location: /documents/upload?error=' . urlencode($message));
            exit;
        }

        $data = [
            'id' => uniqid('doc_'),
            'name' => $_POST['name'] ?? $originalName,
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'project_id' => $_POST['project_id'] ?? '',
            'original_name' => $originalName,
            'filename' => $filename,
            'size' => $file['size'],
            'mime_type' => $file['type'],
            'uploaded_by' => Auth::id(),
            'uploaded_at' => date('Y-m-d H:i:s'),
            'status' => 'ativo',
            'workflow_stage' => 'documentos',
            'approval_status' => 'pending'
        ];

        $this->db->insert('documents', $data);
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Documento enviado com sucesso!',
                'document_id' => $data['id'],
                'filename' => $filename,
                'document' => $data
            ]);
            exit;
        }
        
        // Redirecionar para a página de documentos com sucesso
        header('Location: /documents?success=uploaded&file=' . urlencode($data['name']) . '&project=' . urlencode($data['project_id']));
        exit;
    }
}
