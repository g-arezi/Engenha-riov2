<?php
use App\Core\Auth;
$user = Auth::user();
?>

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
                <a class="nav-link <?= $_SERVER['REQUEST_URI'] === '/dashboard' ? 'active' : '' ?>" href="/dashboard">
                    <i class="fas fa-th-large me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <?php if (Auth::hasPermission('projects.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/projects') === 0 ? 'active' : '' ?>" href="/projects">
                    <i class="fas fa-folder me-2"></i>
                    Projetos
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('documents.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/documents') === 0 ? 'active' : '' ?>" href="/documents">
                    <i class="fas fa-file-alt me-2"></i>
                    Documentos
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (Auth::hasPermission('support.view')): ?>
            <li class="nav-item">
                <a class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/support') === 0 ? 'active' : '' ?>" href="/support">
                    <i class="fas fa-headset me-2"></i>
                    Suporte
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>
