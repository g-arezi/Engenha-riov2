<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class ProjectController
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index(): void
    {
        if (!Auth::check() || !Auth::hasPermission('projects.view')) {
            header('Location: /dashboard');
            exit;
        }

        $user = Auth::user();
        $userId = Auth::id();
        
        // Administradores e coordenadores veem todos os projetos
        if ($user['role'] === 'administrador' || $user['role'] === 'coordenador') {
            $projects = $this->db->findAll('projects');
        }
        // Clientes só veem seus próprios projetos
        elseif ($user['role'] === 'cliente') {
            $projects = $this->db->findAll('projects', ['client_id' => $userId]);
        }
        // Analistas só veem projetos atribuídos a eles
        elseif ($user['role'] === 'analista') {
            $projects = $this->db->findAll('projects', ['analyst_id' => $userId]);
        }
        else {
            $projects = [];
        }
        
        // Carregar dados de usuários
        $users = $this->db->findAll('users');
        
        // Adicionar nomes de clientes e analistas aos projetos
        foreach ($projects as &$project) {
            // Adicionar nome do cliente
            if (!empty($project['client_id']) && isset($users[$project['client_id']])) {
                $project['client_name'] = $users[$project['client_id']]['name'];
            }
            
            // Adicionar nome do analista
            if (!empty($project['analyst_id']) && isset($users[$project['analyst_id']])) {
                $project['analyst_name'] = $users[$project['analyst_id']]['name'];
            }
        }

        require_once __DIR__ . '/../../views/projects/index.php';
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::hasPermission('projects.create')) {
            header('Location: /projects');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $clients = $this->db->findAll('users', ['role' => 'cliente']);
        $analysts = $this->db->findAll('users', ['role' => 'analista']);
        
        // Carregar templates disponíveis
        $templatesFile = __DIR__ . '/../../data/document_templates.json';
        $templates = [];
        if (file_exists($templatesFile)) {
            $content = file_get_contents($templatesFile);
            $allTemplates = json_decode($content, true) ?? [];
            // Filtrar apenas templates ativos
            $templates = array_filter($allTemplates, function($template) {
                return ($template['status'] ?? 'ativo') === 'ativo';
            });
        }
        
        require_once __DIR__ . '/../../views/projects/create.php';
    }

    public function show(string $id): void
    {
        // Log para debug
        error_log("Método show chamado com ID: " . $id);
        
        if (!Auth::check() || !Auth::hasPermission('projects.view')) {
            header('Location: /projects');
            exit;
        }

        // Adiciona trim para remover espaços indesejados
        $id = trim($id);
        
        $project = $this->db->find('projects', $id);
        if (!$project) {
            error_log("Projeto não encontrado com ID: " . $id);
            header('Location: /projects');
            exit;
        }

        $user = Auth::user();
        $userId = Auth::id();
        
        // Verificar se o usuário tem acesso ao projeto
        if ($user['role'] === 'cliente' && $project['client_id'] !== $userId) {
            header('Location: /projects');
            exit;
        }
        
        if ($user['role'] === 'analista' && $project['analyst_id'] !== $userId) {
            header('Location: /projects');
            exit;
        }

        // Buscar documentos do projeto
        $projectDocuments = $this->db->findAll('project_documents', ['project_id' => $id]);
        
        // Buscar também documentos da tabela geral de documentos
        $generalDocuments = $this->db->findAll('documents', ['project_id' => $id]);
        
        // Mesclar os dois arrays de documentos
        $projectDocuments = array_merge($projectDocuments, $generalDocuments);

        // Carregar template de documentos e calcular pendências
        $documentChecklist = $this->getDocumentChecklist($project, $projectDocuments);

        require_once __DIR__ . '/../../views/projects/show.php';
    }

    public function edit(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('projects.edit')) {
            header('Location: /projects');
            exit;
        }

        $project = $this->db->find('projects', $id);
        if (!$project) {
            header('Location: /projects');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }

        $clients = $this->db->findAll('users', ['role' => 'cliente']);
        $analysts = $this->db->findAll('users', ['role' => 'analista']);
        
        // Carregar templates disponíveis
        $templatesFile = __DIR__ . '/../../data/document_templates.json';
        $templates = [];
        if (file_exists($templatesFile)) {
            $content = file_get_contents($templatesFile);
            $allTemplates = json_decode($content, true) ?? [];
            // Filtrar apenas templates ativos
            $templates = array_filter($allTemplates, function($template) {
                return ($template['status'] ?? 'ativo') === 'ativo';
            });
        }
        
        require_once __DIR__ . '/../../views/projects/edit.php';
    }

    public function delete(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('projects.delete')) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        $success = $this->db->delete('projects', $id);
        
        header('Content-Type: application/json');
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Projeto excluído com sucesso']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir o projeto']);
        }
        exit;
    }

    private function store(): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'budget_number' => $_POST['budget_number'] ?? '',
            'project_type' => $_POST['project_type'] ?? '',
            'document_template' => $_POST['document_template'] ?? '',
            'client_id' => $_POST['client_id'] ?? '',
            'analyst_id' => $_POST['analyst_id'] ?? '',
            'status' => $_POST['status'] ?? 'pendente',
            'priority' => $_POST['priority'] ?? 'media',
            'deadline' => $_POST['deadline'] ?? null,
            'workflow_stage' => 1, // Etapa inicial (Documentos)
            'created_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validar campos obrigatórios
        if (empty($data['name']) || empty($data['description']) || empty($data['client_id']) || empty($data['project_type']) || empty($data['document_template']) || empty($data['budget_number'])) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios, incluindo o template de documentos e o número de orçamento.';
            header('Location: /projects/create');
            exit;
        }
        
        // Validar formato do número de orçamento - exatamente 1111.1111.V1
        if (!preg_match('/^\d{4}\.\d{4}\.V\d$/', $data['budget_number'])) {
            $_SESSION['error'] = 'O número de orçamento deve seguir exatamente o formato: 1111.1111.V1 (4 dígitos, ponto, 4 dígitos, ponto, letra V, 1 dígito).';
            header('Location: /projects/create');
            exit;
        }
        
        // Verificar se o template existe
        $templatesFile = __DIR__ . '/../../data/document_templates.json';
        $templates = [];
        if (file_exists($templatesFile)) {
            $content = file_get_contents($templatesFile);
            $templates = json_decode($content, true) ?? [];
        }
        
        if (!isset($templates[$data['document_template']])) {
            $_SESSION['error'] = 'Template de documentos selecionado não existe.';
            header('Location: /projects/create');
            exit;
        }

        // Usar um ID mais simples em vez de uniqid() para evitar problemas de URL
        $simpleId = 'proj_' . time() . '_' . rand(1000, 9999);
        $data['id'] = $simpleId; // Define o ID manualmente
        
        $projectId = $this->db->insert('projects', $data);
        
        // Criar notificação para o cliente
        $notificationData = [
            'user_id' => $data['client_id'],
            'type' => 'project_created',
            'title' => 'Novo Projeto Criado',
            'message' => 'O projeto "' . $data['name'] . '" foi criado e está aguardando documentos.',
            'data' => json_encode(['project_id' => $projectId]),
            'is_read' => false,
            'created_at' => date('Y-m-d H:i:s')
        ];
        $this->db->insert('notifications', $notificationData);
        
        // Se um analista foi atribuído, criar notificação
        if (!empty($data['analyst_id'])) {
            $analystNotificationData = [
                'user_id' => $data['analyst_id'],
                'type' => 'project_assigned',
                'title' => 'Projeto Atribuído',
                'message' => 'Você foi designado como analista do projeto "' . $data['name'] . '".',
                'data' => json_encode(['project_id' => $projectId]),
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('notifications', $analystNotificationData);
        }
        
        $_SESSION['success'] = 'Projeto criado com sucesso!';
        
        // Log para debug
        error_log("Redirecionando para: /projects/" . $projectId);
        
        header('Location: /projects/' . $projectId);
        exit;
    }

    private function update(string $id): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'budget_number' => $_POST['budget_number'] ?? '',
            'project_type' => $_POST['project_type'] ?? '',
            'document_template' => $_POST['document_template'] ?? '',
            'client_id' => $_POST['client_id'] ?? '',
            'analyst_id' => $_POST['analyst_id'] ?? '',
            'status' => $_POST['status'] ?? 'pendente',
            'priority' => $_POST['priority'] ?? 'media',
            'deadline' => $_POST['deadline'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validar campos obrigatórios na atualização
        if (empty($data['name']) || empty($data['description']) || empty($data['client_id']) || empty($data['project_type']) || empty($data['document_template']) || empty($data['budget_number'])) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios, incluindo o template de documentos e o número de orçamento.';
            header('Location: /projects/' . $id . '/edit');
            exit;
        }
        
        // Validar formato do número de orçamento - exatamente 1111.1111.V1
        if (!preg_match('/^\d{4}\.\d{4}\.V\d$/', $data['budget_number'])) {
            $_SESSION['error'] = 'O número de orçamento deve seguir exatamente o formato: 1111.1111.V1 (4 dígitos, ponto, 4 dígitos, ponto, letra V, 1 dígito).';
            header('Location: /projects/' . $id . '/edit');
            exit;
        }

        $this->db->update('projects', $id, $data);
        header('Location: /projects/' . urlencode($id));
        exit;
    }
    
    public function updateStatus(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('projects.edit')) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $status = $input['status'] ?? $_POST['status'] ?? null;

        if (!$status) {
            echo json_encode(['success' => false, 'message' => 'Status é obrigatório']);
            return;
        }

        try {
            $project = $this->db->find('projects', $id);
            if (!$project) {
                echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
                return;
            }

            $updateData = [
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $this->db->update('projects', $id, $updateData);
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
            
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro interno do servidor']);
        }
    }

    /**
     * Gera checklist de documentos baseado no template do projeto
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
