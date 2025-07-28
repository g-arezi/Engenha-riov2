<?php
$title = 'Meu Perfil - Engenha Rio';
$showSidebar = true;
$showNavbar = true;
ob_start();
?>

<div class="profile-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Meu Perfil</h1>
            <p class="text-muted mb-0">Gerencie suas informações pessoais</p>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php
        $successMessage = match($_GET['success']) {
            'profile_updated' => 'Perfil atualizado com sucesso!',
            default => 'Operação realizada com sucesso!'
        };
        echo $successMessage;
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações Pessoais</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/profile">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Completo</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Perfil de Acesso</label>
                                <input type="text" class="form-control" value="<?= ucfirst($user['role']) ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <span class="form-control-plaintext">
                                    <span class="badge bg-<?= $user['status'] === 'ativo' ? 'success' : 'warning' ?>">
                                        <?= ucfirst($user['status']) ?>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6 class="text-muted mb-3">Alterar Senha</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Deixe em branco para manter a atual">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password_confirm" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="/dashboard" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Voltar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações da Conta</h5>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <img src="/assets/images/avatar-default.svg" alt="Avatar" class="rounded-circle mb-2" width="80" height="80">
                    <h6><?= htmlspecialchars($user['name']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                </div>
                
                <hr>
                
                <div class="row text-center">
                    <div class="col-12">
                        <small class="text-muted">Membro desde</small>
                        <div class="fw-bold"><?= date('d/m/Y', strtotime($user['created_at'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validação de confirmação de senha
    const passwordField = document.getElementById('password');
    const confirmField = document.getElementById('password_confirm');
    
    function validatePasswords() {
        if (passwordField.value && passwordField.value !== confirmField.value) {
            confirmField.setCustomValidity('As senhas não coincidem');
        } else {
            confirmField.setCustomValidity('');
        }
    }
    
    passwordField.addEventListener('input', validatePasswords);
    confirmField.addEventListener('input', validatePasswords);
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>
