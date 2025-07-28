<?php
use App\Core\Auth;

$title = 'Documentos do Projeto - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="project-workflow-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Projetos</h1>
            <div class="d-flex align-items-center">
                <span class="text-muted">Detalhes do projeto</span>
                <div class="ms-3">
                    <div class="user-info d-flex align-items-center">
                        <img src="/assets/images/avatar-default.svg" alt="Avatar" class="rounded-circle me-2" width="24" height="24">
                        <span class="fw-medium"><?= htmlspecialchars(Auth::user()['name']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Project Information Card -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <div class="project-info">
                    <label class="text-muted small">INÍCIO - DATA</label>
                    <div class="fw-medium"><?= date('d/m/Y', strtotime($project['created_at'])) ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="project-info">
                    <label class="text-muted small">ENTREGA - DATA</label>
                    <div class="fw-medium"><?= isset($project['deadline']) ? date('d/m/Y', strtotime($project['deadline'])) : 'Não definido' ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="project-info">
                    <label class="text-muted small">Histórico:</label>
                    <div class="project-description">
                        <?= htmlspecialchars($project['description']) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="project-info">
                    <label class="text-muted small">Orçamento:</label>
                    <div class="fw-medium"><?= $project['budget'] ?? 'Não informado' ?></div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="project-info">
                    <label class="text-muted small">Analista:</label>
                    <div class="fw-medium"><?= htmlspecialchars($project['analyst_name'] ?? 'Não atribuído') ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stage Information -->
<div class="alert alert-info mb-4">
    <div class="d-flex align-items-center">
        <i class="fas fa-info-circle me-2"></i>
        <div>
            <strong>Etapa bloqueada até aprovação da etapa documentos e projeto</strong><br>
            <small>As informações têm caráter orientativo para o cliente e podem ser alteradas exclusivamente pelo administrador.</small>
        </div>
    </div>
</div>

<!-- Workflow Stages -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">ETAPAS</h5>
    </div>
    <div class="card-body">
        <div class="workflow-stages">
            <div class="stage-row d-flex justify-content-between align-items-center mb-3">
                <!-- Documentos -->
                <div class="stage-item text-center">
                    <div class="stage-icon completed">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stage-label mt-2">Documentos</div>
                </div>
                
                <!-- Projeto -->
                <div class="stage-item text-center">
                    <div class="stage-icon completed">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="stage-label mt-2">Projeto</div>
                </div>
                
                <!-- Produção -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] >= 3 ? 'completed' : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] >= 3 ? 'check' : 'lock' ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Produção</div>
                </div>
                
                <!-- Buildup -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] >= 4 ? 'completed' : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] >= 4 ? 'check' : 'lock' ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Buildup</div>
                </div>
                
                <!-- Aprovado -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] >= 5 ? 'completed' : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] >= 5 ? 'check' : 'lock' ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Aprovado</div>
                </div>
            </div>
            
            <?php if (Auth::hasPermission('projects.manage_workflow')): ?>
            <!-- Workflow Controls for Admins, Coordinators, and Analysts -->
            <div class="workflow-controls mt-4 p-3 bg-light rounded">
                <h6 class="mb-3">Controle do Workflow</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Etapa Atual</label>
                        <select class="form-select" id="workflow-stage" onchange="updateWorkflowStage('<?= $project['id'] ?>', this.value)">
                            <option value="1" <?= ($project['workflow_stage'] ?? 1) == 1 ? 'selected' : '' ?>>Documentos</option>
                            <option value="2" <?= ($project['workflow_stage'] ?? 1) == 2 ? 'selected' : '' ?>>Projeto</option>
                            <option value="3" <?= ($project['workflow_stage'] ?? 1) == 3 ? 'selected' : '' ?>>Produção</option>
                            <option value="4" <?= ($project['workflow_stage'] ?? 1) == 4 ? 'selected' : '' ?>>Buildup</option>
                            <option value="5" <?= ($project['workflow_stage'] ?? 1) == 5 ? 'selected' : '' ?>>Aprovado</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status do Projeto</label>
                        <select class="form-select" id="project-status" onchange="updateProjectStatus('<?= $project['id'] ?>', this.value)">
                            <option value="pendente" <?= $project['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="ativo" <?= $project['status'] == 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="pausado" <?= $project['status'] == 'pausado' ? 'selected' : '' ?>>Pausado</option>
                            <option value="concluido" <?= $project['status'] == 'concluido' ? 'selected' : '' ?>>Concluído</option>
                            <option value="cancelado" <?= $project['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <div class="row">
                        <?php if (($project['workflow_stage'] ?? 1) < 5): ?>
                        <div class="col-auto">
                            <button class="btn btn-success btn-sm" onclick="advanceWorkflow('<?= $project['id'] ?>')">
                                <i class="fas fa-arrow-right me-1"></i>
                                Avançar Etapa
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php if (($project['workflow_stage'] ?? 1) == 5): ?>
                        <div class="col-auto">
                            <button class="btn btn-primary btn-sm" onclick="finalizeProject('<?= $project['id'] ?>')">
                                <i class="fas fa-check-circle me-1"></i>
                                Finalizar Projeto
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php if (($project['workflow_stage'] ?? 1) > 1): ?>
                        <div class="col-auto">
                            <button class="btn btn-warning btn-sm" onclick="revertWorkflow('<?= $project['id'] ?>')">
                                <i class="fas fa-arrow-left me-1"></i>
                                Voltar Etapa
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Documents Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Documentos</th>
                        <th>Arquivo</th>
                        <th>Tamanho</th>
                        <th>Upload</th>
                        <th>Ações</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Documento Responsável Legal -->
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                <div>
                                    <div class="fw-medium">Identidade do Responsável Legal</div>
                                    <small class="text-muted">BASES_AND_POSTOS.dwg</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="file-format-badge">dwg</span>
                        </td>
                        <td>
                            <span class="file-size">10MB/1GB - 17:45</span>
                        </td>
                        <td>
                            <div class="upload-progress">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                                <small class="text-success">Concluído</small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-danger btn-sm" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" title="Informações">
                                    <i class="fas fa-info"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary">Em análise</span>
                        </td>
                    </tr>
                    
                    <!-- Mais documentos seguem o mesmo padrão -->
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                <div>
                                    <div class="fw-medium">Contrato de Locação se Pde não Socio</div>
                                    <small class="text-muted">BASES_AND_POSTOS.dwg</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="file-format-badge">dwg</span>
                        </td>
                        <td>
                            <span class="file-size">10MB/1GB - 17:45</span>
                        </td>
                        <td>
                            <div class="upload-progress">
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: 100%"></div>
                                </div>
                                <small class="text-success">Concluído</small>
                            </div>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-danger btn-sm" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <button class="btn btn-outline-primary btn-sm" title="Informações">
                                    <i class="fas fa-info"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" title="Download">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary">Em análise</span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Upload Area -->
<div class="upload-area mt-4">
    <div class="text-center">
        <div class="upload-instructions mb-3">
            <p class="text-muted mb-2">
                <strong>Após aprovado, não é possível alterar pelo cliente</strong><br>
                Caso necessário, deverá ser solicitado através do chat.
            </p>
            <p class="text-muted mb-2">
                <strong>Muda ao clicar na etapa.</strong>
            </p>
            <p class="text-muted">
                <strong>Sem status até que seja anexado algo.</strong><br>
                Ao clicar, o arquivo é baixado.<br>
                Arraste o arquivo para esta área ou clique para buscar no seu computador.
            </p>
        </div>
        
        <div class="upload-status mb-3">
            <p class="text-success">
                Se estiver verde, está aprovado pelo cliente e apto a próxima etapa.<br>
                Ao clicar, Abre a aba debaixo.
            </p>
        </div>
        
        <div class="upload-warning">
            <p class="text-warning">
                Se estiver amarelo, falta algum documento a ser enviado ou aprovado.<br>
                Ao clicar, Abre a aba debaixo.
            </p>
        </div>
    </div>
</div>

<style>
.project-info label {
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.workflow-stages {
    position: relative;
}

.stage-item {
    flex: 1;
    position: relative;
}

.stage-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 1.2rem;
    color: white;
}

.stage-icon.completed {
    background-color: #28a745;
}

.stage-icon.in-progress {
    background-color: #ffc107;
    color: #000;
}

.stage-icon.disabled {
    background-color: #6c757d;
}

.stage-label {
    font-size: 0.875rem;
    font-weight: 500;
    color: #495057;
}

.file-format-badge {
    background-color: #e9ecef;
    color: #495057;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.file-size {
    font-size: 0.875rem;
    color: #6c757d;
}

.upload-progress .progress {
    width: 100px;
}

.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 2rem;
    background-color: #f8f9fa;
}

.upload-instructions {
    max-width: 600px;
    margin: 0 auto;
}

.user-info {
    font-size: 0.875rem;
}
</style>

<script>
// Controle do Workflow
function updateWorkflowStage(projectId, stage) {
    fetch('/document-workflow/update-stage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            project_id: projectId,
            stage: parseInt(stage)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Etapa atualizada com sucesso!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Erro ao atualizar etapa', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erro de conexão', 'error');
    });
}

