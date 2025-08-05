<?php
// ticket-simple-view.php - New simple ticket viewer without Auth class
// Display all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Debug function
function debugLog($message) {
    error_log("[SIMPLE-TICKET] " . $message);
}

debugLog("Starting ticket view with session ID: " . session_id());
debugLog("Session data: " . json_encode($_SESSION));

// Setup user data for permission checks in the view
if (isset($_SESSION['user_id'])) {
    // Load user directly from the JSON file to avoid Auth class issues
    $usersFile = __DIR__ . '/../data/users.json';
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
        $currentUserId = $_SESSION['user_id'];
        
        // Set the user role and name in session if not already there
        if (isset($users[$currentUserId])) {
            $_SESSION['user_role'] = $users[$currentUserId]['role'] ?? 'cliente';
            $_SESSION['user_name'] = $users[$currentUserId]['name'] ?? $currentUserId;
            $_SESSION['user_avatar'] = $users[$currentUserId]['avatar'] ?? '';
        } else {
            foreach ($users as $user) {
                if (isset($user['id']) && $user['id'] === $currentUserId) {
                    $_SESSION['user_role'] = $user['role'] ?? 'cliente';
                    $_SESSION['user_name'] = $user['name'] ?? $currentUserId;
                    $_SESSION['user_avatar'] = $user['avatar'] ?? '';
                    break;
                }
            }
        }
    }
}

// Define permission helper functions needed by the template
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

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get the ticket ID from query parameter
$id = $_GET['id'] ?? null;

if (!$id) {
    debugLog("No ticket ID provided");
    $_SESSION['error'] = 'ID do ticket não fornecido.';
    header('Location: /support');
    exit;
}

debugLog("Looking for ticket with ID: " . $id);

// Load ticket data directly
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
if (!file_exists($ticketsFile)) {
    debugLog("Tickets file not found");
    $_SESSION['error'] = 'Arquivo de tickets não encontrado.';
    header('Location: /support');
    exit;
}

// Read ticket data
$ticketsData = json_decode(file_get_contents($ticketsFile), true) ?? [];
debugLog("Loaded " . count($ticketsData) . " tickets");

// Check if ticket exists directly by ID or by searching through all tickets
$ticket = null;
if (isset($ticketsData[$id])) {
    $ticket = $ticketsData[$id];
    // Make sure the ID is stored in the ticket
    $ticket['id'] = $id;
} else {
    // Search through all tickets
    foreach ($ticketsData as $ticketId => $ticketData) {
        if (isset($ticketData['id']) && $ticketData['id'] === $id) {
            $ticket = $ticketData;
            break;
        }
    }
}

if ($ticket === null) {
    debugLog("Ticket not found in data");
    $_SESSION['error'] = 'Ticket não encontrado.';
    header('Location: /support');
    exit;
}

debugLog("Found ticket: " . json_encode($ticket));

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

// Create a simple direct output instead of using the template
echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #' . substr($ticket['id'], 0, 8) . ' - Engenha Rio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .ticket-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        .ticket-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
        }
        .reply-item {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 20px;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4rem 0.8rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3">Ticket #' . substr($ticket['id'], 0, 8) . '</h1>
                    <a href="/support" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Voltar para Tickets
                    </a>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="ticket-card">
                    <div class="ticket-header">
                        <h2 class="h5 mb-0">' . htmlspecialchars($ticket['subject']) . '</h2>
                        <small>' . date('d/m/Y H:i', strtotime($ticket['created_at'])) . '</small>
                    </div>
                    <div class="card-body bg-white">
                        <p>' . nl2br(htmlspecialchars($ticket['description'])) . '</p>
                        <div class="d-flex align-items-center mt-3">
                            <span class="badge bg-' . ($ticket['status'] === 'aberto' ? 'success' : 
                                  ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary')) . ' me-2 status-badge">
                                ' . ucfirst(str_replace('_', ' ', $ticket['status'])) . '
                            </span>
                            <span class="badge bg-' . ($ticket['priority'] === 'alta' ? 'danger' : 
                                  ($ticket['priority'] === 'media' ? 'warning' : 'info')) . ' status-badge">
                                ' . ucfirst($ticket['priority']) . '
                            </span>
                        </div>
                    </div>
                </div>
                
                <h3 class="h5 mb-3">Respostas</h3>';

