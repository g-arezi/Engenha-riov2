<?php
use App\Core\Auth;
use App\Services\NotificationService;

$user = Auth::user();
$notificationService = new NotificationService();
$notifications = $notificationService->getUserNotifications(Auth::id());
$unreadCount = count(array_filter($notifications, fn($n) => !($n['is_read'] ?? false)));
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
    <div class="container-fluid">
        <div class="d-flex align-items-center ms-auto">
            <!-- Notificações -->
            <div class="dropdown me-3">
                <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <?php if ($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $unreadCount ?>
                        </span>
                    <?php endif; ?>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="width: 300px;">
                    <li class="dropdown-header">Notificações</li>
                    <?php if (empty($notifications)): ?>
                        <li><span class="dropdown-item text-muted">Nenhuma notificação</span></li>
                    <?php else: ?>
                        <?php foreach (array_slice($notifications, 0, 5) as $notification): ?>
                            <li>
                                <a class="dropdown-item <?= !($notification['is_read'] ?? false) ? 'bg-light' : '' ?>" 
                                   href="#" 
                                   onclick="markNotificationAsRead('<?= $notification['id'] ?>', '<?= $notification['link'] ?? '#' ?>')">
                                    <div class="d-flex justify-content-between">
                                        <div class="text-truncate">
                                            <small class="text-muted"><?= date('d/m H:i', strtotime($notification['created_at'])) ?></small>
                                            <div><?= htmlspecialchars($notification['message']) ?></div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-center" href="/notifications">Ver todas</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <!-- Usuário -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-container rounded-circle me-2" style="width: 32px; height: 32px; overflow: hidden;">
                        <img src="<?= !empty($user['avatar']) ? '/uploads/avatars/' . $user['avatar'] : '/assets/images/avatar-default.svg' ?>" 
                             alt="Avatar" class="rounded-circle" width="32" height="32" style="object-fit: cover;">
                    </div>
                    <span><?= htmlspecialchars($user['name']) ?></span>
                    <i class="fas fa-chevron-down ms-2"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="/profile">
                        <i class="fas fa-user me-2"></i>Meu Perfil
                    </a></li>
                    <?php if (Auth::hasPermission('admin.view')): ?>
                    <li><a class="dropdown-item" href="/admin">
                        <i class="fas fa-cog me-2"></i>Administração
                    </a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="/logout">
                        <i class="fas fa-sign-out-alt me-2"></i>Sair
                    </a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
