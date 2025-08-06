<?php
// get-ticket-detail.php - Endpoint para obter detalhes do ticket via AJAX
// Display all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir o corretor de autenticação AJAX
require_once __DIR__ . '/ajax-auth-fix.php';

// Debug function
function debugLog($message) {
    error_log("[TICKET-DETAIL] " . $message);
}

debugLog("Starting ticket detail with session ID: " . session_id());
debugLog("User ID in session: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Verificar cabeçalhos personalizados para autenticação alternativa
$authUserId = $_SERVER['HTTP_X_AUTH_USER_ID'] ?? null;
$authUserRole = $_SERVER['HTTP_X_AUTH_USER_ROLE'] ?? null;

if ($authUserId && !isset($_SESSION['user_id'])) {
    debugLog("Autenticação via cabeçalho X-Auth-User-ID: " . $authUserId);
    $_SESSION['user_id'] = $authUserId;
    if ($authUserRole) {
        $_SESSION['user_role'] = $authUserRole;
        debugLog("Role definido via cabeçalho: " . $authUserRole);
    } else {
        $_SESSION['user_role'] = 'cliente'; // Padrão
        debugLog("Role padrão definido: cliente");
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    debugLog("AUTHENTICATION FAILED - No user_id in session after all authentication attempts");
    http_response_code(403);
    echo '<div class="alert alert-danger">
            <h4>Usuário não autenticado</h4>
            <p>Por favor, faça login novamente para acessar esta funcionalidade.</p>
            <p><small>Session ID: ' . session_id() . '</small></p>
            <button onclick="window.location.reload()" class="btn btn-sm btn-primary">
                <i class="fas fa-sync me-1"></i> Recarregar
            </button>
          </div>';
    exit;
}

// Get the ticket ID from query parameter
$id = $_GET['id'] ?? null;

if (!$id) {
    debugLog("No ticket ID provided");
    http_response_code(400);
    echo '<div class="alert alert-danger">ID do ticket não fornecido.</div>';
    exit;
}

debugLog("Looking for ticket with ID: " . $id);

// Load ticket data directly - verificar e registrar o caminho completo
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
$absolutePath = realpath($ticketsFile);
debugLog("Caminho do arquivo: " . $ticketsFile);
debugLog("Caminho absoluto: " . ($absolutePath ? $absolutePath : "Não encontrado"));

if (!file_exists($ticketsFile)) {
    debugLog("Tickets file not found");
    http_response_code(500);
    echo '<div class="alert alert-danger">Arquivo de tickets não encontrado: ' . $ticketsFile . '</div>';
    exit;
}

// Read ticket data com verificação adicional
$fileContent = @file_get_contents($ticketsFile);
if ($fileContent === false) {
    debugLog("Failed to read tickets file");
    http_response_code(500);
    echo '<div class="alert alert-danger">Falha ao ler arquivo de tickets.</div>';
    exit;
}

$ticketsData = json_decode($fileContent, true);
if ($ticketsData === null) {
    debugLog("Failed to parse JSON: " . json_last_error_msg());
    http_response_code(500);
    echo '<div class="alert alert-danger">Falha ao processar JSON de tickets: ' . json_last_error_msg() . '</div>';
    exit;
}
debugLog("Loaded " . count($ticketsData) . " tickets");

// Check if ticket exists directly by ID or by searching through all tickets
$ticket = null;
debugLog("Buscando ticket com ID: " . $id);
debugLog("Tipos de IDs encontrados: " . implode(", ", array_keys($ticketsData)));

if (isset($ticketsData[$id])) {
    debugLog("Ticket encontrado diretamente pelo ID como chave");
    $ticket = $ticketsData[$id];
    // Make sure the ID is stored in the ticket
    $ticket['id'] = $id;
} else {
    debugLog("Ticket não encontrado diretamente, buscando em todos os tickets");
    // Search through all tickets
    foreach ($ticketsData as $ticketId => $ticketData) {
        debugLog("Verificando ticket " . $ticketId);
        
        if (isset($ticketData['id']) && $ticketData['id'] === $id) {
            debugLog("Ticket encontrado pelo ID interno");
            $ticket = $ticketData;
            break;
        }
    }
}

if ($ticket === null) {
    debugLog("Ticket not found in data");
    http_response_code(404);
    echo '<div class="alert alert-danger">Ticket não encontrado (ID: '.$id.').</div>';
    exit;
}

debugLog("Found ticket: " . json_encode($ticket));

// Define permission helper function
function hasPermission($permission) {
    // Initialize permissions array
    $userPermissions = [];
    
    // Get user role from session
    $role = $_SESSION['user_role'] ?? 'cliente';
    
    // Define permissions based on role
    switch ($role) {
        case 'administrador':
            $userPermissions = [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.delete', 'documents.approve', 'documents.reject',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'admin.view', 'admin.manage', 'admin.manage_permissions', 'admin.manage_documents', 'admin.manage_users',
                'support.view', 'support.manage'
            ];
            break;
        case 'analista':
        case 'coordenador':
            $userPermissions = [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.approve', 'documents.reject',
                'admin.view', 'admin.manage_users',
                'support.view', 'support.manage'
            ];
            break;
        case 'cliente':
            $userPermissions = [
                'dashboard.view',
                'projects.view',
                'documents.view', 'documents.upload', 'documents.download',
                'support.view'
            ];
            break;
    }
    
    return in_array($permission, $userPermissions);
}

// Get replies for this ticket
$replies = [];
$repliesFile = __DIR__ . '/../data/support_replies.json';
if (file_exists($repliesFile)) {
    $repliesData = json_decode(file_get_contents($repliesFile), true) ?? [];
    
    foreach ($repliesData as $reply) {
        if (isset($reply['ticket_id']) && $reply['ticket_id'] === $id) {
            $replies[] = $reply;
        }
    }
}
debugLog("Found " . count($replies) . " replies for this ticket");

// Get user data for display names
$users = [];
$usersFile = __DIR__ . '/../data/users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?? [];
}

// Get creator name
$creatorName = $ticket['user_id'] ?? 'Unknown';
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
    $reply['user_name'] = $reply['user_id'] ?? 'Unknown'; // Default value
    
    if (isset($reply['user_id']) && isset($users[$reply['user_id']])) {
        $reply['user_name'] = $users[$reply['user_id']]['name'];
    } else if (isset($reply['user_id'])) {
        foreach ($users as $user) {
            if (isset($user['id']) && $user['id'] === $reply['user_id']) {
                $reply['user_name'] = $user['name'];
                break;
            }
        }
    }
}

