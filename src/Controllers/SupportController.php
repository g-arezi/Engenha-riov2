<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;

class SupportController
{
    private Database $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = new Database();
    }

    public function index(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $tickets = $this->db->findAll('support_tickets');
        require_once __DIR__ . '/../../views/support/index.php';
    }

    public function create(): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->store();
            return;
        }

        require_once __DIR__ . '/../../views/support/create.php';
    }

    public function show(string $id): void
    {
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }

        $ticket = $this->db->find('support_tickets', $id);
        if (!$ticket) {
            $_SESSION['error'] = 'Ticket não encontrado.';
            header('Location: /support');
            exit;
        }

        require_once __DIR__ . '/../../views/support/show.php';
    }

    public function reply(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('support.manage')) {
            header('Location: /support');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->storeReply($id);
            return;
        }

        header('Location: /support/' . $id);
        exit;
    }

    private function store(): void
    {
        try {
            // Validação básica
            if (empty($_POST['subject']) || empty($_POST['description'])) {
                $_SESSION['error'] = 'Assunto e descrição são obrigatórios.';
                header('Location: /support/create');
                exit;
            }

            $data = [
                'subject' => trim($_POST['subject']),
                'description' => trim($_POST['description']),
                'priority' => $_POST['priority'] ?? 'media',
                'status' => 'aberto',
                'user_id' => Auth::id()
            ];
            
            $id = $this->db->insert('support_tickets', $data);
            
            if ($id) {
                $_SESSION['success'] = 'Ticket criado com sucesso! ID: ' . substr($id, 0, 8);
                header('Location: /support?success=ticket_created');
            } else {
                $_SESSION['error'] = 'Erro ao criar ticket. Tente novamente.';
                header('Location: /support/create?error=creation_failed');
            }
        } catch (\Exception $e) {
            error_log('Erro ao criar ticket: ' . $e->getMessage());
            $_SESSION['error'] = 'Erro interno. Contate o administrador.';
            header('Location: /support/create?error=internal_error');
        }
        exit;
    }

    private function storeReply(string $ticketId): void
    {
        $data = [
            'ticket_id' => $ticketId,
            'message' => $_POST['message'] ?? '',
            'user_id' => Auth::id(),
            'is_staff' => Auth::hasPermission('support.manage')
        ];

        $this->db->insert('support_replies', $data);
        
        // Atualizar status do ticket se necessário
        if (isset($_POST['status'])) {
            $this->db->update('support_tickets', $ticketId, ['status' => $_POST['status']]);
        }

        header('Location: /support/' . $ticketId);
        exit;
    }

    public function updateStatus(string $id): void
    {
        if (!Auth::check() || !Auth::hasPermission('support.manage')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Acesso negado']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $newStatus = $input['status'] ?? '';

        $allowedStatuses = ['aberto', 'em_andamento', 'fechado'];
        if (!in_array($newStatus, $allowedStatuses)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Status inválido']);
            exit;
        }

        $updated = $this->db->update('support_tickets', $id, ['status' => $newStatus]);
        
        if ($updated) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
        }
        exit;
    }
}
