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
                    <div class="stage-icon <?= $project['workflow_stage'] >= 1 ? ($project['workflow_stage'] == 1 ? 'current' : 'completed') : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 1 ? 'check' : ($project['workflow_stage'] == 1 ? 'file-alt' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Documentos</div>
                </div>
                
                <!-- Projeto -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] > 2 ? 'completed' : ($project['workflow_stage'] == 2 ? 'current' : 'disabled') ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 2 ? 'check' : ($project['workflow_stage'] == 2 ? 'sync-alt' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Projeto</div>
                </div>
                
                <!-- Produção -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] > 3 ? 'completed' : ($project['workflow_stage'] == 3 ? 'current' : 'disabled') ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 3 ? 'check' : ($project['workflow_stage'] == 3 ? 'sync-alt' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Produção</div>
                </div>
                
                <!-- Buildup -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] > 4 ? 'completed' : ($project['workflow_stage'] == 4 ? 'current' : 'disabled') ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 4 ? 'check' : ($project['workflow_stage'] == 4 ? 'sync-alt' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Buildup</div>
                </div>
                
                <!-- Aprovado -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] == 5 ? 'current' : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] == 5 ? 'check' : 'lock' ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Aprovado</div>
                </div>
            </div>
            
            <!-- Hidden input to store current stage for JavaScript -->
            <input type="hidden" id="workflow-stage" value="<?= $project['workflow_stage'] ?? 1 ?>">
            
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
                    <?php if (!empty($documents)): ?>
                        <?php foreach ($documents as $document): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf text-danger me-2"></i>
                                    <div>
                                        <div class="fw-medium"><?= htmlspecialchars($document['name']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($document['original_name']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="file-format-badge"><?= strtolower(pathinfo($document['original_name'], PATHINFO_EXTENSION)) ?></span>
                            </td>
                            <td>
                                <span class="file-size"><?= number_format($document['size'] / 1024 / 1024, 1) ?>MB - <?= date('H:i', strtotime($document['created_at'])) ?></span>
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
                                    <button class="btn btn-outline-danger btn-sm" title="Excluir" onclick="deleteDocument('<?= $document['id'] ?>')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" title="Informações" onclick="showDocumentInfo('<?= $document['id'] ?>')">
                                        <i class="fas fa-info"></i>
                                    </button>
                                    <button class="btn btn-outline-secondary btn-sm" title="Download" onclick="downloadDocument('<?= $document['id'] ?>')">
                                        <i class="fas fa-download"></i>
                                    </button>
                                </div>
                            </td>
                            <td>
                                <?php if (Auth::hasPermission('documents.approve')): ?>
                                <div class="status-controls">
                                    <select class="form-select form-select-sm status-select" data-document-id="<?= $document['id'] ?>" onchange="updateDocumentStatus(this)">
                                        <option value="em_analise" <?= $document['status'] === 'em_analise' ? 'selected' : '' ?>>Em análise</option>
                                        <option value="aprovado" <?= $document['status'] === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                                        <option value="rejeitado" <?= $document['status'] === 'rejeitado' ? 'selected' : '' ?>>Rejeitado</option>
                                    </select>
                                </div>
                                <?php else: ?>
                                <span class="badge bg-<?= $document['status'] === 'aprovado' ? 'success' : ($document['status'] === 'rejeitado' ? 'danger' : 'primary') ?>">
                                    <?= ucfirst(str_replace('_', ' ', $document['status'])) ?>
                                </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-file-upload fa-2x mb-2"></i>
                                <p>Nenhum documento foi enviado ainda</p>
                                <small>Faça upload dos documentos necessários para continuar</small>
                            </td>
                        </tr>
                    <?php endif; ?>
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

<!-- Modal de Informações do Documento -->
<div class="modal fade" id="documentInfoModal" tabindex="-1" aria-labelledby="documentInfoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentInfoModalLabel">
                    <i class="fas fa-file-alt me-2"></i>
                    Informações do Documento
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="documentInfoContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                    <p class="mt-2">Carregando informações...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" id="downloadFromModal" style="display: none;">
                    <i class="fas fa-download me-1"></i>
                    Download
                </button>
            </div>
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

/* Status Select Styling */
.status-controls {
    min-width: 120px;
}

.status-select {
    border: 1px solid #dee2e6;
    font-size: 0.875rem;
    font-weight: 500;
    min-width: 110px;
}

.status-select.status-em-analise {
    background-color: #fff3cd;
    border-color: #ffc107;
    color: #856404;
}

.status-select.status-aprovado {
    background-color: #d4edda;
    border-color: #28a745;
    color: #155724;
}

.status-select.status-rejeitado {
    background-color: #f8d7da;
    border-color: #dc3545;
    color: #721c24;
}

.status-select:disabled {
    opacity: 0.6;
    pointer-events: none;
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

function updateDocumentStatus(selectElement) {
    const documentId = selectElement.getAttribute('data-document-id');
    const newStatus = selectElement.value;
    const originalValue = selectElement.getAttribute('data-original-value') || selectElement.options[0].value;
    
    let additionalData = {};
    let confirmMessage = '';
    
    // Preparar dados específicos baseados no status
    switch (newStatus) {
        case 'aprovado':
            confirmMessage = 'Confirma a aprovação deste documento?';
            const comments = prompt('Comentários (opcional):');
            if (comments !== null) {
                additionalData.comments = comments;
            } else {
                // Se cancelou o prompt, volta ao valor original
                selectElement.value = originalValue;
                return;
            }
            break;
            
        case 'rejeitado':
            const rejectionReason = prompt('Motivo da rejeição (obrigatório):');
            if (rejectionReason && rejectionReason.trim()) {
                additionalData.rejection_reason = rejectionReason.trim();
                confirmMessage = 'Confirma a rejeição deste documento?';
            } else {
                alert('O motivo da rejeição é obrigatório');
                selectElement.value = originalValue;
                return;
            }
            break;
            
        case 'em_analise':
            confirmMessage = 'Confirma colocar este documento em análise?';
            const analysisComments = prompt('Comentários (opcional):');
            if (analysisComments !== null) {
                additionalData.comments = analysisComments;
            } else {
                selectElement.value = originalValue;
                return;
            }
            break;
    }
    
    // Confirmar a ação
    if (!confirm(confirmMessage)) {
        selectElement.value = originalValue;
        return;
    }
    
    // Desabilitar o select durante a requisição
    selectElement.disabled = true;
    
    // Fazer a requisição
    fetch('/documents/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            document_id: documentId,
            status: newStatus,
            ...additionalData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status do documento atualizado com sucesso!', 'success');
            // Atualizar o valor original para o novo status
            selectElement.setAttribute('data-original-value', newStatus);
            
            // Atualizar a aparência visual baseada no novo status
            updateStatusVisualFeedback(selectElement, newStatus);
        } else {
            showAlert(data.message || 'Erro ao atualizar status do documento', 'error');
            selectElement.value = originalValue;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Erro de conexão', 'error');
        selectElement.value = originalValue;
    })
    .finally(() => {
        selectElement.disabled = false;
    });
}

function updateStatusVisualFeedback(selectElement, status) {
    // Remover classes de status anteriores
    selectElement.classList.remove('status-em-analise', 'status-aprovado', 'status-rejeitado');
    
    // Adicionar classe baseada no novo status
    switch (status) {
        case 'em_analise':
            selectElement.classList.add('status-em-analise');
            break;
        case 'aprovado':
            selectElement.classList.add('status-aprovado');
            break;
        case 'rejeitado':
            selectElement.classList.add('status-rejeitado');
            break;
    }
}

// Inicializar valores originais quando a página carrega
document.addEventListener('DOMContentLoaded', function() {
    const statusSelects = document.querySelectorAll('.status-select');
    statusSelects.forEach(select => {
        select.setAttribute('data-original-value', select.value);
        updateStatusVisualFeedback(select, select.value);
    });
});

// Funções auxiliares para ações dos documentos
function deleteDocument(documentId) {
    if (confirm('Tem certeza que deseja excluir este documento? Esta ação não pode ser desfeita.')) {
        fetch(`/documents/project/${documentId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Documento excluído com sucesso!', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showAlert(data.message || 'Erro ao excluir documento', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Erro de conexão', 'error');
        });
    }
}

function showDocumentInfo(documentId) {
    // Abrir modal
    const modal = new bootstrap.Modal(document.getElementById('documentInfoModal'));
    modal.show();
    
    // Resetar conteúdo para loading
    document.getElementById('documentInfoContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Carregando...</span>
            </div>
            <p class="mt-2">Carregando informações...</p>
        </div>
    `;
    
    // Buscar informações do documento
    fetch(`/documents/project/${documentId}/info`, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => {
            console.log('Response status:', response.status);
            console.log('Response headers:', response.headers);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response:', text);
            
            try {
                const data = JSON.parse(text.trim());
                console.log('Parsed data:', data);
                
                if (data.success && data.document) {
                    displayDocumentInfo(data.document);
                } else {
                    throw new Error(data.message || 'Dados inválidos recebidos');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text:', text);
                document.getElementById('documentInfoContent').innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erro ao processar resposta do servidor.
                        <br><small>Detalhes: ${parseError.message}</small>
                    </div>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('documentInfoContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Erro ao carregar informações do documento: ${error.message}
                </div>
            `;
        });
}

function displayDocumentInfo(document) {
    const content = `
        <div class="row">
            <div class="col-md-8">
                <h6 class="text-primary mb-3">
                    <i class="fas fa-file-alt me-2"></i>
                    ${document.name}
                </h6>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Arquivo original:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.original_name}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Tamanho:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.size_formatted}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Tipo de arquivo:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.mime_type}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Enviado por:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.uploader}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Data de envio:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.created_at_formatted}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Projeto:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.project_name}
                    </div>
                </div>
                
                ${document.description ? `
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Descrição:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${document.description}
                    </div>
                </div>
                ` : ''}
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Status</h6>
                        <span class="badge fs-6 bg-${getStatusColor(document.status)}">
                            ${document.status_label}
                        </span>
                        
                        ${document.approved_by ? `
                        <hr>
                        <small class="text-muted">
                            <strong>Aprovado por:</strong><br>
                            ${document.approved_by}<br>
                            ${document.approved_at ? new Date(document.approved_at).toLocaleString('pt-BR') : ''}
                        </small>
                        ` : ''}
                        
                        ${document.rejected_by ? `
                        <hr>
                        <small class="text-muted">
                            <strong>Rejeitado por:</strong><br>
                            ${document.rejected_by}<br>
                            ${document.rejected_at ? new Date(document.rejected_at).toLocaleString('pt-BR') : ''}
                        </small>
                        ${document.rejection_reason ? `<br><br><strong>Motivo:</strong><br>${document.rejection_reason}` : ''}
                        ` : ''}
                        
                        ${document.comments ? `
                        <hr>
                        <small class="text-muted">
                            <strong>Comentários:</strong><br>
                            ${document.comments}
                        </small>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('documentInfoContent').innerHTML = content;
    
    // Mostrar botão de download
    const downloadBtn = document.getElementById('downloadFromModal');
    downloadBtn.style.display = 'inline-block';
    downloadBtn.onclick = () => downloadDocument(document.id);
}

function getStatusColor(status) {
    switch (status) {
        case 'aprovado': return 'success';
        case 'rejeitado': return 'danger';
        case 'em_analise': return 'warning';
        default: return 'secondary';
    }
}

function downloadDocument(documentId) {
    window.open(`/documents/project/${documentId}/download`, '_blank');
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
$scripts = '<script src="/assets/js/workflow-stages.js"></script>';
include __DIR__ . '/../layouts/app.php';
?>