function updateProjectStatus(projectId, status) {
    fetch('/document-workflow/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            project_id: projectId,
            status: status
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status atualizado com sucesso!', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showAlert(data.message || 'Erro ao atualizar status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erro de conexão', 'error');
    });
}

function advanceWorkflow(projectId) {
    if (confirm('Tem certeza que deseja avançar o projeto para a próxima etapa?')) {
        fetch('/document-workflow/advance', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Projeto avançado para a próxima etapa!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Erro ao avançar etapa', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão', 'error');
        });
    }
}

function revertWorkflow(projectId) {
    if (confirm('Tem certeza que deseja retroceder o projeto para a etapa anterior?')) {
        fetch('/document-workflow/revert', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Projeto retrocedido para a etapa anterior!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Erro ao retroceder etapa', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão', 'error');
        });
    }
}

function finalizeProject(projectId) {
    if (confirm('Tem certeza que deseja finalizar este projeto? Esta ação não pode ser desfeita.')) {
        fetch('/document-workflow/finalize', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                project_id: projectId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Projeto finalizado com sucesso!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Erro ao finalizar projeto', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão', 'error');
        });
    }
}

// Funções de aprovação de documentos
function approveDocument(documentId) {
    if (confirm('Tem certeza que deseja aprovar este documento?')) {
        fetch('/document-workflow/approve-document-ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                document_id: documentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Documento aprovado com sucesso!', 'success');
                location.reload();
            } else {
                showAlert(data.message || 'Erro ao aprovar documento', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão', 'error');
        });
    }
}

function rejectDocument(documentId) {
    const comment = prompt('Motivo da rejeição (opcional):');
    if (comment !== null) {
        fetch('/document-workflow/reject-document-ajax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                document_id: documentId,
                comment: comment
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Documento rejeitado!', 'success');
                location.reload();
            } else {
                showAlert(data.message || 'Erro ao rejeitar documento', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão', 'error');
        });
    }
}

function showAlert(message, type) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;
    
    // Add alert to the top of the page
    const container = document.querySelector('.container-fluid');
    container.insertAdjacentHTML('afterbegin', alertHtml);
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
