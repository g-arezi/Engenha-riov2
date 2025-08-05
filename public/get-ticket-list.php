<?php
// Endpoint para atualizar a lista de tickets via AJAX
require_once __DIR__ . '/../autoload.php';

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

use App\Core\Auth;
use App\Core\Database;

// Verificar autenticação
if (!Auth::check()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Carregar tickets diretamente do JSON
header('Content-Type: text/html; charset=utf-8');

$db = new Database();
$tickets = [];

// Obter todos os tickets
$allTickets = $db->getAllData('support_tickets');
$openCount = 0;
$closedCount = 0;

// Filtrar tickets para usuários regulares
if (!Auth::hasPermission('admin.view') && !Auth::hasPermission('support.manage')) {
    $userTickets = [];
    foreach ($allTickets as $id => $ticket) {
        if (isset($ticket['user_id']) && $ticket['user_id'] === Auth::id()) {
            $userTickets[$id] = $ticket;
        }
    }
    $tickets = $userTickets;
} else {
    $tickets = $allTickets;
}

// Contar tickets por status
foreach ($tickets as $ticket) {
    if (isset($ticket['status']) && $ticket['status'] === 'fechado') {
        $closedCount++;
    } else {
        $openCount++;
    }
}

// Obter dados de usuários
$users = $db->getAllData('users');

// Adicionar nomes de usuários aos tickets
foreach ($tickets as $id => &$ticket) {
    $ticket['user_name'] = $ticket['user_id'];
    
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

// Renderizar apenas a lista de tickets
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
            <a href="/view-ticket.php?id=<?= $ticket['id'] ?>" 
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
