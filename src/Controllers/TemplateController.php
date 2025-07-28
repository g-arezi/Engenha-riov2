<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class TemplateController
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage')) {
            header('Location: /dashboard');
            exit;
        }

        $templates = $this->getTemplates();
        $title = 'Gerenciar Templates - Engenha Rio';
        $showSidebar = true;
        $showNavbar = true;

        require_once __DIR__ . '/../../views/admin/templates.php';
    }

    public function create(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage')) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        $title = 'Criar Template - Engenha Rio';
        $showSidebar = true;
        $showNavbar = true;

        require_once __DIR__ . '/../../views/admin/create_template.php';
    }

    public function edit(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage')) {
            header('Location: /dashboard');
            exit;
        }

        $templates = $this->getTemplates();
        $template = $templates[$id] ?? null;
        
        if (!$template) {
            header('Location: /admin/templates');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->update($id);
            return;
        }

        $title = 'Editar Template - Engenha Rio';
        $showSidebar = true;
        $showNavbar = true;

        require_once __DIR__ . '/../../views/admin/edit_template.php';
    }

    public function delete(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage')) {
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            return;
        }

        $templates = $this->getTemplates();
        
        if (isset($templates[$id])) {
            unset($templates[$id]);
            $this->saveTemplates($templates);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => 'Template excluído com sucesso!']);
        } else {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['success' => false, 'message' => 'Template não encontrado']);
        }
    }

    private function store(): void
    {
        $data = [
            'id' => $this->generateId($_POST['name']),
            'name' => $_POST['name'] ?? '',
            'code' => $this->generateCode($_POST['name']),
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? 'outros',
            'status' => $_POST['status'] ?? 'ativo',
            'required_documents' => $this->processRequiredDocuments($_POST),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $templates = $this->getTemplates();
        $templates[$data['id']] = $data;
        $this->saveTemplates($templates);

        header('Location: /admin/templates');
        exit;
    }

    private function update(string $id): void
    {
        $templates = $this->getTemplates();
        
        if (isset($templates[$id])) {
            $templates[$id]['name'] = $_POST['name'] ?? $templates[$id]['name'];
            $templates[$id]['description'] = $_POST['description'] ?? $templates[$id]['description'];
            $templates[$id]['category'] = $_POST['category'] ?? $templates[$id]['category'];
            $templates[$id]['status'] = $_POST['status'] ?? $templates[$id]['status'];
            $templates[$id]['required_documents'] = $this->processRequiredDocuments($_POST);
            $templates[$id]['updated_at'] = date('Y-m-d H:i:s');
            
            $this->saveTemplates($templates);
        }

        header('Location: /admin/templates');
        exit;
    }

    private function processRequiredDocuments(array $postData): array
    {
        $documents = [];
        
        if (isset($postData['documents']) && is_array($postData['documents'])) {
            foreach ($postData['documents'] as $index => $docName) {
                if (!empty($docName)) {
                    $documents[] = [
                        'name' => $docName,
                        'description' => $postData['doc_descriptions'][$index] ?? '',
                        'required' => isset($postData['doc_required'][$index]) ? true : false,
                        'format' => $postData['doc_formats'][$index] ?? 'PDF',
                        'max_size' => $postData['doc_max_sizes'][$index] ?? '10MB'
                    ];
                }
            }
        }
        
        return $documents;
    }

    private function getTemplates(): array
    {
        $file = __DIR__ . '/../../data/document_templates.json';
        if (!file_exists($file)) {
            return [];
        }
        
        $content = file_get_contents($file);
        return json_decode($content, true) ?? [];
    }

    private function saveTemplates(array $templates): void
    {
        $file = __DIR__ . '/../../data/document_templates.json';
        file_put_contents($file, json_encode($templates, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function generateId(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
    }

    private function generateCode(string $name): string
    {
        return strtolower(preg_replace('/[^a-zA-Z0-9]/', '_', $name));
    }

    public function getTemplatesByCategory(): array
    {
        $templates = $this->getTemplates();
        $categories = [];
        
        foreach ($templates as $template) {
            $category = $template['category'] ?? 'outros';
            $categories[$category][] = $template;
        }
        
        return $categories;
    }

    public function getActiveTemplates(): array
    {
        $templates = $this->getTemplates();
        return array_filter($templates, function($template) {
            return ($template['status'] ?? 'ativo') === 'ativo';
        });
    }
}
