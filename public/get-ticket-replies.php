<?php
// Endpoint para obter respostas de um ticket em formato JSON para atualizações AJAX

// Log de depuração
function debug_log($message) {
    error_log('[TICKET-REFRESH] ' . $message);
}

debug_log('Iniciando requisição de atualização de respostas');

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Garantir que a resposta seja JSON
header('Content-Type: application/json');

// Iniciar sessão se ainda não iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se o usuário está autenticado
if (!Auth::check()) {
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

// Instanciar o banco de dados
$db = new Database();
$ticket = $db->find('support_tickets', $id);

if (!$ticket) {
    echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
    exit;
}

// Verificar permissões para visualizar este ticket
if (!(Auth::hasPermission('admin.view') || 
     Auth::hasPermission('support.manage') || 
     $ticket['user_id'] === Auth::id())) {
    echo json_encode(['success' => false, 'message' => 'Sem permissão para visualizar este ticket']);
    exit;
}

// Obter respostas do ticket
$replies = [];
if (file_exists(__DIR__ . '/../data/support_replies.json')) {
    $repliesData = json_decode(file_get_contents(__DIR__ . '/../data/support_replies.json'), true) ?: [];
    
    foreach ($repliesData as $reply) {
        if ($reply['ticket_id'] === $ticket['id']) {
            // Garantir que o nome do usuário esteja presente
            if (!isset($reply['user_name']) || empty($reply['user_name'])) {
                // Obter dados do usuário
                $users = [];
                if (file_exists(__DIR__ . '/../data/users.json')) {
                    $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true) ?: [];
                }
                
                // Tentar encontrar o nome do usuário
                $userId = $reply['user_id'];
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