// Display replies
if (empty($replies)) {
    echo '<div class="alert alert-info">Nenhuma resposta ainda.</div>';
} else {
    foreach ($replies as $reply) {
        echo '<div class="reply-item">
            <div class="d-flex justify-content-between">
                <strong>' . htmlspecialchars($reply['user_name']) . '</strong>
                <small>' . date('d/m/Y H:i', strtotime($reply['created_at'])) . '</small>
            </div>
            <p class="mt-2">' . nl2br(htmlspecialchars($reply['message'])) . '</p>';
            
        // Show attachment if exists
        if (isset($reply['attachment']) && !empty($reply['attachment'])) {
            echo '<div class="mt-2">
                <a href="' . $reply['attachment'] . '" target="_blank">
                    <img src="' . $reply['attachment'] . '" style="max-height: 200px; max-width: 100%;" class="img-thumbnail">
                </a>
            </div>';
        }
            
        echo '</div>';
    }
}

// Add reply form
echo '<div class="card mt-4">
        <div class="card-header">
            <h4 class="h6 mb-0">Adicionar Resposta</h4>
        </div>
        <div class="card-body">
            <form action="/reply-ticket.php?id=' . $ticket['id'] . '&redirect=ticket-simple-view" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="message" class="form-label">Mensagem</label>
                    <textarea name="message" id="message" rows="4" class="form-control" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="attachment" class="form-label">Anexo (opcional)</label>
                    <input type="file" name="attachment" id="attachment" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane me-2"></i>Enviar Resposta
                </button>
            </form>
        </div>
    </div>';

echo '        </div>
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header">
                    <h3 class="h6 mb-0">Informações do Ticket</h3>
                </div>
                <div class="card-body">
                    <p><strong>Criado por:</strong> ' . htmlspecialchars($creatorName) . '</p>
                    <p><strong>Data:</strong> ' . date('d/m/Y H:i', strtotime($ticket['created_at'])) . '</p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-' . ($ticket['status'] === 'aberto' ? 'success' : 
                              ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary')) . '">
                            ' . ucfirst(str_replace('_', ' ', $ticket['status'])) . '
                        </span>
                    </p>
                    <p><strong>Prioridade:</strong> 
                        <span class="badge bg-' . ($ticket['priority'] === 'alta' ? 'danger' : 
                              ($ticket['priority'] === 'media' ? 'warning' : 'info')) . '">
                            ' . ucfirst($ticket['priority']) . '
                        </span>
                    </p>
                </div>
            </div>';

// Add action buttons for admins
if (hasPermission('support.manage')) {
    echo '<div class="card">
            <div class="card-header">
                <h3 class="h6 mb-0">Ações</h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-success btn-sm" onclick="updateStatus(\'' . $ticket['id'] . '\', \'aberto\')">
                        <i class="fas fa-redo me-2"></i>Marcar como Aberto
                    </button>
                    <button class="btn btn-outline-warning btn-sm" onclick="updateStatus(\'' . $ticket['id'] . '\', \'em_andamento\')">
                        <i class="fas fa-play me-2"></i>Marcar Em Andamento
                    </button>
                    <button class="btn btn-outline-secondary btn-sm" onclick="updateStatus(\'' . $ticket['id'] . '\', \'fechado\')">
                        <i class="fas fa-check me-2"></i>Fechar Ticket
                    </button>
                </div>
            </div>
        </div>';
}

echo '    </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateStatus(ticketId, status) {
    if (!confirm("Deseja alterar o status do ticket para " + status + "?")) {
        return;
    }
    
    fetch("/update-ticket-status.php?id=" + ticketId, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Status atualizado com sucesso!");
            window.location.reload();
        } else {
            alert("Erro ao atualizar status: " + data.message);
        }
    })
    .catch(error => {
        alert("Erro ao processar solicitação: " + error);
    });
}
</script>
</body>
</html>';
