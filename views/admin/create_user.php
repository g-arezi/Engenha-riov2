<?php
use App\Core\Auth;

$title = 'Novo Usuário - Administração';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="admin-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Novo Usuário</h1>
            <p class="text-muted mb-0">Adicionar novo usuário ao sistema</p>
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
            'email_exists' => 'Este e-mail já está cadastrado no sistema.',
            default => 'Erro ao criar usuário. Tente novamente.'
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
                    <i class="fas fa-user-plus me-2"></i>Informações do Usuário
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/users/create">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Completo *</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail *</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="form-text">Mínimo 6 caracteres</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="role" class="form-label">Tipo de Usuário *</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Selecione...</option>
                                    <option value="administrador" <?= ($_POST['role'] ?? '') === 'administrador' ? 'selected' : '' ?>>
                                        Administrador
                                    </option>
                                    <option value="coordenador" <?= ($_POST['role'] ?? '') === 'coordenador' ? 'selected' : '' ?>>
                                        Coordenador
                                    </option>
                                    <option value="analista" <?= ($_POST['role'] ?? '') === 'analista' ? 'selected' : '' ?>>
                                        Analista
                                    </option>
                                    <option value="cliente" <?= ($_POST['role'] ?? '') === 'cliente' ? 'selected' : '' ?>>
                                        Cliente
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Descrição dos Perfis:</label>
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong>Administrador:</strong>
                                                    <small class="text-muted d-block">Acesso total ao sistema, gerencia usuários e configurações</small>
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Coordenador:</strong>
                                                    <small class="text-muted d-block">Gerencia projetos e coordena equipes</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-2">
                                                    <strong>Analista:</strong>
                                                    <small class="text-muted d-block">Analisa e aprova documentos</small>
                                                </div>
                                                <div class="mb-2">
                                                    <strong>Cliente:</strong>
                                                    <small class="text-muted d-block">Visualiza projetos e envia documentos</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/admin" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Criar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
