<?php

namespace App\Core;

class Auth
{
    private static ?array $user = null;

    public static function login(string $email, string $password): bool
    {
        $db = new Database();
        $user = $db->findBy('users', 'email', $email);

        if (!$user || !password_verify($password, $user['password'])) {
            return false;
        }

        if ($user['status'] !== 'ativo') {
            return false;
        }

        self::$user = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        
        return true;
    }

    public static function logout(): void
    {
        self::$user = null;
        session_destroy();
    }

    public static function user(): ?array
    {
        if (self::$user !== null) {
            return self::$user;
        }

        if (!isset($_SESSION['user_id'])) {
            return null;
        }

        $db = new Database();
        self::$user = $db->find('users', $_SESSION['user_id']);
        
        return self::$user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function id(): ?string
    {
        $user = self::user();
        return $user['id'] ?? null;
    }

    public static function role(): ?string
    {
        $user = self::user();
        return $user['role'] ?? null;
    }

    public static function isAdmin(): bool
    {
        return self::role() === 'administrador';
    }

    public static function isAnalista(): bool
    {
        return self::role() === 'analista';
    }

    public static function isCliente(): bool
    {
        return self::role() === 'cliente';
    }

    public static function isCoordenador(): bool
    {
        return self::role() === 'coordenador';
    }

    public static function hasPermission(string $permission): bool
    {
        $role = self::role();
        $permissions = self::getRolePermissions($role);
        
        return in_array($permission, $permissions);
    }

    private static function getRolePermissions(string $role): array
    {
        $permissions = [
            'administrador' => [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.delete', 'documents.approve', 'documents.reject',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'admin.view', 'admin.manage', 'admin.manage_permissions', 'admin.manage_documents',
                'support.view', 'support.manage'
            ],
            'analista' => [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.approve', 'documents.reject',
                'admin.view',
                'support.view', 'support.manage'
            ],
            'coordenador' => [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.approve', 'documents.reject',
                'admin.view',
                'support.view', 'support.manage'
            ],
            'cliente' => [
                'dashboard.view',
                'projects.view',
                'documents.view', 'documents.upload', 'documents.download',
                'support.view'
            ]
        ];

        return $permissions[$role] ?? [];
    }
}
