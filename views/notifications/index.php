<?php
use App\Core\Auth;
use App\Services\NotificationService;

$title = $title ?? 'Notificações - Engenha Rio';
$showSidebar = $showSidebar ?? true;
$showNavbar = $showNavbar ?? true;
ob_start();
?>

<div class="notifications-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Notificações</h1>
            <p class="text-muted mb-0">Acompanhe as atualizações do sistema</p>
        </div>
        <?php if (!empty($notifications) && count(array_filter($notifications, fn($n) => !($n['is_read'] ?? false))) > 0): ?>
        <button class="btn btn-outline-primary" onclick="markAllNotificationsAsRead()">
            <i class="fas fa-check-double me-1"></i>
            Marcar todas como lidas
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-bell me-2"></i>Notificações
                </h4>
            </div>
            <div class="card-body">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhuma notificação encontrada</h5>
                        <p class="text-muted">Você não possui notificações no momento.</p>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="list-group-item <?= !($notification['is_read'] ?? false) ? 'bg-light' : '' ?>">
                                <div class="d-flex w-100 justify-content-between">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-1">
                                            <?php
                                            $iconClass = match($notification['type']) {
                                                'document_uploaded' => 'fas fa-upload text-info',
                                                'document_approved' => 'fas fa-check-circle text-success',
                                                'document_rejected' => 'fas fa-times-circle text-danger',
                                                'project_created' => 'fas fa-project-diagram text-primary',
                                                'project_updated' => 'fas fa-edit text-warning',
                                                default => 'fas fa-info-circle text-secondary'
                                            };
                                            ?>
                                            <i class="<?= $iconClass ?> me-2"></i>
                                            <h6 class="mb-0 <?= !($notification['is_read'] ?? false) ? 'fw-bold' : '' ?>">
                                                <?= htmlspecialchars($notification['title']) ?>
                                            </h6>
                                            <?php if (!($notification['is_read'] ?? false)): ?>
                                                <span class="badge bg-primary ms-2">Nova</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars($notification['message']) ?></p>
                                        <small class="text-muted">
                                            <i class="fas fa-clock me-1"></i>
                                            <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="ms-3">
                                        <?php if (!($notification['is_read'] ?? false)): ?>
                                            <button class="btn btn-sm btn-outline-primary mark-as-read" data-id="<?= $notification['id'] ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if (isset($notification['data']['project_name'])): ?>
                                    <div class="mt-2">
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-project-diagram me-1"></i>
                                            <?= htmlspecialchars($notification['data']['project_name']) ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button class="btn btn-outline-secondary" id="markAllAsRead">
                            <i class="fas fa-check-double me-2"></i>Marcar todas como lidas
                        </button>
                        <small class="text-muted">
                            Total: <?= count($notifications) ?> notificações
                        </small>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Marcar notificação individual como lida
    document.querySelectorAll('.mark-as-read').forEach(button => {
        button.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            markAsRead(notificationId, this);
        });
    });
    
    // Marcar todas como lidas
    document.getElementById('markAllAsRead')?.addEventListener('click', function() {
        markAllAsRead();
    });
    
    function markAsRead(notificationId, button) {
        fetch('/notifications/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const listItem = button.closest('.list-group-item');
                listItem.classList.remove('bg-light');
                listItem.querySelector('.fw-bold')?.classList.remove('fw-bold');
                listItem.querySelector('.badge.bg-primary')?.remove();
                button.remove();
            }
        })
        .catch(error => {
            console.error('Erro ao marcar notificação como lida:', error);
        });
    }
    
    function markAllNotificationsAsRead() {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Erro ao marcar todas as notificações:', error);
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erro ao marcar todas as notificações como lidas:', error);
        });
    }
});
</script>