// Sort replies by creation date
usort($replies, function($a, $b) {
    return strtotime($a['created_at'] ?? 0) - strtotime($b['created_at'] ?? 0);
});

// Output HTML for ticket details
?>
<div class="ticket-detail">
    <div class="ticket-card">
        <div class="ticket-header">
            <h2 class="h5 mb-0"><?= htmlspecialchars($ticket['subject']) ?></h2>
            <small><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></small>
        </div>
        <div class="card-body bg-white">
            <p><?= nl2br(htmlspecialchars($ticket['description'])) ?></p>
            <div class="d-flex align-items-center mt-3">
                <span class="badge bg-<?= $ticket['status'] === 'aberto' ? 'success' : 
                      ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary') ?> me-2 status-badge">
                    <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                </span>
                <span class="badge bg-<?= $ticket['priority'] === 'alta' ? 'danger' : 
                      ($ticket['priority'] === 'media' ? 'warning' : 'info') ?> status-badge">
                    <?= ucfirst($ticket['priority']) ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="h6 mb-0">Informações do Ticket</h3>
        </div>
        <div class="card-body">
            <p><strong>Criado por:</strong> <?= htmlspecialchars($creatorName) ?></p>
            <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></p>
            <p><strong>Status:</strong> 
                <span class="badge bg-<?= $ticket['status'] === 'aberto' ? 'success' : 
                      ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary') ?>">
                    <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                </span>
            </p>
            <p class="mb-0"><strong>Prioridade:</strong> 
                <span class="badge bg-<?= $ticket['priority'] === 'alta' ? 'danger' : 
                      ($ticket['priority'] === 'media' ? 'warning' : 'info') ?>">
                    <?= ucfirst($ticket['priority']) ?>
                </span>
            </p>
        </div>
    </div>
    
    <?php if (hasPermission('support.manage')): ?>
    <div class="card mt-3">
        <div class="card-header">
            <h3 class="h6 mb-0">Ações</h3>
        </div>
        <div class="card-body">
            <div class="d-grid gap-2">
                <button class="btn btn-outline-success btn-sm status-action-btn" 
                       data-ticket-id="<?= $ticket['id'] ?>" 
                       data-status="aberto">
                    <i class="fas fa-redo me-2"></i>Marcar como Aberto
                </button>
                <button class="btn btn-outline-warning btn-sm status-action-btn" 
                       data-ticket-id="<?= $ticket['id'] ?>" 
                       data-status="em_andamento">
                    <i class="fas fa-play me-2"></i>Marcar Em Andamento
                </button>
                <button class="btn btn-outline-secondary btn-sm status-action-btn" 
                       data-ticket-id="<?= $ticket['id'] ?>" 
                       data-status="fechado">
                    <i class="fas fa-check me-2"></i>Fechar Ticket
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <h3 class="h5 mt-4 mb-3">Respostas</h3>
    
    <?php if (empty($replies)): ?>
        <div class="alert alert-info">Nenhuma resposta ainda.</div>
    <?php else: ?>
        <?php foreach ($replies as $reply): ?>
            <div class="reply-item">
                <div class="d-flex justify-content-between">
                    <strong><?= htmlspecialchars($reply['user_name']) ?></strong>
                    <small><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></small>
                </div>
                <p class="mt-2"><?= nl2br(htmlspecialchars($reply['message'])) ?></p>
                
                <?php if (isset($reply['attachment']) && !empty($reply['attachment'])): ?>
                    <div class="mt-2">
                        <a href="<?= $reply['attachment'] ?>" target="_blank">
                            <img src="<?= $reply['attachment'] ?>" style="max-height: 200px; max-width: 100%;" class="img-thumbnail">
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <div class="card mt-4">
        <div class="card-header">
            <h4 class="h6 mb-0">Adicionar Resposta</h4>
        </div>
        <div class="card-body">
            <form class="reply-form" enctype="multipart/form-data" method="post" action="/reply-ticket-new.php?id=<?= $id ?>&ajax=1">
                <input type="hidden" name="ajax_reply" value="1">
                <input type="hidden" name="ticket_id" value="<?= $id ?>">
                <div class="mb-3">
                    <label for="message" class="form-label">Mensagem</label>
                    <textarea name="message" id="message" rows="4" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="attachment" class="form-label">Anexo (opcional)</label>
                    <input type="file" name="attachment" id="attachment" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary" id="enviar-resposta-btn">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Resposta
                </button>
            </form>
        </div>
    </div>
</div>
