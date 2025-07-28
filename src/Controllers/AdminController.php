<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class AdminController
{
    private Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function index(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.view')) {
            header('Location: /dashboard');
            exit;
        }

        $activeTab = $_GET['tab'] ?? 'usuarios_ativos';
        require_once __DIR__ . '/../../views/admin/index.php';
    }

    public function permissions(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_permissions')) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updatePermissions();
            return;
        }

        require_once __DIR__ . '/../../views/admin/permissions.php';
    }

    public function documents(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_documents')) {
            header('Location: /dashboard');
            exit;
        }

        $documents = $this->db->findAll('document_templates');
        require_once __DIR__ . '/../../views/admin/documents.php';
    }

    public function createDocument(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_documents')) {
            header('Location: /admin?tab=documentos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeDocument();
            return;
        }

        require_once __DIR__ . '/../../views/admin/create_document.php';
    }

    public function editDocument(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_documents')) {
            header('Location: /admin?tab=documentos');
            exit;
        }

        $document = $this->db->find('document_templates', $id);
        if (!$document) {
            header('Location: /admin?tab=documentos');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateDocument($id);
            return;
        }

        require_once __DIR__ . '/../../views/admin/edit_document.php';
    }

    public function deleteDocument(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_documents')) {
            header('Location: /admin?tab=documentos');
            exit;
        }

        $this->db->delete('document_templates', $id);
        header('Location: /admin?tab=documentos');
        exit;
    }

    private function updatePermissions(): void
    {
        // Implementar lógica de atualização de permissões
        // Por enquanto, apenas redirecionar com sucesso
        header('Location: /admin?tab=perfis_acesso&success=permissions_updated');
        exit;
    }

    private function storeDocument(): void
    {
        $code = $_POST['code'] ?? '';
        
        // Verificar se já existe um template com este código
        $existing = $this->db->findAll('document_templates', ['code' => $code]);
        if (!empty($existing)) {
            header('Location: /admin/documents/create?error=template_exists');
            exit;
        }
        
        $data = [
            'id' => $code,
            'name' => $_POST['name'] ?? '',
            'code' => $code,
            'description' => $_POST['description'] ?? '',
            'category' => $_POST['category'] ?? '',
            'status' => $_POST['status'] ?? 'ativo',
            'formats' => isset($_POST['formats']) ? implode(',', $_POST['formats']) : 'pdf',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->insert('document_templates', $data);
        header('Location: /admin?tab=documentos&success=template_created');
        exit;
    }

    private function updateDocument(string $id): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'code' => $_POST['code'] ?? '',
            'description' => $_POST['description'] ?? '',
            'status' => $_POST['status'] ?? 'ativo'
        ];

        $this->db->update('document_templates', $id, $data);
        header('Location: /admin?tab=documentos');
        exit;
    }

    public function createUser(): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_users')) {
            header('Location: /dashboard');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeUser();
            return;
        }

        require_once __DIR__ . '/../../views/admin/create_user.php';
    }

    public function editUser(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_users')) {
            header('Location: /admin');
            exit;
        }

        $user = $this->db->find('users', $id);
        if (!$user) {
            header('Location: /admin');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateUser($id);
            return;
        }

        require_once __DIR__ . '/../../views/admin/edit_user.php';
    }

    public function deleteUser(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_users')) {
            header('Location: /admin');
            exit;
        }

        // Não permitir deletar o próprio usuário
        if ($id === Auth::id()) {
            header('Location: /admin?error=cannot_delete_self');
            exit;
        }

        $this->db->delete('users', $id);
        header('Location: /admin?success=user_deleted');
        exit;
    }

    public function approveUser(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_users')) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }

        $this->db->update('users', $id, [
            'status' => 'ativo',
            'approved_at' => date('Y-m-d H:i:s'),
            'approved_by' => Auth::id()
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    public function rejectUser(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('admin.manage_users')) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }

        $this->db->update('users', $id, [
            'status' => 'rejeitado',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejected_by' => Auth::id()
        ]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
    }

    private function storeUser(): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
            'role' => $_POST['role'] ?? 'cliente',
            'status' => 'ativo',
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::id()
        ];

        // Verificar se email já existe
        $existingUser = $this->db->findAll('users', ['email' => $data['email']]);
        if (!empty($existingUser)) {
            header('Location: /admin/users/create?error=email_exists');
            exit;
        }

        $this->db->insert('users', $data);
        header('Location: /admin?success=user_created');
        exit;
    }

    private function updateUser(string $id): void
    {
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'role' => $_POST['role'] ?? 'cliente',
            'status' => $_POST['status'] ?? 'ativo',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Se senha foi fornecida, atualizar
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        // Verificar se email já existe (exceto para o próprio usuário)
        $existingUser = $this->db->findAll('users', ['email' => $data['email']]);
        if (!empty($existingUser) && $existingUser[0]['id'] !== $id) {
            header('Location: /admin/users/' . $id . '/edit?error=email_exists');
            exit;
        }

        $this->db->update('users', $id, $data);
        header('Location: /admin?success=user_updated');
        exit;
    }
}
