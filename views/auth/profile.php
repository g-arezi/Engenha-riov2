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

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        $errorMessage = match($_GET['error']) {
            'avatar_type' => 'Tipo de arquivo inválido! Use apenas formatos JPG ou PNG.',
            'avatar_size' => 'Arquivo muito grande! O tamanho máximo permitido é 2MB.',
            'avatar_upload' => 'Erro ao fazer upload da imagem. Tente novamente.',
            default => 'Ocorreu um erro ao processar sua solicitação.'
        };
        echo $errorMessage;
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
                <form method="POST" action="/profile" enctype="multipart/form-data">
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
                    
                    <div class="mb-3">
                        <label for="bio" class="form-label">Descrição / Biografia</label>
                        <textarea class="form-control" id="bio" name="bio" rows="3" 
                                 placeholder="Escreva uma breve descrição sobre você..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        <div class="form-text">Esta descrição será visível para outros usuários do sistema</div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="avatar" class="form-label">Foto de Perfil</label>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <div class="avatar-preview rounded-circle overflow-hidden" style="width: 100px; height: 100px; background-color: #e9ecef;">
                                    <img src="<?= !empty($user['avatar']) ? '/uploads/avatars/' . $user['avatar'] : '/assets/images/avatar-default.svg' ?>" 
                                         alt="Avatar" class="img-fluid" id="avatarPreview">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                <div class="form-text">Formatos aceitos: JPG, PNG. Tamanho máximo: 2MB.</div>
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
                    <div class="avatar-container mx-auto mb-2" style="width: 100px; height: 100px; overflow: hidden; border-radius: 50%; box-shadow: 0 3px 6px rgba(0,0,0,0.16);">
                        <img src="<?= !empty($user['avatar']) ? '/uploads/avatars/' . $user['avatar'] : '/assets/images/avatar-default.svg' ?>" 
                             alt="Avatar" class="img-fluid" width="100%" height="100%" style="object-fit: cover;">
                    </div>
                    <h6><?= htmlspecialchars($user['name']) ?></h6>
                    <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                </div>
                
                <?php if (!empty($user['bio'])): ?>
                <div class="card bg-light mb-3">
                    <div class="card-body py-2">
                        <small class="text-muted fst-italic">
                            "<?= htmlspecialchars($user['bio']) ?>"
                        </small>
                    </div>
                </div>
                <?php endif; ?>
                
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
    
    // Preview de imagem ao selecionar avatar
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('avatarPreview');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                // Verificar tamanho (2MB máximo)
                if (this.files[0].size > 2 * 1024 * 1024) {
                    alert('Arquivo muito grande! O tamanho máximo permitido é 2MB.');
                    this.value = '';
                    return;
                }
                
                // Verificar tipo
                const fileType = this.files[0].type;
                if (fileType !== 'image/jpeg' && fileType !== 'image/png') {
                    alert('Formato inválido! Use apenas arquivos JPG ou PNG.');
                    this.value = '';
                    return;
                }
                
                // Mostrar preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>
