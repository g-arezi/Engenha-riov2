<?php
// Endpoint para obter respostas de um ticket em formato JSON para atualizações AJAX

// Log de depuração
function debug_log($message) {
    error_log('[TICKET-REFRESH] ' . $message);
}

debug_log('Iniciando requisição de atualização de respostas');

// Garantir que a resposta seja JSON
header('Content-Type: application/json');

// Iniciar sessão se ainda não iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Obter ID do ticket
$id = $_GET['id'] ?? null;
debug_log('ID do ticket recebido: ' . ($id ?: 'nenhum'));
if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID do ticket não fornecido']);
    exit;
}

// Verificar se o ticket existe (carregando diretamente do JSON)
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
if (!file_exists($ticketsFile)) {
    echo json_encode(['success' => false, 'message' => 'Arquivo de tickets não encontrado']);
    exit;
}

// Carregar tickets do JSON
$tickets = json_decode(file_get_contents($ticketsFile), true) ?? [];
$ticket = null;

// Encontrar o ticket específico
if (isset($tickets[$id])) {
    $ticket = $tickets[$id];
    $ticket['id'] = $id; // Garantir que o ID esteja presente
} else {
    foreach ($tickets as $t) {
        if (isset($t['id']) && $t['id'] === $id) {
            $ticket = $t;
            break;
        }
    }
}

if (!$ticket) {
    echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
    exit;
}

// Verificar permissões para visualizar este ticket
$currentUserId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'administrador' || 
                                            $_SESSION['user_role'] === 'analista' || 
                                            $_SESSION['user_role'] === 'coordenador');

if (!$isAdmin && $ticket['user_id'] !== $currentUserId) {
    echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este ticket']);
    exit;
}

// Obter respostas do ticket
$replies = [];
if (file_exists(__DIR__ . '/../data/support_replies.json')) {
    $repliesData = json_decode(file_get_contents(__DIR__ . '/../data/support_replies.json'), true) ?: [];
    
    // Check if there's a ticket ID in the ticket
    $ticketId = $ticket['id'] ?? $id;
    
    foreach ($repliesData as $reply) {
        if (isset($reply['ticket_id']) && $reply['ticket_id'] === $ticketId) {
            // Garantir que o nome do usuário esteja presente
            if (!isset($reply['user_name']) || empty($reply['user_name'])) {
                // Obter dados do usuário
                $users = [];
                if (file_exists(__DIR__ . '/../data/users.json')) {
                    $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true) ?: [];
                }
                
                // Tentar encontrar o nome do usuário
                $userId = $reply['user_id'] ?? 'unknown';
                if (isset($users[$userId])) {
                    $reply['user_name'] = $users[$userId]['name'];
                } else {
                    foreach ($users as $user) {
                        if (isset($user['id']) && $user['id'] === $userId) {
                            $reply['user_name'] = $user['name'];
                            break;
                        }
                    }
                }
                
                if (!isset($reply['user_name'])) {
                    $reply['user_name'] = $userId;
                }
            }
            
            $replies[] = $reply;
        }
    }
}

// Ordenar respostas por data de criação
usort($replies, function($a, $b) {
    return strtotime($a['created_at']) - strtotime($b['created_at']);
});

debug_log('Retornando ' . count($replies) . ' respostas para o ticket ' . $id);

// Retornar as respostas em formato JSON
$response = [
    'success' => true,
    'ticketId' => $ticket['id'],
    'replies' => $replies
];

echo json_encode($response);
exit;
