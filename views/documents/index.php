<?php
use App\Core\Auth;
use App\Core\Database;

$title = 'Documentos Internos - Engenha Rio';
$showSidebar = true;
$showNavbar = true;
$db = new Database();

// Filtros selecionados
$selectedCategory = $_GET['category'] ?? 'all';
$selectedProjectId = $_GET['project_id'] ?? null;

// A variável $documents já vem preenchida pelo controlador
// A variável $projects também já vem preenchida pelo controlador
// A variável $users também já vem preenchida pelo controlador

ob_start();
?>

<!-- Alert de sucesso do upload -->
<?php if (isset($_GET['success']) && $_GET['success'] === 'uploaded'): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <strong>Upload realizado com sucesso!</strong>
    <?php if (isset($_GET['file'])): ?>
        <br>Arquivo: <strong><?= htmlspecialchars($_GET['file']) ?></strong>
    <?php endif; ?>
    <?php if (isset($_GET['project'])): ?>
        <br>Projeto: <strong><?= htmlspecialchars($_GET['project']) ?></strong>
    <?php endif; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="documents-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Documentos Internos</h1>
            <p class="text-muted mb-0">Arquivos de orientação para a equipe interna</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-secondary btn-sm" onclick="manageCategoriesModal()">
                <i class="fas fa-cog me-1"></i>
                Gerenciar Categorias
            </button>
            <a href="/documents/upload" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>
                Enviar Documento
            </a>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="input-group">
            <span class="input-group-text">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" placeholder="Buscar documentos..." 
                   onkeyup="performAdvancedSearch(this.value, 'documents')">
        </div>
    </div>
    <div class="col-md-3">
        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle w-100" type="button" 
                    data-bs-toggle="dropdown">
                <i class="fas fa-filter me-2"></i>
                Todas as categorias
                <i class="fas fa-chevron-down ms-auto"></i>
            </button>
            <ul class="dropdown-menu w-100">
                <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['category' => 'all'])) ?>">Todas as categorias</a></li>
                <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['category' => 'procedimento'])) ?>">Procedimento</a></li>
                <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['category' => 'template'])) ?>">Template</a></li>
                <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['category' => 'manual'])) ?>">Manual</a></li>
            </ul>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle w-100" type="button" 
                    data-bs-toggle="dropdown">
                <i class="fas fa-project-diagram me-2"></i>
                <?php 
                $selectedProject = $_GET['project_id'] ?? null;
                if ($selectedProject && isset($projects[$selectedProject])) {
                    echo htmlspecialchars(substr($projects[$selectedProject]['name'], 0, 15)) . (strlen($projects[$selectedProject]['name']) > 15 ? '...' : '');
                } else {
                    echo 'Filtrar por Projeto';
                }
                ?>
                <i class="fas fa-chevron-down ms-auto"></i>
            </button>
            <ul class="dropdown-menu w-100">
                <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['project_id' => null])) ?>">Todos os Projetos</a></li>
                <li><hr class="dropdown-divider"></li>
                <?php 
                // Mostrar apenas projetos vinculados ao usuário
                $userProjects = [];
                $currentUser = Auth::user();
                $userId = Auth::id();
                
                if (in_array($currentUser['role'], ['administrador', 'analista', 'coordenador'])) {
                    // Admin/analista/coordenador - projetos que estão vinculados a ele
                    foreach ($projects as $project) {
                        if ($project['analyst_id'] == $userId || $project['created_by'] == $userId) {
                            $userProjects[] = $project;
                        }
                    }
                } else {
                    // Cliente - projetos onde é cliente
                    foreach ($projects as $project) {
                        if ($project['client_id'] == $userId) {
                            $userProjects[] = $project;
                        }
                    }
                }
                
                // Mostrar lista de projetos para filtrar
                foreach ($userProjects as $project): 
                ?>
                    <li><a class="dropdown-item" href="?<?= http_build_query(array_merge($_GET, ['project_id' => $project['id']])) ?>">
                        <?= htmlspecialchars($project['name']) ?>
                    </a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Documents Grid -->
