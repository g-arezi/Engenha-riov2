<?php
use App\Core\Auth;

$title = 'Ticket #' . $ticket['id'] . ' - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="support-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4 mb-1">Ticket #<?= substr($ticket['id'], 0, 8) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($ticket['subject']) ?></p>
        </div>
        <div class="d-flex gap-2">
            <?php if (Auth::hasPermission('support.manage')): ?>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" 
                            data-bs-toggle="dropdown">
                        <i class="fas fa-cog me-1"></i>
                        Ações
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#" onclick="updateTicketStatus('<?= $ticket['id'] ?>', 'em_andamento')">
                            <i class="fas fa-play me-2"></i>Em Andamento
                        </a></li>
                        <li><a class="dropdown-item" href="#" onclick="updateTicketStatus('<?= $ticket['id'] ?>', 'fechado')">
                            <i class="fas fa-check me-2"></i>Fechar Ticket
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" onclick="updateTicketStatus('<?= $ticket['id'] ?>', 'aberto')">
                            <i class="fas fa-undo me-2"></i>Reabrir
                        </a></li>
                    </ul>
                </div>
            <?php endif; ?>
            <a href="/support" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Ticket Details -->
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Detalhes do Ticket</h6>
                    <div class="ticket-meta">
                        <span class="badge bg-<?= $ticket['status'] === 'aberto' ? 'success' : ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary') ?> me-2">
                            <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                        </span>
                        <span class="badge bg-<?= $ticket['priority'] === 'alta' || $ticket['priority'] === 'urgente' ? 'danger' : ($ticket['priority'] === 'media' ? 'warning' : 'info') ?>">
                            <?= ucfirst($ticket['priority']) ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="ticket-description">
                    <h6 class="fw-bold mb-3"><?= htmlspecialchars($ticket['subject']) ?></h6>
                    <div class="description-content">
                        <?= nl2br(htmlspecialchars($ticket['description'])) ?>
                    </div>
                </div>
                
                <hr class="my-4">
                
                <div class="ticket-info">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Criado por</small>
                            <div class="d-flex align-items-center">
                                <img src="/assets/images/avatar-default.svg" alt="Avatar" class="rounded-circle me-2" width="24" height="24">
                                <span><?= htmlspecialchars(isset($creatorName) ? $creatorName : $ticket['user_id']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Data de criação</small>
                            <span><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Replies Section -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Respostas</h6>
            </div>
            <div class="card-body">
                <div id="replies-list" class="replies-list" data-ticket-id="<?= $ticket['id'] ?>">
                    <?php if (empty($replies)): ?>
                        <div class="text-center py-4 text-muted no-replies">
                            <i class="fas fa-comments fa-2x mb-3"></i>
                            <p>Nenhuma resposta ainda</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($replies as $reply): ?>
                            <div class="reply-item mb-3" data-reply-id="<?= $reply['id'] ?>">
                                <div class="d-flex">
                                    <img src="/assets/images/avatar-default.svg" alt="Avatar" class="rounded-circle me-3" width="32" height="32">
                                    <div class="flex-grow-1">
                                        <div class="reply-header d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong><?= htmlspecialchars(isset($reply['user_name']) && !empty($reply['user_name']) ? $reply['user_name'] : $reply['user_id']) ?></strong>
                                                <?php if ($reply['is_staff']): ?>
                                                    <span class="badge bg-primary ms-1">Equipe</span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></small>
                                        </div>
                                        <div class="reply-content">
                                            <?= nl2br(htmlspecialchars($reply['message'])) ?>
                                            
                                            <?php if (isset($reply['attachment']) && !empty($reply['attachment'])): ?>
                                            <div class="reply-attachment mt-2">
                                                <a href="<?= $reply['attachment'] ?>" target="_blank" class="d-block">
                                                    <img src="<?= $reply['attachment'] ?>" alt="Imagem anexada" class="img-fluid img-thumbnail" style="max-height: 200px;">
                                                </a>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fas fa-paperclip me-1"></i>
                                                    <?= isset($reply['attachment_name']) ? htmlspecialchars($reply['attachment_name']) : 'Imagem anexada' ?>
                                                </small>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Reply Form -->
                <?php if ($ticket['status'] !== 'fechado'): ?>
                    <div class="reply-form mt-4">
                        <form method="POST" action="/reply-ticket.php?id=<?= $ticket['id'] ?>" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="message" class="form-label">Sua Resposta</label>
                                <textarea class="form-control" id="message" name="message" rows="4" 
                                          placeholder="Digite sua resposta..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="attachment" class="form-label">Anexar Imagem (opcional)</label>
                                <input type="file" class="form-control" id="attachment" name="attachment" accept="image/*">
                                <div class="form-text">Formatos aceitos: JPG, JPEG, PNG, GIF - tamanho máximo 2MB</div>
                            </div>
                            
                            <?php if (Auth::hasPermission('support.manage')): ?>
                                <div class="mb-3">
                                    <label for="status" class="form-label">Alterar Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="">Manter status atual</option>
                                        <option value="aberto">Aberto</option>
                                        <option value="em_andamento">Em Andamento</option>
                                        <option value="fechado">Fechado</option>
                                    </select>
                                </div>
                            <?php endif; ?>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-reply me-1"></i>
                                Responder
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Este ticket foi fechado. Para continuar a conversa, abra um novo ticket.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Ticket Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">Informações</h6>
            </div>
            <div class="card-body">
                <div class="info-item mb-3">
                    <small class="text-muted d-block">Status</small>
                    <span class="badge bg-<?= $ticket['status'] === 'aberto' ? 'success' : ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary') ?>">
                        <?= ucfirst(str_replace('_', ' ', $ticket['status'])) ?>
                    </span>
                </div>
                
                <div class="info-item mb-3">
                    <small class="text-muted d-block">Prioridade</small>
                    <span class="badge bg-<?= $ticket['priority'] === 'alta' || $ticket['priority'] === 'urgente' ? 'danger' : ($ticket['priority'] === 'media' ? 'warning' : 'info') ?>">
                        <?= ucfirst($ticket['priority']) ?>
                    </span>
                </div>
                
                <div class="info-item mb-3">
                    <small class="text-muted d-block">Criado em</small>
                    <span><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
                </div>
                
                <div class="info-item">
                    <small class="text-muted d-block">Atualizado em</small>
                    <span><?= date('d/m/Y H:i', strtotime($ticket['updated_at'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Ações Rápidas</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="/support/create" class="btn btn-outline-primary">
                        <i class="fas fa-plus me-1"></i>
                        Novo Ticket
                    </a>
                    
                    <button class="btn btn-outline-info" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        Imprimir
                    </button>
                    
                    <a href="/support" class="btn btn-outline-secondary">
                        <i class="fas fa-list me-1"></i>
                        Todos os Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ticket-description {
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #007bff;
}

.description-content {
    font-size: 0.95rem;
    line-height: 1.6;
    color: #495057;
}

.reply-item {
    border-left: 3px solid #e9ecef;
    padding-left: 1rem;
    transition: background-color 0.5s ease;
}

/* Animação para novas respostas */
.reply-item.new-reply {
    animation: highlightNew 3s ease;
}

@keyframes highlightNew {
    0% { background-color: rgba(0, 123, 255, 0.1); }
    70% { background-color: rgba(0, 123, 255, 0.1); }
    100% { background-color: transparent; }
}

.reply-form {
    border-top: 2px solid #e9ecef;
    padding-top: 1.5rem;
}

.info-item {
    padding-bottom: 0.5rem;
    border-bottom: 1px solid #f1f3f4;
}

.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

.auto-refresh-status {
    font-size: 0.8rem;
    opacity: 0.7;
}

@media print {
    .card-header, .btn, .reply-form, .auto-refresh-status {
        display: none !important;
    }
}
</style>

<?php
// Adicionar scripts de atualização automática das respostas
echo '<script src="/assets/js/ticket-refresh.js?v=' . time() . '"></script>';
echo '<script src="/assets/js/ticket-init.js?v=' . time() . '"></script>';

$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
