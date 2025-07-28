<?php
use App\Core\Auth;
use App\Core\Database;

$title = 'Administração - Engenha Rio';
$showSidebar = true;
$showNavbar = true;
$db = new Database();

// Buscar dados para as abas
$activeUsers = $db->findAll('users', ['status' => 'ativo']);
$pendingUsers = $db->findAll('users', ['status' => 'pendente']);
$services = $db->findAll('services');
$documents = $db->findAll('document_templates');

ob_start();
?>

<div class="admin-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Administração</h1>
            <p class="text-muted mb-0">Gerencie configurações e usuários do sistema</p>
        </div>
    </div>
</div>

<?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php
        $successMessage = match($_GET['success']) {
            'user_created' => 'Usuário criado com sucesso!',
            'user_updated' => 'Usuário atualizado com sucesso!',
            'user_deleted' => 'Usuário excluído com sucesso!',
            'permissions_updated' => 'Permissões atualizadas com sucesso!',
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
            'cannot_delete_self' => 'Você não pode excluir seu próprio usuário.',
            'user_not_found' => 'Usuário não encontrado.',
            default => 'Erro ao realizar operação. Tente novamente.'
        };
        echo $errorMessage;
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'usuarios_ativos' ? 'active' : '' ?>" 
                id="usuarios-ativos-tab" data-bs-toggle="tab" data-bs-target="#usuarios-ativos" 
                type="button" role="tab">
            Usuários Ativos
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'usuarios_pendentes' ? 'active' : '' ?>" 
                id="usuarios-pendentes-tab" data-bs-toggle="tab" data-bs-target="#usuarios-pendentes" 
                type="button" role="tab">
            Usuários Pendentes
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'servicos' ? 'active' : '' ?>" 
                id="servicos-tab" data-bs-toggle="tab" data-bs-target="#servicos" 
                type="button" role="tab">
            Serviços
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'documentos' ? 'active' : '' ?>" 
                id="documentos-tab" data-bs-toggle="tab" data-bs-target="#documentos" 
                type="button" role="tab">
            Documentos
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'templates' ? 'active' : '' ?>" 
                id="templates-tab" data-bs-toggle="tab" data-bs-target="#templates" 
                type="button" role="tab">
            Templates
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link <?= $activeTab === 'perfis_acesso' ? 'active' : '' ?>" 
                id="perfis-acesso-tab" data-bs-toggle="tab" data-bs-target="#perfis-acesso" 
                type="button" role="tab">
            Perfis de Acesso
        </button>
    </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="adminTabsContent">
    <!-- Usuários Ativos -->
    <div class="tab-pane fade <?= $activeTab === 'usuarios_ativos' ? 'show active' : '' ?>" 
         id="usuarios-ativos" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Usuários Ativos</h5>
                <a href="/admin/users/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Novo Usuário
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Última Atividade</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activeUsers as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td>
                                    <?php
                                    $roleBadgeClass = match($user['role']) {
                                        'administrador' => 'danger',
                                        'coordenador' => 'primary',
                                        'analista' => 'info',
                                        'cliente' => 'secondary',
                                        default => 'secondary'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $roleBadgeClass ?>">
                                        <?= ucfirst($user['role']) ?>
                                    </span>
                                </td>
                                <td><?= isset($user['last_login']) ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Nunca' ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-outline-primary btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($user['id'] !== Auth::id()): ?>
                                        <button class="btn btn-outline-danger btn-sm delete-user" 
                                                data-user-id="<?= $user['id'] ?>" 
                                                data-user-name="<?= htmlspecialchars($user['name']) ?>" 
                                                title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Usuários Pendentes -->
    <div class="tab-pane fade <?= $activeTab === 'usuarios_pendentes' ? 'show active' : '' ?>" 
         id="usuarios-pendentes" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Usuários Pendentes de Aprovação</h5>
            </div>
            <div class="card-body">
                <?php if (empty($pendingUsers)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-user-clock fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Nenhum usuário pendente</h5>
                        <p class="text-muted">Todos os usuários foram aprovados ou rejeitados.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Perfil Solicitado</th>
                                    <th>Data de Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingUsers as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td>
                                        <?php
                                        $roleBadgeClass = match($user['role']) {
                                            'administrador' => 'danger',
                                            'coordenador' => 'primary',
                                            'analista' => 'info',
                                            'cliente' => 'secondary',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?= $roleBadgeClass ?>">
                                            <?= ucfirst($user['role']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-success btn-sm approve-user" 
                                                    data-user-id="<?= $user['id'] ?>" 
                                                    data-user-name="<?= htmlspecialchars($user['name']) ?>" 
                                                    title="Aprovar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="btn btn-danger btn-sm reject-user" 
                                                    data-user-id="<?= $user['id'] ?>" 
                                                    data-user-name="<?= htmlspecialchars($user['name']) ?>" 
                                                    title="Rejeitar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <a href="/admin/users/<?= $user['id'] ?>/edit" class="btn btn-outline-primary btn-sm" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Gerenciar Documentos -->
    <div class="tab-pane fade <?= $activeTab === 'documentos' ? 'show active' : '' ?>" 
         id="documentos" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gerenciar Documentos</h5>
                <a href="/admin/documents/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Adicionar Documento
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Código</th>
                                <th>Descrição</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Identidade do responsável legal</td>
                                <td>responsavel_legal</td>
                                <td>—</td>
                                <td><span class="badge bg-success">Ativo</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>Contrato social</td>
                                <td>contrato_social</td>
                                <td>—</td>
                                <td><span class="badge bg-success">Ativo</span></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Gerenciar Templates -->
    <div class="tab-pane fade <?= $activeTab === 'templates' ? 'show active' : '' ?>" 
         id="templates" role="tabpanel">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Templates de Documentos</h5>
                <a href="/admin/templates" class="btn btn-primary btn-sm">
                    <i class="fas fa-cog me-1"></i>
                    Gerenciar Templates
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-file-alt fa-3x text-primary"></i>
                                </div>
                                <h6>Templates Disponíveis</h6>
                                <h3 class="text-primary"><?= count($documents) ?></h3>
                                <p class="text-muted small mb-0">Total de templates configurados</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <div class="mb-3">
                                    <i class="fas fa-cogs fa-3x text-success"></i>
                                </div>
                                <h6>Sistema Ativo</h6>
                                <h3 class="text-success">
                                    <i class="fas fa-check-circle"></i>
                                </h3>
                                <p class="text-muted small mb-0">Templates funcionando corretamente</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mt-4">
                    <h6>Funcionalidades do Sistema de Templates:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success me-2"></i>Criar novos templates personalizados</li>
                        <li><i class="fas fa-check text-success me-2"></i>Editar templates existentes</li>
                        <li><i class="fas fa-check text-success me-2"></i>Organizar por categorias de engenharia</li>
                        <li><i class="fas fa-check text-success me-2"></i>Ativar/desativar templates conforme necessário</li>
                        <li><i class="fas fa-check text-success me-2"></i>Associação automática por tipo de projeto</li>
                    </ul>
                </div>
                
                <div class="mt-4">
                    <div class="d-flex gap-2">
                        <a href="/admin/templates" class="btn btn-primary">
                            <i class="fas fa-list me-1"></i>
                            Ver Todos os Templates
                        </a>
                        <a href="/admin/templates/create" class="btn btn-success">
                            <i class="fas fa-plus me-1"></i>
                            Criar Novo Template
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Perfis de Acesso -->
    <div class="tab-pane fade <?= $activeTab === 'perfis_acesso' ? 'show active' : '' ?>" 
         id="perfis-acesso" role="tabpanel">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Configurar Permissões por Perfil</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/permissions">
                    <div class="row">
                        <!-- Administrador -->
                        <div class="col-md-6 mb-4">
                            <div class="permission-group">
                                <h6 class="fw-bold mb-3 text-danger">
                                    <i class="fas fa-user-shield me-2"></i>Administrador
                                </h6>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Dashboard</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Projetos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Documentos Internos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Suporte</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Administração</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Meu Perfil</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Gerenciar Usuários</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Configurações do Sistema</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Coordenador -->
                        <div class="col-md-6 mb-4">
                            <div class="permission-group">
                                <h6 class="fw-bold mb-3 text-primary">
                                    <i class="fas fa-users-cog me-2"></i>Coordenador
                                </h6>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Dashboard</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Projetos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Documentos Internos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Suporte</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Administração</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Meu Perfil</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Gerenciar Equipes</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Aprovar Documentos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Analista -->
                        <div class="col-md-6 mb-4">
                            <div class="permission-group">
                                <h6 class="fw-bold mb-3 text-info">
                                    <i class="fas fa-clipboard-check me-2"></i>Analista
                                </h6>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Dashboard</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Projetos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Documentos Internos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Suporte</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Administração</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Meu Perfil</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Analisar Documentos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Emitir Pareceres</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cliente -->
                        <div class="col-md-6 mb-4">
                            <div class="permission-group">
                                <h6 class="fw-bold mb-3 text-secondary">
                                    <i class="fas fa-user me-2"></i>Cliente
                                </h6>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Dashboard</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Projetos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Documentos Internos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox">
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Suporte</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Administração</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" disabled>
                                    </div>
                                </div>
                                
                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Meu Perfil</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Visualizar Projetos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>

                                <div class="permission-item d-flex justify-content-between align-items-center mb-2">
                                    <span>Enviar Documentos</span>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" checked>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button type="submit" class="btn btn-dark">
                            Salvar Permissões
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete User
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            if (confirm(`Tem certeza que deseja excluir o usuário "${userName}"? Esta ação não pode ser desfeita.`)) {
                deleteUser(userId);
            }
        });
    });
    
    // Approve User
    document.querySelectorAll('.approve-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            if (confirm(`Aprovar o usuário "${userName}"?`)) {
                approveUser(userId, this);
            }
        });
    });
    
    // Reject User
    document.querySelectorAll('.reject-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.dataset.userId;
            const userName = this.dataset.userName;
            
            if (confirm(`Rejeitar o usuário "${userName}"?`)) {
                rejectUser(userId, this);
            }
        });
    });
    
    function deleteUser(userId) {
        fetch(`/admin/users/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Delete response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Delete response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Delete parsed data:', data);
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro ao excluir usuário: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                // Se não é JSON, assumir que é uma resposta de redirecionamento bem-sucedida
                location.reload();
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir usuário: ' + error.message);
        });
    }
    
    function approveUser(userId, button) {
        fetch(`/admin/users/${userId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                if (data.success) {
                    const row = button.closest('tr');
                    row.style.backgroundColor = '#d4edda';
                    row.style.transition = 'background-color 0.3s';
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Erro ao aprovar usuário: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                alert('Erro na resposta do servidor: ' + text);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao aprovar usuário. Tente novamente.');
        });
    }
    
    function rejectUser(userId, button) {
        fetch(`/admin/users/${userId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Response text:', text);
            try {
                const data = JSON.parse(text);
                console.log('Parsed data:', data);
                if (data.success) {
                    const row = button.closest('tr');
                    row.style.backgroundColor = '#f8d7da';
                    row.style.transition = 'background-color 0.3s';
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    alert('Erro ao rejeitar usuário: ' + (data.message || 'Erro desconhecido'));
                }
            } catch (e) {
                console.error('JSON parse error:', e);
                alert('Erro na resposta do servidor: ' + text);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao rejeitar usuário. Tente novamente.');
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
