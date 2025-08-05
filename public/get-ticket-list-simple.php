<?php
// Simplified ticket list that doesn't use Auth class
// Display all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Load tickets directly from JSON
header('Content-Type: text/html; charset=utf-8');

// Get user role and ID
$currentUserId = $_SESSION['user_id'];
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['administrador', 'analista', 'coordenador']);

// Load tickets
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
$tickets = [];
$openCount = 0;
$closedCount = 0;

if (file_exists($ticketsFile)) {
    $allTickets = json_decode(file_get_contents($ticketsFile), true) ?? [];
    
    // Filter tickets for regular users
    if (!$isAdmin) {
        $userTickets = [];
        foreach ($allTickets as $id => $ticket) {
            if (isset($ticket['user_id']) && $ticket['user_id'] === $currentUserId) {
                // Make sure ID is included in the ticket data
                if (!isset($ticket['id'])) {
                    $ticket['id'] = $id;
                }
                $userTickets[$id] = $ticket;
            }
        }
        $tickets = $userTickets;
    } else {
        // For admins, get all tickets but ensure IDs are set
        foreach ($allTickets as $id => $ticket) {
            if (!isset($ticket['id'])) {
                $ticket['id'] = $id;
            }
            $tickets[$id] = $ticket;
        }
    }
    
    // Count by status
    foreach ($tickets as $ticket) {
        if (isset($ticket['status']) && $ticket['status'] === 'fechado') {
            $closedCount++;
        } else {
            $openCount++;
        }
    }
}

// Load users
$usersFile = __DIR__ . '/../data/users.json';
$users = [];
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?? [];
}

// Add user names to tickets
foreach ($tickets as $id => &$ticket) {
    // Default to user ID
    $ticket['user_name'] = $ticket['user_id'];
    
    // Check if user exists in the users data
    if (isset($users[$ticket['user_id']])) {
        $ticket['user_name'] = $users[$ticket['user_id']]['name'];
    } else {
        foreach ($users as $user) {
            if (isset($user['id']) && $user['id'] === $ticket['user_id']) {
                $ticket['user_name'] = $user['name'];
                break;
            }
        }
    }
}

// Render tickets list
ob_start();
?>
<div class="list-group list-group-flush ticket-list">
    <?php if (empty($tickets)): ?>
        <div class="text-center py-4">
            <i class="fas fa-ticket-alt fa-2x text-muted mb-3"></i>
            <p class="text-muted mb-0">Nenhum ticket encontrado</p>
            <a href="/support/create" class="btn btn-primary btn-sm mt-2">
                <i class="fas fa-plus me-1"></i>
                Criar Primeiro Ticket
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($tickets as $id => $ticket): ?>
            <a href="#" 
               class="list-group-item list-group-item-action ticket-item <?= $ticket['status'] === 'fechado' ? 'history-ticket' : 'open-ticket' ?>" 
               data-ticket-id="<?= $ticket['id'] ?>"
               data-status="<?= $ticket['status'] ?>"
               data-user="<?= $ticket['user_id'] ?>">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1"><?= htmlspecialchars($ticket['subject']) ?></h6>
                    <small class="text-muted"><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></small>
                </div>
                <p class="mb-1 text-muted small">
                    <?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?>
                    <?= strlen($ticket['description']) > 100 ? '...' : '' ?>
                </p>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-<?= $ticket['status'] === 'aberto' ? 'success' : ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                        </span>
                        <span class="text-muted small ms-2">
                            <i class="fas fa-user me-1"></i>
                            <?= htmlspecialchars(isset($ticket['user_name']) ? $ticket['user_name'] : $ticket['user_id']) ?>
                        </span>
                    </div>
                    <span class="badge bg-<?= $ticket['priority'] === 'alta' || $ticket['priority'] === 'urgente' ? 'danger' : ($ticket['priority'] === 'media' ? 'warning' : 'info') ?>">
                        <?= ucfirst($ticket['priority']) ?>
                    </span>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<?php
$output = ob_get_clean();
echo $output;
