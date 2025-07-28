<?php
$title = 'Login - Engenha Rio';
ob_start();
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-header text-center mb-4">
            <h1 class="brand-logo">ENGENHARIO</h1>
            <p class="text-muted">Sistema de Gestão de Projetos</p>
        </div>
        
        <?php if (isset($error)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="/login" class="login-form">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-envelope"></i>
                    </span>
                    <input type="email" class="form-control" id="email" name="email" required 
                           placeholder="Digite seu e-mail">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" required 
                           placeholder="Digite sua senha">
                </div>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">
                    Lembrar-me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>
                Entrar
            </button>
        </form>
        
        <div class="login-footer text-center mt-4">
            <small class="text-muted">
                © 2025 Engenha Rio. Todos os direitos reservados.
            </small>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
