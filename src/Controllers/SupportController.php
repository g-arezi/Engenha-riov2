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

        // Debug: Print directly to console for easier debugging
        echo "<!-- DEBUG: Current user: " . Auth::id() . ", Has admin.view: " . (Auth::hasPermission('admin.view') ? 'Yes' : 'No') . ", Has support.manage: " . (Auth::hasPermission('support.manage') ? 'Yes' : 'No') . " -->\n";

        // Carrega o arquivo JSON diretamente para debug
        $raw_tickets = json_decode(file_get_contents(__DIR__ . '/../../data/support_tickets.json'), true);
        echo "<!-- DEBUG: Raw tickets from JSON: " . count($raw_tickets) . " -->\n";
        
        // Fetch tickets based on user role
        if (Auth::hasPermission('admin.view') || Auth::hasPermission('support.manage')) {
            // Admin, coordinator, and analyst can see all tickets
            $tickets = $this->db->getAllData('support_tickets'); // Use getAllData to ensure we get all tickets
            echo "<!-- DEBUG: User has admin permissions. Total tickets found: " . count($tickets) . " -->\n";
            
            // Debug: Print all ticket IDs
            $ticketIds = array_keys($tickets);
            echo "<!-- DEBUG: Admin tickets IDs: " . implode(", ", $ticketIds) . " -->\n";
        } else {
            // Regular users can only see their own tickets
            $tickets = $this->db->findAll('support_tickets', ['user_id' => Auth::id()]);
            echo "<!-- DEBUG: Regular user. Tickets found for this user: " . count($tickets) . " -->\n";
        }
        
        // If no tickets found, do a direct check
        if (empty($tickets)) {
            echo "<!-- DEBUG: No tickets found. This is a problem! -->\n";
        }
        
        // Count tickets by status
        $openCount = 0;
        $closedCount = 0;
        
        foreach ($tickets as $ticket) {
            if ($ticket['status'] === 'fechado') {
                $closedCount++;
            } else {
                $openCount++;
            }
            // Debug: Log each ticket
            error_log("Ticket ID: " . $ticket['id'] . ", Subject: " . $ticket['subject'] . ", Status: " . $ticket['status'] . ", User: " . $ticket['user_id']);
        }
        
        // Get user data to display names
        $users = [];
        if (file_exists(__DIR__ . '/../../data/users.json')) {
            $users = json_decode(file_get_contents(__DIR__ . '/../../data/users.json'), true) ?: [];
            echo "<!-- DEBUG: Usuários carregados: " . count($users) . " -->\n";
        }
        
        echo "<!-- DEBUG: Antes de adicionar nomes, temos " . count($tickets) . " tickets -->\n";
        
        // Add user names to tickets - garantindo que todos os tickets sejam mantidos
        foreach ($tickets as $id => &$ticket) {
            echo "<!-- DEBUG: Processando ticket ID=" . $id . ", User ID=" . $ticket['user_id'] . " -->\n";
            
            $ticket['user_name'] = $ticket['user_id'];
            if (isset($users[$ticket['user_id']])) {
                $ticket['user_name'] = $users[$ticket['user_id']]['name'];
                echo "<!-- DEBUG: Encontrou nome do usuário diretamente: " . $ticket['user_name'] . " -->\n";
            } else {
                foreach ($users as $user) {
                    if (isset($user['id']) && $user['id'] === $ticket['user_id']) {
                        $ticket['user_name'] = $user['name'];
                        echo "<!-- DEBUG: Encontrou nome do usuário por iteração: " . $ticket['user_name'] . " -->\n";
                        break;
                    }
                }
            }
        }
        
        echo "<!-- DEBUG: Depois de adicionar nomes, temos " . count($tickets) . " tickets -->\n";
        
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

        // Log the ticket ID and user permissions for debugging
        error_log("SupportController::show - Looking for ticket with ID: " . $id);
        error_log("Current user: " . Auth::id() . ", Has admin.view: " . (Auth::hasPermission('admin.view') ? 'Yes' : 'No') . ", Has support.manage: " . (Auth::hasPermission('support.manage') ? 'Yes' : 'No'));
        
        $ticket = $this->db->find('support_tickets', $id);
        
        if (!$ticket) {
            $_SESSION['error'] = 'Ticket não encontrado.';
            error_log("SupportController::show - Ticket not found: " . $id);
            
            // Debug: Let's look at what tickets we have
            $allTickets = $this->db->findAll('support_tickets');
            error_log("Available tickets: " . json_encode(array_keys($allTickets)));
            
            header('Location: /support');
            exit;
        }

        error_log("SupportController::show - Ticket found: " . json_encode($ticket));

        // Check if user has permission to view this ticket
        if (!(Auth::hasPermission('admin.view') || 
              Auth::hasPermission('support.manage') || 
              $ticket['user_id'] === Auth::id())) {
            $_SESSION['error'] = 'Você não tem permissão para visualizar este ticket.';
            error_log("SupportController::show - Permission denied for user: " . Auth::id());
            header('Location: /support');
            exit;
        }

        // Get replies for this ticket
        $replies = [];
        if (file_exists(__DIR__ . '/../../data/support_replies.json')) {
            $repliesData = json_decode(file_get_contents(__DIR__ . '/../../data/support_replies.json'), true) ?: [];
            foreach ($repliesData as $reply) {
                if ($reply['ticket_id'] === $ticket['id']) {
                    $replies[] = $reply;
                }
            }
        }
        
        // Get users data to attach names to replies
        $users = [];
        if (file_exists(__DIR__ . '/../../data/users.json')) {
            $users = json_decode(file_get_contents(__DIR__ . '/../../data/users.json'), true) ?: [];
        }
        
        // Add creator name to ticket
        $creatorName = $ticket['user_id']; // Default
        if (isset($users[$ticket['user_id']])) {
            $creatorName = $users[$ticket['user_id']]['name'];
        } else {
            foreach ($users as $user) {
                if (isset($user['id']) && $user['id'] === $ticket['user_id']) {
                    $creatorName = $user['name'];
                    break;
                }
            }
        }
        
        // Add user names to replies
        foreach ($replies as &$reply) {
            $reply['user_name'] = $reply['user_id']; // Default
            
            // Direct access if user is stored with ID as key
            if (isset($users[$reply['user_id']])) {
                $reply['user_name'] = $users[$reply['user_id']]['name'];
                error_log("Found username directly: " . $reply['user_name'] . " for user_id: " . $reply['user_id']);
            } else {
                // Search for user
                foreach ($users as $userKey => $user) {
                    if (isset($user['id']) && $user['id'] === $reply['user_id']) {
                        $reply['user_name'] = $user['name'];
                        error_log("Found username by iteration: " . $reply['user_name'] . " for user_id: " . $reply['user_id']);
                        break;
                    }
                }
            }
        }

        require_once __DIR__ . '/../../views/support/show.php';
    }

    public function reply(string $id): void
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

        // Check if user has permission to reply
        if (!(Auth::hasPermission('admin.view') || 
             Auth::hasPermission('support.manage') || 
             $ticket['user_id'] === Auth::id())) {
            $_SESSION['error'] = 'Você não tem permissão para responder a este ticket.';
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
        // Obter o nome do usuário atual
        $userId = Auth::id();
        $userName = $userId; // Valor padrão
        
        // Carregar dados dos usuários para obter o nome
        if (file_exists(__DIR__ . '/../../data/users.json')) {
            $users = json_decode(file_get_contents(__DIR__ . '/../../data/users.json'), true) ?: [];
            
            // Tentar encontrar o nome do usuário
            if (isset($users[$userId])) {
                $userName = $users[$userId]['name'];
            } else {
                foreach ($users as $user) {
                    if (isset($user['id']) && $user['id'] === $userId) {
                        $userName = $user['name'];
                        break;
                    }
                }
            }
        }
        
        $data = [
            'ticket_id' => $ticketId,
            'message' => $_POST['message'] ?? '',
            'user_id' => $userId,
            'user_name' => $userName, // Adicionar nome do usuário
            'is_staff' => Auth::hasPermission('support.manage')
        ];

        $this->db->insert('support_replies', $data);
        
        // Atualizar status do ticket se necessário
        if (isset($_POST['status']) && !empty($_POST['status'])) {
            $this->db->update('support_tickets', $ticketId, ['status' => $_POST['status']]);
        }

        header('Location: /support/' . $ticketId);
        exit;
    }

    public function updateStatus(string $id): void
    {
        if (!Auth::check() || !(Auth::hasPermission('admin.view') || Auth::hasPermission('support.manage'))) {
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
