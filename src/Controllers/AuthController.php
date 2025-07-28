<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /login');
            exit;
        }

        // Verificar se é requisição JSON
        $isJsonRequest = isset($_SERVER['CONTENT_TYPE']) && 
                        strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;

        if ($isJsonRequest) {
            $input = json_decode(file_get_contents('php://input'), true);
            $email = $input['email'] ?? '';
            $password = $input['password'] ?? '';
        } else {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
        }

        if (Auth::login($email, $password)) {
            if ($isJsonRequest) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Login realizado com sucesso']);
                exit;
            } else {
                header('Location: /dashboard');
                exit;
            }
        }

        // Verificar se o usuário existe mas não está ativo
        $db = new Database();
        $user = $db->findBy('users', 'email', $email);
        $errorMessage = 'Credenciais inválidas';
        
        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'pendente') {
                $errorMessage = 'Sua conta ainda não foi aprovada. Aguarde a aprovação de um administrador.';
            } elseif ($user['status'] === 'rejeitado') {
                $errorMessage = 'Sua conta foi rejeitada. Entre em contato com o administrador.';
            } elseif ($user['status'] === 'inativo') {
                $errorMessage = 'Sua conta está inativa. Entre em contato com o administrador.';
            }
        }

        if ($isJsonRequest) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMessage]);
            exit;
        } else {
            $error = $errorMessage;
            require_once __DIR__ . '/../../views/auth/login.php';
        }
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /login');
        exit;
    }

    public function profile(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $user = Auth::user();
        require_once __DIR__ . '/../../views/auth/profile.php';
    }

    public function updateProfile(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $db = new Database();
        $userId = Auth::id();
        
        $data = [
            'name' => $_POST['name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Se uma nova senha foi fornecida
        if (!empty($_POST['password'])) {
            $data['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
        }

        $db->update('users', $userId, $data);
        
        header('Location: /profile?success=profile_updated');
        exit;
    }

    public function registerForm(): void
    {
        if (Auth::check()) {
            header('Location: /dashboard');
            exit;
        }
        
        require_once __DIR__ . '/../../views/auth/register.php';
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /auth/register');
            exit;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $requestedRole = 'cliente'; // Sempre cliente por padrão
        $password = $_POST['password'] ?? '';
        $passwordConfirmation = $_POST['password_confirmation'] ?? '';

        // Validações
        if (empty($name) || empty($email) || empty($password) || empty($passwordConfirmation)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: /auth/register');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'E-mail inválido.';
            header('Location: /auth/register');
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = 'A senha deve ter pelo menos 6 caracteres.';
            header('Location: /auth/register');
            exit;
        }

        if ($password !== $passwordConfirmation) {
            $_SESSION['error'] = 'As senhas não coincidem.';
            header('Location: /auth/register');
            exit;
        }

        $db = new Database();

        // Verificar se o e-mail já existe
        $existingUser = $db->findBy('users', 'email', $email);
        if ($existingUser) {
            $_SESSION['error'] = 'Este e-mail já está cadastrado no sistema.';
            header('Location: /auth/register');
            exit;
        }

        // Criar usuário com status pendente
        $userData = [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'company' => $company,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $requestedRole,
            'status' => 'pendente', // Status pendente para aprovação
            'requested_role' => $requestedRole,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $userId = $db->insert('users', $userData);

        // Criar notificação para administradores e coordenadores
        $admins = $db->findAll('users', ['role' => 'administrador']);
        $coordinators = $db->findAll('users', ['role' => 'coordenador']);
        $approvers = array_merge($admins, $coordinators);

        foreach ($approvers as $approver) {
            $notificationData = [
                'user_id' => $approver['id'],
                'type' => 'user_registration',
                'title' => 'Nova Solicitação de Cadastro',
                'message' => "O usuário {$name} ({$email}) solicitou cadastro como {$requestedRole}.",
                'data' => json_encode([
                    'new_user_id' => $userId,
                    'requested_role' => $requestedRole,
                    'user_name' => $name,
                    'user_email' => $email
                ]),
                'is_read' => false,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $db->insert('notifications', $notificationData);
        }

        $_SESSION['success'] = 'Cadastro realizado com sucesso! Aguarde a aprovação de um administrador ou coordenador para acessar o sistema.';
        header('Location: /auth/register');
        exit;
    }
}