<div class="row">
    <?php if (!empty($documents)): ?>
        <?php foreach ($documents as $doc): ?>
            <?php 
            // Filtrar por categoria se selecionada
            if ($selectedCategory !== 'all' && 
                strtolower($doc['category'] ?? '') !== strtolower($selectedCategory)) {
                continue;
            }
            
            // O filtro por projeto já é feito no controller, mas podemos adicionar uma verificação extra
            if ($selectedProjectId && $doc['project_id'] != $selectedProjectId) {
                continue;
            }
            
            // Determinar ícone do arquivo
            $fileExtension = pathinfo($doc['original_name'] ?? '', PATHINFO_EXTENSION);
            $iconClass = 'fas fa-file';
            $iconColor = 'text-primary';
            
            switch (strtolower($fileExtension)) {
                case 'pdf':
                    $iconClass = 'fas fa-file-pdf';
                    $iconColor = 'text-danger';
                    break;
                case 'doc':
                case 'docx':
                    $iconClass = 'fas fa-file-word';
                    $iconColor = 'text-primary';
                    break;
                case 'xls':
                case 'xlsx':
                    $iconClass = 'fas fa-file-excel';
                    $iconColor = 'text-success';
                    break;
                case 'jpg':
                case 'jpeg':
                case 'png':
                    $iconClass = 'fas fa-file-image';
                    $iconColor = 'text-info';
                    break;
            }
            ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card document-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="document-icon">
                                <i class="<?= $iconClass ?> fa-2x <?= $iconColor ?>"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-link btn-sm text-muted" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Editar</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteDocument('<?= htmlspecialchars($doc['id']) ?>')"><i class="fas fa-trash me-2"></i>Excluir</a></li>
                                </ul>
                            </div>
                        </div>
                        
                        <h6 class="card-title"><?= htmlspecialchars($doc['name']) ?></h6>
                        <div class="document-meta flex-grow-1">
                            <span class="badge bg-primary mb-2"><?= htmlspecialchars($doc['category'] ?? 'Geral') ?></span>
                            <?php if (!empty($doc['description'])): ?>
                                <small class="text-muted d-block"><?= htmlspecialchars($doc['description']) ?></small>
                            <?php endif; ?>
                            <small class="text-muted">
                                Enviado em <?= date('d/m/Y', strtotime($doc['uploaded_at'] ?? $doc['created_at'])) ?>
                            </small>
                            <small class="text-muted d-block">
                                Por <?php 
                                    $uploaderId = $doc['uploaded_by'] ?? 'N/A';
                                    if (isset($users[$uploaderId])) {
                                        echo htmlspecialchars($users[$uploaderId]['name']);
                                    } else {
                                        echo htmlspecialchars($uploaderId);
                                    }
                                ?>
                            </small>
                            <?php if (!empty($doc['project_id'])): ?>
                                <small class="text-info d-block">
                                    <i class="fas fa-project-diagram me-1"></i>
                                    Projeto: <?php 
                                        $projectId = $doc['project_id'];
                                        if (isset($projects[$projectId])) {
                                            echo htmlspecialchars($projects[$projectId]['name']);
                                        } else {
                                            echo htmlspecialchars($projectId);
                                        }
                                    ?>
                                </small>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-3">
                            <button class="btn btn-outline-primary btn-sm w-100" 
                                   onclick="downloadDocument('<?= htmlspecialchars($doc['id']) ?>')">
                                <i class="fas fa-download me-1"></i>
                                Baixar Documento
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Empty state quando não há documentos -->
        <div class="col-12">
            <div class="empty-state text-center py-5">
                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Nenhum documento encontrado</h5>
                <p class="text-muted">Faça upload do primeiro documento para começar.</p>
                <a href="/documents/upload" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    Enviar Documento
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
