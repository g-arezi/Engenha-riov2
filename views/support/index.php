<?php
use App\Core\Auth;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$title = 'Suporte - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="support-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Suporte</h1>
            <p class="text-muted mb-0">Gerencie tickets de suporte e histórico</p>
        </div>
    </div>
</div>

<!-- Tickets Status Tabs -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-6">
                        <div class="support-tab active" data-tab="open">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-clock fa-lg me-2"></i>
                                <div>
                                    <h6 class="mb-0">Tickets em Aberto (0)</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="support-tab" data-tab="history">
                            <div class="d-flex align-items-center justify-content-center">
                                <i class="fas fa-history fa-lg me-2"></i>
                                <div>
                                    <h6 class="mb-0">Histórico (0)</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Mensagens de Sucesso/Erro -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'ticket_created'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        Ticket criado com sucesso!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Support Content -->
<div class="row">
    <!-- Tickets List (Left Side) -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Meus Tickets</h6>
                <a href="/support/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Novo
                </a>
            </div>
            <div class="card-body p-0">
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
                    <div class="list-group list-group-flush">
                        <?php foreach ($tickets as $ticket): ?>
                            <a href="/support/view/<?= $ticket['id'] ?>" 
                               class="list-group-item list-group-item-action ticket-item" 
                               data-ticket-id="<?= $ticket['id'] ?>">>
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?= htmlspecialchars($ticket['subject']) ?></h6>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($ticket['created_at'])) ?></small>
                                </div>
                                <p class="mb-1 text-muted small">
                                    <?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?>
                                    <?= strlen($ticket['description']) > 100 ? '...' : '' ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-<?= $ticket['status'] === 'aberto' ? 'success' : ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                                    </span>
                                    <span class="badge bg-<?= $ticket['priority'] === 'alta' || $ticket['priority'] === 'urgente' ? 'danger' : ($ticket['priority'] === 'media' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($ticket['priority']) ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Main Content Area (Right Side) -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Selecione um ticket</h5>
                    <p class="text-muted">Escolha um ticket para ver os detalhes e responder</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
