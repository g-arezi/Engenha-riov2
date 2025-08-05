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

        // Debug para verificar permissões
        error_log("Current user: " . Auth::id() . ", Has admin.view: " . (Auth::hasPermission('admin.view') ? 'Yes' : 'No') . ", Has support.manage: " . (Auth::hasPermission('support.manage') ? 'Yes' : 'No'));
        
        // Verificar se é uma requisição para atualização via AJAX
        $isAjaxRequest = isset($_GET['format']) && $_GET['format'] === 'json';
        if ($isAjaxRequest) {
            error_log("Requisição AJAX para atualização da lista de tickets");
        }
        
        // Carregar diretamente do arquivo JSON para evitar problemas com cache
        $tickets = [];
        $jsonFile = __DIR__ . '/../../data/support_tickets.json';
        
        if (file_exists($jsonFile)) {
            // Limpar cache de arquivo antes de ler
            clearstatcache(true, $jsonFile);
            
            $jsonContent = file_get_contents($jsonFile);
            error_log("JSON content: " . substr($jsonContent, 0, 100) . "...");
            
            $ticketsData = json_decode($jsonContent, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("JSON decode error: " . json_last_error_msg());
                $ticketsData = [];
            } else {
                $ticketsData = $ticketsData ?: [];
            }
            
            error_log("Tickets carregados diretamente do JSON: " . count($ticketsData));
            
            // Garantir que cada ticket tenha seu ID incluído
            foreach ($ticketsData as $id => $ticket) {
                if (!isset($ticket['id'])) {
                    $ticketsData[$id]['id'] = $id;
                }
                // Garantir que temos todos os campos necessários
                $tickets[$id] = $ticket;
            }
            
            // Log de todos os IDs de tickets carregados
            $ticketIds = array_keys($tickets);
            error_log("IDs de tickets carregados: " . implode(', ', $ticketIds));
        } else {
            error_log("ERRO: Arquivo de tickets não encontrado!");
        }
        
        // Filtrar tickets para usuários regulares
        if (!Auth::hasPermission('admin.view') && !Auth::hasPermission('support.manage')) {
            // Regular users can only see their own tickets
            $userTickets = [];
            foreach ($tickets as $id => $ticket) {
                if (isset($ticket['user_id']) && $ticket['user_id'] === Auth::id()) {
                    $userTickets[$id] = $ticket;
                }
            }
            $tickets = $userTickets;
            error_log("Regular user. Filtered tickets for this user: " . count($tickets));
        } else {
            error_log("Admin user. Total tickets: " . count($tickets));
        }
        
        // Debug: Mostrar todos os IDs de tickets
        $ticketIds = array_keys($tickets);
        error_log("Tickets IDs disponíveis: " . implode(", ", $ticketIds));
        
        // If no tickets found, do a direct check
        if (empty($tickets)) {
            error_log("No tickets found. This is a problem!");
        }
        
        // Count tickets by status
        $openCount = 0;
        $closedCount = 0;
        
        foreach ($tickets as $ticket) {
            if (isset($ticket['status']) && $ticket['status'] === 'fechado') {
                $closedCount++;
            } else {
                $openCount++;
            }
            // Debug: Log each ticket
            error_log("Ticket ID: " . $ticket['id'] . ", Subject: " . ($ticket['subject'] ?? 'N/A') . ", Status: " . ($ticket['status'] ?? 'N/A') . ", User: " . ($ticket['user_id'] ?? 'N/A'));
        }
        
        // Get user data to display names
        $users = [];
        if (file_exists(__DIR__ . '/../../data/users.json')) {
            $users = json_decode(file_get_contents(__DIR__ . '/../../data/users.json'), true) ?: [];
            error_log("Usuários carregados: " . count($users));
        }
        
        error_log("Antes de adicionar nomes, temos " . count($tickets) . " tickets");
        
        // Add user names to tickets - garantindo que todos os tickets sejam mantidos
        foreach ($tickets as $id => &$ticket) {
            error_log("Processando ticket ID=" . $id . ", User ID=" . $ticket['user_id']);
            
            $ticket['user_name'] = $ticket['user_id'];
            if (isset($users[$ticket['user_id']])) {
                $ticket['user_name'] = $users[$ticket['user_id']]['name'];
                error_log("Encontrou nome do usuário diretamente: " . $ticket['user_name']);
            } else {
                foreach ($users as $user) {
                    if (isset($user['id']) && $user['id'] === $ticket['user_id']) {
                        $ticket['user_name'] = $user['name'];
                        error_log("Encontrou nome do usuário por iteração: " . $ticket['user_name']);
                        break;
                    }
                }
            }
        }
        
        error_log("Depois de adicionar nomes, temos " . count($tickets) . " tickets");
        
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
        
        // Handle ticket paths with additional slashes or characters (bug fix for IDs with dots)
        if (strpos($id, '/') !== false) {
            $id = preg_replace('/\/+/', '', $id);
            error_log("Cleaned ID from slashes: " . $id);
        }
        
        // If we still can't find the ticket, try loading all tickets and find by ID
        $allTickets = $this->db->findAll('support_tickets');
        error_log("Available tickets: " . implode(', ', array_keys($allTickets)));
        
        // Look directly for the ticket ID in our array of tickets
        $ticket = $allTickets[$id] ?? null;
        
        if (!$ticket) {
            $_SESSION['error'] = 'Ticket não encontrado. ID: ' . $id;
            error_log("SupportController::show - Ticket not found: " . $id);
            header('Location: /support');
            exit;
        }
        
        error_log("Ticket found: " . $id);

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
            
            error_log("Criando novo ticket com dados: " . json_encode($data));
            
            // Verificar tickets existentes antes da inserção
            $existingTickets = [];
            $jsonFile = __DIR__ . '/../../data/support_tickets.json';
            
            if (file_exists($jsonFile)) {
                // Limpar cache do arquivo
                clearstatcache(true, $jsonFile);
                
                $jsonContent = file_get_contents($jsonFile);
                if (!empty($jsonContent)) {
                    $existingTickets = json_decode($jsonContent, true) ?: [];
                    error_log("Tickets existentes antes da inserção: " . count($existingTickets));
                } else {
                    error_log("Arquivo de tickets vazio ou ilegível");
                    $existingTickets = [];
                }
            }
            
            // Criar um ID único para o novo ticket
            $id = uniqid('', true);
            $data['id'] = $id;
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
            
            // Adicionar o novo ticket diretamente ao array
            $existingTickets[$id] = $data;
            
            // Salvar todos os tickets de volta para o arquivo
            file_put_contents($jsonFile, json_encode($existingTickets, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            
            error_log("Novo ticket salvo manualmente com ID: " . $id);
            
            // Verificar se o ticket foi salvo com sucesso
            clearstatcache(true, $jsonFile);
            $newTickets = json_decode(file_get_contents($jsonFile), true) ?: [];
            if (isset($newTickets[$id])) {
                error_log("Novo ticket confirmado no arquivo JSON");
            } else {
                error_log("ERRO: Novo ticket NÃO encontrado no arquivo JSON após salvar manualmente");
            }
            
            // Preparar uma mensagem de sucesso com parte do ID
            $_SESSION['success'] = 'Ticket criado com sucesso! ID: ' . substr($id, 0, 8);
            
            // Redirecionar para o arquivo de visualização direta
            error_log("Redirecionando para a página de visualização direta do ticket: " . $id);
            header('Location: /support-view.php?id=' . $id . '&nocache=' . time());
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
