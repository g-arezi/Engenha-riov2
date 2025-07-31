<?php
use App\Core\Auth;

$title = 'Dashboard - Engenha Rio';
$showSidebar = true;
$showNavbar = true;
$user = Auth::user();
ob_start();
?>

<div class="dashboard-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Dashboard</h1>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-title">Total de Projetos</div>
                        <div class="stats-number"><?= $stats['total_projects'] ?></div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-folder fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-title">Projetos Ativos</div>
                        <div class="stats-number"><?= $stats['active_projects'] ?></div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-warning text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-title">Docs Pendentes</div>
                        <div class="stats-number"><?= $stats['pending_docs'] ?></div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6">
        <div class="card bg-purple text-white stats-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="stats-title">Finalizados</div>
                        <div class="stats-number"><?= $stats['finished_projects'] ?></div>
                    </div>
                    <div class="stats-icon">
                        <i class="fas fa-file-alt fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Projects -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Projetos Recentes</h5>
                <?php if (Auth::hasPermission('projects.create')): ?>
                <a href="/projects/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>
                    Novo Projeto
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($recentProjects)): ?>
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
                                <th>Nº Orçamento</th>
                                <th>Status</th>
                                <th>Prioridade</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentProjects as $project): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($project['name']) ?></strong>
                                </td>
                                <td><?= htmlspecialchars($project['client_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($project['budget_number'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= $project['status'] === 'ativo' ? 'success' : ($project['status'] === 'pendente' ? 'warning' : 'secondary') ?>">
                                        <?= ucfirst($project['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $project['priority'] === 'alta' ? 'danger' : ($project['priority'] === 'media' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($project['priority']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y', strtotime($project['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/projects/<?= $project['id'] ?>" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if (Auth::hasPermission('projects.edit')): ?>
                                        <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
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
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
