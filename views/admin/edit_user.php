<?php
use App\Core\Auth;

$title = 'Editar Usuário - Administração';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="admin-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Editar Usuário</h1>
            <p class="text-muted mb-0">Modificar informações do usuário <?= htmlspecialchars($user['name']) ?></p>
        </div>
        <a href="/admin" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        $errorMessage = match($_GET['error']) {
            'email_exists' => 'Este e-mail já está sendo usado por outro usuário.',
            default => 'Erro ao atualizar usuário. Tente novamente.'
        };
        echo $errorMessage;
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-edit me-2"></i>Informações do Usuário
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/users/<?= $user['id'] ?>/edit">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="password" name="password">
                                <div class="form-text">Deixe em branco para manter a senha atual</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Tipo de Usuário *</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="administrador" <?= $user['role'] === 'administrador' ? 'selected' : '' ?>>
                                        Administrador
                                    </option>
                                    <option value="coordenador" <?= $user['role'] === 'coordenador' ? 'selected' : '' ?>>
                                        Coordenador
                                    </option>
                                    <option value="analista" <?= $user['role'] === 'analista' ? 'selected' : '' ?>>
                                        Analista
                                    </option>
                                    <option value="cliente" <?= $user['role'] === 'cliente' ? 'selected' : '' ?>>
                                        Cliente
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="ativo" <?= $user['status'] === 'ativo' ? 'selected' : '' ?>>
                                        Ativo
                                    </option>
                                    <option value="inativo" <?= $user['status'] === 'inativo' ? 'selected' : '' ?>>
                                        Inativo
                                    </option>
                                    <option value="pendente" <?= $user['status'] === 'pendente' ? 'selected' : '' ?>>
                                        Pendente
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Criado em</label>
                                <input type="text" class="form-control" 
                                       value="<?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <?php if ($user['id'] === Auth::id()): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Você está editando seu próprio perfil. Tenha cuidado ao alterar suas permissões.
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/admin" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title mb-0">Informações Adicionais</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">ID do Usuário</small>
                    <div><?= htmlspecialchars($user['id']) ?></div>
                </div>
                
                <?php if (isset($user['last_login']) && $user['last_login']): ?>
                <div class="mb-3">
                    <small class="text-muted">Último Login</small>
                    <div><?= date('d/m/Y H:i', strtotime($user['last_login'])) ?></div>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                    <small class="text-muted">Status Atual</small>
                    <div>
                        <?php
                        $statusClass = match($user['status']) {
                            'ativo' => 'success',
                            'inativo' => 'secondary',
                            'pendente' => 'warning',
                            default => 'secondary'
                        };
                        ?>
                        <span class="badge bg-<?= $statusClass ?>">
                            <?= ucfirst($user['status']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
