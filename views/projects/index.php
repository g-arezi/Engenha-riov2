<?php
use App\Core\Auth;

$title = 'Projetos - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="projects-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Projetos</h1>
            <p class="text-muted mb-0">Gerencie todos os projetos da empresa</p>
        </div>
        <?php if (Auth::hasPermission('projects.create')): ?>
        <a href="/projects/create" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>
            Novo Projeto
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" placeholder="Buscar projetos...">
        </div>
    </div>
    <div class="col-md-4">
        <select class="form-select">
            <option value="">Todos os status</option>
            <option value="pendente">Pendente</option>
            <option value="ativo">Ativo</option>
            <option value="finalizado">Finalizado</option>
            <option value="cancelado">Cancelado</option>
        </select>
    </div>
</div>

<!-- Projects Table -->
<div class="card">
    <div class="card-body">
        <?php if (empty($projects)): ?>
        <div class="empty-state text-center py-5">
            <i class="fas fa-folder fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">Nenhum projeto encontrado</h5>
            <p class="text-muted">Comece criando seu primeiro projeto</p>
            <?php if (Auth::hasPermission('projects.create')): ?>
            <a href="/projects/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Criar Projeto
            </a>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Cliente</th>
                        <th>Analista</th>
                        <th>Status</th>
                        <th>Prioridade</th>
                        <th>Prazo</th>
                        <th>Criado em</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                            <div>
                                <strong><?= htmlspecialchars($project['name']) ?></strong>
                                <?php if (!empty($project['description'])): ?>
                                <br><small class="text-muted"><?= htmlspecialchars(substr($project['description'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($project['client_name'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($project['analyst_name'] ?? 'N/A') ?></td>
                        <td>
                            <span class="badge bg-<?= $project['status'] === 'ativo' ? 'success' : ($project['status'] === 'pendente' ? 'warning' : ($project['status'] === 'finalizado' ? 'info' : 'secondary')) ?>">
                                <?= ucfirst($project['status']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-<?= $project['priority'] === 'alta' ? 'danger' : ($project['priority'] === 'media' ? 'warning' : 'info') ?>">
                                <?= ucfirst($project['priority']) ?>
                            </span>
                        </td>
                        <td>
                            <?= isset($project['deadline']) ? date('d/m/Y', strtotime($project['deadline'])) : 'N/A' ?>
                        </td>
                        <td><?= date('d/m/Y', strtotime($project['created_at'])) ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary btn-sm" onclick="viewProject('<?= $project['id'] ?>')" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="/documents/project/<?= $project['id'] ?>" class="btn btn-outline-info btn-sm" title="Documentos">
                                    <i class="fas fa-file-alt"></i>
                                </a>
                                <?php if (Auth::hasPermission('projects.edit')): ?>
                                <button class="btn btn-outline-secondary btn-sm" onclick="editProject('<?= $project['id'] ?>')" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php endif; ?>
                                <?php if (Auth::hasPermission('projects.delete')): ?>
                                <button class="btn btn-outline-danger btn-sm" onclick="deleteProject('<?= $project['id'] ?>')" title="Excluir">
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
        <?php endif; ?>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
