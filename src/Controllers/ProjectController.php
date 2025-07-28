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
        if (!Auth::check() || !Auth::hasPermission('projects.view')) {
            header('Location: /projects');
            exit;
        }

        $project = $this->db->find('projects', $id);
        if (!$project) {
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
            header('Location: /projects');
            exit;
        }

        $this->db->delete('projects', $id);
        header('Location: /projects');
        exit;
    }

    private function store(): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'project_type' => $_POST['project_type'] ?? '',
            'document_template' => $_POST['document_template'] ?? '',
            'client_id' => $_POST['client_id'] ?? '',
            'analyst_id' => $_POST['analyst_id'] ?? '',
            'status' => $_POST['status'] ?? 'pendente',
            'priority' => $_POST['priority'] ?? 'media',
            'deadline' => $_POST['deadline'] ?? null,
            'workflow_stage' => 'documentos',
            'created_by' => Auth::id(),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Validar campos obrigatórios
        if (empty($data['name']) || empty($data['description']) || empty($data['client_id']) || empty($data['project_type']) || empty($data['document_template'])) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios, incluindo o template de documentos.';
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
        header('Location: /projects/' . $projectId);
        exit;
    }

    private function update(string $id): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
            'project_type' => $_POST['project_type'] ?? '',
            'document_template' => $_POST['document_template'] ?? '',
            'client_id' => $_POST['client_id'] ?? '',
            'analyst_id' => $_POST['analyst_id'] ?? '',
            'status' => $_POST['status'] ?? 'pendente',
            'priority' => $_POST['priority'] ?? 'media',
            'deadline' => $_POST['deadline'] ?? null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->update('projects', $id, $data);
        header('Location: /projects/' . $id);
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
}
