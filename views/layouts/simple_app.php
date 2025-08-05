<?php
/**
 * Simplified app.php layout that doesn't use Auth class
 * Used for ticket-view.php
 */

$user = [
    'name' => $_SESSION['user_name'] ?? $_SESSION['user_id'] ?? 'Usuário',
    'role' => $_SESSION['user_role'] ?? 'cliente',
    'avatar' => $_SESSION['user_avatar'] ?? ''
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Engenha Rio' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="/assets/css/app.css" rel="stylesheet">
    <link href="/assets/css/chat-widget.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        <!-- Simplified sidebar -->
        <?php if (isset($showSidebar) && $showSidebar): ?>
        <div class="sidebar bg-dark" id="sidebar">
            <div class="sidebar-header">
                <div class="brand d-flex align-items-center px-3 py-3">
                    <img src="/assets/images/engenhario-logo.png" alt="Engenha Rio" class="me-2" style="height: 32px;">
                </div>
                
                <div class="user-profile d-flex align-items-center p-3 text-white">
                    <div class="avatar-container me-2" style="width: 40px; height: 40px; overflow: hidden; border-radius: 50%;">
                        <img src="<?= !empty($user['avatar']) ? '/uploads/avatars/' . $user['avatar'] : '/assets/images/avatar-default.svg' ?>" 
                            alt="Avatar" class="img-fluid" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                        <small class="text-light-50"><?= ucfirst($user['role']) ?></small>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="/dashboard">
                            <i class="fas fa-th-large me-2"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/projects">
                            <i class="fas fa-folder me-2"></i>
                            Projetos
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="/documents">
                            <i class="fas fa-file-alt me-2"></i>
                            Documentos
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link active" href="/support">
                            <i class="fas fa-headset me-2"></i>
                            Suporte
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php endif; ?>
        
        <div class="flex-grow-1">
            <!-- Simplified navbar -->
            <?php if (isset($showNavbar) && $showNavbar): ?>
            <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
                <div class="container-fluid">
                    <button class="navbar-toggler sidebar-toggler me-2" type="button">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user me-1"></i> <?= htmlspecialchars($user['name']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="/profile"><i class="fas fa-user-cog me-2"></i>Meu Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="/logout"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
            <?php endif; ?>
            
            <main class="<?= isset($showSidebar) && $showSidebar ? 'main-content' : '' ?>">
                <?= $content ?? '' ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/app.js"></script>
    <?= $scripts ?? '' ?>
</body>
</html>
