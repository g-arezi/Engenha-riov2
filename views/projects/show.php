<?php
use App\Core\Auth;

$title = 'Visualizar Projeto - Engenha Rio';
$showSidebar = true;
$showNavbar = true;
ob_start();
?>

<!-- Alertas de sucesso ou erro -->
<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <strong>Sucesso!</strong> <?= htmlspecialchars($_GET['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Erro:</strong> <?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="project-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1"><?= htmlspecialchars($project['name']) ?></h1>
            <p class="text-muted mb-0"><?= htmlspecialchars($project['description']) ?></p>
        </div>
        <div>
            <?php if (Auth::hasPermission('projects.edit')): ?>
            <a href="/projects/<?= $project['id'] ?>/edit" class="btn btn-outline-primary">
                <i class="fas fa-edit me-1"></i>
                Editar
            </a>
            <?php endif; ?>
            <a href="/projects" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Detalhes do Projeto</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nome:</strong>
                        <p><?= htmlspecialchars($project['name']) ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Status:</strong>
                        <p>
                            <span class="badge bg-<?= 
                                match($project['status']) {
                                    'ativo' => 'success',
                                    'pausado' => 'warning',
                                    'concluido' => 'primary',
                                    default => 'secondary'
                                }
                            ?>">
                                <?= ucfirst($project['status']) ?>
                            </span>
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <strong>Data de Início:</strong>
                        <p><?= isset($project['start_date']) ? date('d/m/Y', strtotime($project['start_date'])) : 'Não definida' ?></p>
                    </div>
                    <div class="col-md-6">
                        <strong>Data de Término:</strong>
                        <p><?= isset($project['end_date']) && $project['end_date'] ? date('d/m/Y', strtotime($project['end_date'])) : 'Não definida' ?></p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <strong>Descrição:</strong>
                        <p><?= nl2br(htmlspecialchars($project['description'])) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Documentos do Projeto -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Documentos do Projeto</h5>
                <?php if (Auth::hasPermission('documents.upload')): ?>
                <a href="/documents/project/upload?project_id=<?= $project['id'] ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-upload me-1"></i>
                    Enviar Documento
                </a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($projectDocuments)): ?>
                <div class="text-center py-4">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Nenhum documento encontrado para este projeto</p>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Tipo</th>
                                <th>Status</th>
                                <th>Enviado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projectDocuments as $doc): ?>
                            <tr>
                                <td><?= htmlspecialchars($doc['name']) ?></td>
                                <td><?= htmlspecialchars($doc['document_type'] ?? $doc['type'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge bg-<?= 
                                        match($doc['status']) {
                                            'aprovado' => 'success',
                                            'pendente' => 'warning',
                                            'rejeitado' => 'danger',
                                            default => 'secondary'
                                        }
                                    ?>">
                                        <?= ucfirst($doc['status']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($doc['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/documents/project/<?= $doc['id'] ?>/download" class="btn btn-outline-primary btn-sm" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <?php if (Auth::hasPermission('documents.approve')): ?>
                                        <button class="btn btn-outline-success btn-sm" onclick="approveDocument('<?= $doc['id'] ?>')" title="Aprovar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button class="btn btn-outline-danger btn-sm" onclick="rejectDocument('<?= $doc['id'] ?>')" title="Rejeitar">
                                            <i class="fas fa-times"></i>
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
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações Adicionais</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Criado por:</strong>
                    <p><?= htmlspecialchars($project['created_by'] ?? 'Sistema') ?></p>
                </div>
                
                <div class="mb-3">
                    <strong>Criado em:</strong>
                    <p><?= date('d/m/Y H:i', strtotime($project['created_at'])) ?></p>
                </div>
                
                <?php if (isset($project['updated_at'])): ?>
                <div class="mb-3">
                    <strong>Última atualização:</strong>
                    <p><?= date('d/m/Y H:i', strtotime($project['updated_at'])) ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <strong>Cliente Atribuído:</strong>
                    <p><?= htmlspecialchars($project['client_id'] ?? 'Não atribuído') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>
