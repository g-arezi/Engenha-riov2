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

<!-- Checklist de Documentos Obrigatórios -->
<?php if (!empty($documentChecklist['required_documents']) || !empty($documentChecklist['optional_documents'])): ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-clipboard-list me-2"></i>
            Checklist de Documentos
        </h5>
        <div class="d-flex align-items-center">
            <div class="progress me-3" style="width: 150px; height: 8px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: <?= $documentChecklist['completion_percentage'] ?>%" 
                     aria-valuenow="<?= $documentChecklist['completion_percentage'] ?>" 
                     aria-valuemin="0" aria-valuemax="100">
                </div>
            </div>
            <small class="text-muted">
                <?= $documentChecklist['completed_required'] ?>/<?= $documentChecklist['total_required'] ?> obrigatórios
            </small>
        </div>
    </div>
    <div class="card-body">
        <?php if (!empty($documentChecklist['required_documents'])): ?>
        <h6 class="text-danger mb-3">
            <i class="fas fa-exclamation-circle me-1"></i>
            Documentos Obrigatórios
        </h6>
        <div class="row">
            <?php foreach ($documentChecklist['required_documents'] as $doc): ?>
            <div class="col-md-6 mb-3">
                <div class="card border-<?= $doc['uploaded'] ? ($doc['status'] === 'aprovado' ? 'success' : ($doc['status'] === 'rejeitado' ? 'danger' : 'warning')) : 'danger' ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">
                                <?php if ($doc['uploaded'] && $doc['status'] === 'aprovado'): ?>
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                <?php elseif ($doc['uploaded']): ?>
                                    <i class="fas fa-clock text-warning me-1"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger me-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($doc['name']) ?>
                            </h6>
                            <span class="badge bg-<?= $doc['uploaded'] ? ($doc['status'] === 'aprovado' ? 'success' : ($doc['status'] === 'rejeitado' ? 'danger' : 'warning')) : 'danger' ?>">
                                <?php if ($doc['uploaded']): ?>
                                    <?= ucfirst($doc['status']) ?>
                                <?php else: ?>
                                    Pendente
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <p class="card-text small text-muted mb-2">
                            <?= htmlspecialchars($doc['description']) ?>
                        </p>
                        
                        <div class="small text-muted mb-2">
                            <strong>Formato:</strong> <?= htmlspecialchars($doc['format']) ?> | 
                            <strong>Tamanho máx:</strong> <?= htmlspecialchars($doc['max_size']) ?>
                        </div>
                        
                        <?php if ($doc['uploaded']): ?>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-upload me-1"></i>
                                Enviado em: <?= date('d/m/Y H:i', strtotime($doc['upload_date'])) ?>
                            </div>
                            <?php if ($doc['file_info']): ?>
                            <div class="d-flex gap-1">
                                <a href="/documents/project/<?= $doc['file_info']['id'] ?>/download" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <?php if (Auth::hasPermission('documents.upload')): ?>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="uploadNewVersionWorkflow('<?= $doc['name'] ?>', <?= $doc['index'] ?>, '<?= $project['id'] ?>')">
                                    <i class="fas fa-upload"></i> Nova versão
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (Auth::hasPermission('documents.upload')): ?>
                            <button class="btn btn-primary btn-sm" onclick="uploadDocumentWorkflow('<?= $doc['name'] ?>', <?= $doc['index'] ?>, '<?= $project['id'] ?>')">
                                <i class="fas fa-upload me-1"></i>
                                Enviar Documento
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($documentChecklist['optional_documents'])): ?>
        <h6 class="text-info mb-3 mt-4">
            <i class="fas fa-info-circle me-1"></i>
            Documentos Opcionais
        </h6>
        <div class="row">
            <?php foreach ($documentChecklist['optional_documents'] as $doc): ?>
            <div class="col-md-6 mb-3">
                <div class="card border-<?= $doc['uploaded'] ? ($doc['status'] === 'aprovado' ? 'success' : ($doc['status'] === 'rejeitado' ? 'danger' : 'warning')) : 'light' ?>">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title mb-0">
                                <?php if ($doc['uploaded'] && $doc['status'] === 'aprovado'): ?>
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                <?php elseif ($doc['uploaded']): ?>
                                    <i class="fas fa-clock text-warning me-1"></i>
                                <?php else: ?>
                                    <i class="fas fa-minus-circle text-muted me-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($doc['name']) ?>
                                <small class="text-muted">(opcional)</small>
                            </h6>
                            <?php if ($doc['uploaded']): ?>
                            <span class="badge bg-<?= $doc['status'] === 'aprovado' ? 'success' : ($doc['status'] === 'rejeitado' ? 'danger' : 'warning') ?>">
                                <?= ucfirst($doc['status']) ?>
                            </span>
                            <?php endif; ?>
                        </div>
                        
                        <p class="card-text small text-muted mb-2">
                            <?= htmlspecialchars($doc['description']) ?>
                        </p>
                        
                        <div class="small text-muted mb-2">
                            <strong>Formato:</strong> <?= htmlspecialchars($doc['format']) ?> | 
                            <strong>Tamanho máx:</strong> <?= htmlspecialchars($doc['max_size']) ?>
                        </div>
                        
                        <?php if ($doc['uploaded']): ?>
                            <div class="small text-muted mb-2">
                                <i class="fas fa-upload me-1"></i>
                                Enviado em: <?= date('d/m/Y H:i', strtotime($doc['upload_date'])) ?>
                            </div>
                            <?php if ($doc['file_info']): ?>
                            <div class="d-flex gap-1">
                                <a href="/documents/project/<?= $doc['file_info']['id'] ?>/download" 
                                   class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                                <?php if (Auth::hasPermission('documents.upload')): ?>
                                <button class="btn btn-outline-secondary btn-sm" 
                                        onclick="uploadNewVersionWorkflow('<?= $doc['name'] ?>', <?= $doc['index'] ?>, '<?= $project['id'] ?>')">
                                    <i class="fas fa-upload"></i> Nova versão
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if (Auth::hasPermission('documents.upload')): ?>
                            <button class="btn btn-outline-primary btn-sm" onclick="uploadDocumentWorkflow('<?= $doc['name'] ?>', <?= $doc['index'] ?>, '<?= $project['id'] ?>')">
                                <i class="fas fa-upload me-1"></i>
                                Enviar Documento
                            </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

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
                    <div class="stage-icon <?= $project['workflow_stage'] >= 1 ? ($project['workflow_stage'] == 1 ? 'pending' : 'completed') : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 1 ? 'check' : ($project['workflow_stage'] == 1 ? 'clock' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Documentos</div>
                </div>
                
                <!-- Projeto -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] > 2 ? 'completed' : ($project['workflow_stage'] == 2 ? 'pending' : 'disabled') ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 2 ? 'check' : ($project['workflow_stage'] == 2 ? 'clock' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Projeto</div>
                </div>
                
                <!-- Produção -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] > 3 ? 'completed' : ($project['workflow_stage'] == 3 ? 'pending' : 'disabled') ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 3 ? 'check' : ($project['workflow_stage'] == 3 ? 'clock' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Produção</div>
                </div>
                
                <!-- Buildup -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] > 4 ? 'completed' : ($project['workflow_stage'] == 4 ? 'pending' : 'disabled') ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] > 4 ? 'check' : ($project['workflow_stage'] == 4 ? 'clock' : 'lock') ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Buildup</div>
                </div>
                
                <!-- Aprovado -->
                <div class="stage-item text-center">
                    <div class="stage-icon <?= $project['workflow_stage'] == 5 ? 'pending' : 'disabled' ?>">
                        <i class="fas fa-<?= $project['workflow_stage'] == 5 ? 'clock' : 'lock' ?>"></i>
                    </div>
                    <div class="stage-label mt-2">Aprovado</div>
                </div>
            </div>
            
            <!-- Hidden input to store current stage for JavaScript -->
            <input type="hidden" id="current-workflow-stage" value="<?= $project['workflow_stage'] ?? 1 ?>">
            
            <?php if (Auth::hasPermission('projects.manage_workflow')): ?>
            <!-- Workflow Controls for Admins, Coordinators, and Analysts -->
            <div class="workflow-controls mt-4 p-3 bg-light rounded">
                <h6 class="mb-3">Controle do Workflow</h6>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Etapa Atual</label>
                        <select class="form-select" id="workflow-stage-select" onchange="updateWorkflowStage('<?= $project['id'] ?>', this.value)">
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
                            <button class="btn btn-success btn-sm" onclick="advanceProjectWorkflow('<?= $project['id'] ?>')">
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
                            <button class="btn btn-warning btn-sm" onclick="revertProjectWorkflow('<?= $project['id'] ?>')">
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
                                    <select class="form-select form-select-sm status-select" data-document-id="<?= $document['id'] ?>" onchange="updateDocumentStatus(this.getAttribute('data-document-id'), this.value, this)">
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

<!-- Modal para Upload de Documento Específico no Workflow -->
<div class="modal fade" id="uploadDocumentWorkflowModal" tabindex="-1" aria-labelledby="uploadDocumentWorkflowModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentWorkflowModalLabel">Enviar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadDocumentWorkflowForm" enctype="multipart/form-data">
                    <input type="hidden" id="workflowProjectId" value="">
                    <input type="hidden" id="workflowDocumentIndex" value="">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Tipo de Documento:</strong></label>
                        <div id="workflowDocumentTypeInfo" class="alert alert-info">
                            <!-- Informações do documento serão inseridas aqui -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="workflowDocumentFile" class="form-label">Selecionar Arquivo *</label>
                        <input type="file" class="form-control" id="workflowDocumentFile" name="document_file" required>
                        <div class="form-text">
                            <span id="workflowFormatInfo"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="workflowDocumentDescription" class="form-label">Descrição (opcional)</label>
                        <textarea class="form-control" id="workflowDocumentDescription" name="description" rows="3" 
                                  placeholder="Adicione observações sobre o documento..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="submitWorkflowUpload">
                    <i class="fas fa-upload me-1"></i>
                    Enviar Documento
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

.stage-icon.pending {
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
<script>
// Dados dos documentos do template para o JavaScript no workflow
const workflowDocumentTemplate = <?= json_encode($documentChecklist) ?>;

function uploadDocumentWorkflow(documentName, documentIndex, projectId) {
    // Encontrar informações do documento
    const allDocs = [...(workflowDocumentTemplate.required_documents || []), ...(workflowDocumentTemplate.optional_documents || [])];
    const docInfo = allDocs.find(doc => doc.index === documentIndex);
    
    if (!docInfo) {
        alert('Erro: Informações do documento não encontradas.');
        return;
    }
    
    // Preencher modal com informações
    document.getElementById('workflowDocumentIndex').value = documentIndex;
    document.getElementById('workflowProjectId').value = projectId;
    document.getElementById('uploadDocumentWorkflowModalLabel').textContent = 'Enviar ' + documentName;
    
    // Mostrar informações do documento
    const infoDiv = document.getElementById('workflowDocumentTypeInfo');
    infoDiv.innerHTML = `
        <h6>${docInfo.name} ${docInfo.required ? '<span class="badge bg-danger">Obrigatório</span>' : '<span class="badge bg-info">Opcional</span>'}</h6>
        <p class="mb-1">${docInfo.description}</p>
        <small><strong>Formato aceito:</strong> ${docInfo.format} | <strong>Tamanho máximo:</strong> ${docInfo.max_size}</small>
    `;
    
    // Atualizar info de formato
    document.getElementById('workflowFormatInfo').textContent = `Formato aceito: ${docInfo.format}, Tamanho máximo: ${docInfo.max_size}`;
    
    // Limpar form
    document.getElementById('uploadDocumentWorkflowForm').reset();
    document.getElementById('workflowProjectId').value = projectId;
    document.getElementById('workflowDocumentIndex').value = documentIndex;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('uploadDocumentWorkflowModal'));
    modal.show();
}

function uploadNewVersionWorkflow(documentName, documentIndex, projectId) {
    uploadDocumentWorkflow(documentName, documentIndex, projectId);
    document.getElementById('uploadDocumentWorkflowModalLabel').textContent = 'Nova Versão - ' + documentName;
}

// Submit do upload no workflow
document.addEventListener('DOMContentLoaded', function() {
    const submitBtn = document.getElementById('submitWorkflowUpload');
    if (submitBtn) {
        submitBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Evitar submit normal do form
            
            const form = document.getElementById('uploadDocumentWorkflowForm');
            const fileInput = document.getElementById('workflowDocumentFile');
            
            if (!fileInput.files[0]) {
                alert('Por favor, selecione um arquivo.');
                return;
            }
            
            // Validar tamanho do arquivo
            const file = fileInput.files[0];
            const documentIndex = parseInt(document.getElementById('workflowDocumentIndex').value);
            const allDocs = [...(workflowDocumentTemplate.required_documents || []), ...(workflowDocumentTemplate.optional_documents || [])];
            const docInfo = allDocs.find(doc => doc.index === documentIndex);
            
            if (docInfo) {
                const maxSizeStr = docInfo.max_size;
                const maxSizeMB = parseInt(maxSizeStr.replace(/[^\d]/g, ''));
                const fileSizeMB = file.size / (1024 * 1024);
                
                if (fileSizeMB > maxSizeMB) {
                    alert(`Arquivo muito grande. Tamanho máximo permitido: ${maxSizeStr}`);
                    return;
                }
                
                // Validar formato (se especificado)
                if (docInfo.format !== 'Todos') {
                    const allowedFormats = docInfo.format.toLowerCase().split(',').map(f => f.trim());
                    const fileExtension = file.name.split('.').pop().toLowerCase();
                    
                    if (!allowedFormats.includes(fileExtension) && !allowedFormats.includes('pdf') && fileExtension !== 'pdf') {
                        if (!(allowedFormats.includes('pdf') && fileExtension === 'pdf')) {
                            alert(`Formato não permitido. Formatos aceitos: ${docInfo.format}`);
                            return;
                        }
                    }
                }
            }
            
            // Desabilitar botão e mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Enviando...';
            
            // Criar FormData
            const formData = new FormData();
            formData.append('document_file', file);
            formData.append('project_id', document.getElementById('workflowProjectId').value);
            formData.append('document_type', docInfo ? docInfo.name : 'Template Document');
            formData.append('document_index', documentIndex);
            formData.append('name', file.name);
            formData.append('description', document.getElementById('workflowDocumentDescription').value || '');
            formData.append('type', 'Template Document');
            formData.append('category', 'Documento');
            formData.append('template_based', '1');
            
            // Debug log
            console.log('FormData contents:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            console.log('File object:', file);
            console.log('File name:', file.name);
            console.log('File size:', file.size);
            
            // Enviar arquivo via workflow controller
            fetch('/documents/upload-project-file', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fechar modal
                    bootstrap.Modal.getInstance(document.getElementById('uploadDocumentWorkflowModal')).hide();
                    
                    // Mostrar sucesso
                    showAlert('Documento enviado com sucesso!', 'success');
                    
                    // Recarregar página após 2 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    alert('Erro: ' + (data.message || 'Erro desconhecido'));
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de conexão. Tente novamente.');
            })
            .finally(() => {
                // Restaurar botão
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-upload me-1"></i>Enviar Documento';
            });
        });
    }
});

// Funções para avançar/retroceder workflow
function advanceProjectWorkflow(projectId) {
    if (confirm('Tem certeza que deseja avançar o projeto para a próxima etapa?')) {
        fetch('/advance-workflow.php', {
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

function revertProjectWorkflow(projectId) {
    if (confirm('Tem certeza que deseja retroceder o projeto para a etapa anterior?')) {
        fetch('/revert-workflow.php', {
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

// Controle do Workflow
function updateWorkflowStage(projectId, stage) {
    fetch('/update-stage.php', {
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
    fetch('/update-status.php', {
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
        fetch('/advance-workflow.php', {
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
        fetch('/revert-workflow.php', {
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
        fetch('/finalize-project.php', {
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

function updateDocumentStatus(documentId, newStatus, selectElement) {
    // Verificar se o ID é válido
    if (!documentId || documentId === 'undefined') {
        console.error('ID do documento inválido:', documentId);
        alert('Erro: ID do documento não encontrado. Por favor, recarregue a página e tente novamente.');
        return;
    }
    
    const originalValue = selectElement.getAttribute('data-original-value') || selectElement.options[0].value;
    
    let additionalData = {};
    let confirmMessage = '';
    
    console.log('Atualizando documento ID:', documentId, 'para status:', newStatus);
    
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
    fetch(`/update-document-status.php?id=${documentId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
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
    try {
        console.log('showDocumentInfo called for document:', documentId);
        
        // Obter o modal usando querySelector (mais confiável)
        const modalElement = document.querySelector('#documentInfoModal');
        if (!modalElement) {
            console.error('Modal element not found with ID: documentInfoModal');
            alert('Erro: Modal não encontrado. Contate o suporte técnico.');
            return;
        }
        
        // Definir a variável global infoContent
        window.infoContent = document.querySelector('#documentInfoContent');
        if (!window.infoContent) {
            console.error('Modal content element not found with ID: documentInfoContent');
            alert('Erro: Conteúdo do modal não encontrado. Contate o suporte técnico.');
            return;
        }
        
        // Mostrar o modal manualmente se bootstrap não estiver disponível
        if (typeof bootstrap === 'undefined' || typeof bootstrap.Modal !== 'function') {
            console.warn('Bootstrap não encontrado ou bootstrap.Modal não é uma função, usando alternativa manual');
            
            // Exibir o modal manualmente
            modalElement.classList.add('show');
            modalElement.style.display = 'block';
            document.body.classList.add('modal-open');
            
            // Criar overlay
            const backdrop = document.createElement('div');
            backdrop.className = 'modal-backdrop fade show';
            document.body.appendChild(backdrop);
            
            // Adicionar evento de fechamento nos botões de fechar
            const closeButtons = modalElement.querySelectorAll('[data-bs-dismiss="modal"]');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    modalElement.classList.remove('show');
                    modalElement.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    backdrop.remove();
                });
            });
        } else {
            // Usar Bootstrap normalmente
            console.log('Usando bootstrap.Modal para exibir o modal');
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        }
        
        // Atualizar conteúdo para loading
        infoContent.innerHTML = `
            <div class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Carregando...</span>
                </div>
                <p class="mt-2">Carregando informações...</p>
            </div>
        `;
        
        // Buscar informações do documento
        loadDocumentInfo(documentId);
        
    } catch (error) {
        console.error('Erro ao mostrar modal:', error);
        alert('Erro ao exibir informações do documento: ' + error.message);
    }
}

function loadDocumentInfo(documentId) {
    console.log('Carregando informações para o documento:', documentId);
    
    // Verificar se window.infoContent já está definido
    if (!window.infoContent) {
        window.infoContent = document.querySelector('#documentInfoContent');
        if (!window.infoContent) {
            console.error('Element not found: documentInfoContent');
            alert('Erro: Elemento de conteúdo não encontrado');
            return;
        }
    }
    
    window.infoContent.innerHTML = `
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
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.text();
        })
        .then(text => {
            console.log('Raw response length:', text.length);
            
            try {
                // Tenta fazer o parse do JSON
                const data = JSON.parse(text.trim());
                console.log('Parsed data successfully');
                
                if (data.success && data.document) {
                    displayDocumentInfo(data.document);
                } else {
                    throw new Error(data.message || 'Dados inválidos recebidos');
                }
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                console.error('Response text (first 100 chars):', text.substring(0, 100));
                
                // Obter elemento de conteúdo do modal usando querySelector
                if (!window.infoContent) {
                    window.infoContent = document.querySelector('#documentInfoContent');
                }
                
                if (window.infoContent) {
                    window.infoContent.innerHTML = `
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Erro ao processar resposta do servidor.
                            <br><small>Detalhes: ${parseError.message}</small>
                        </div>
                    `;
                } else {
                    console.error('Elemento #documentInfoContent não encontrado');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            
            // Obter elemento de conteúdo do modal usando querySelector
            if (!window.infoContent) {
                window.infoContent = document.querySelector('#documentInfoContent');
            }
            
            if (window.infoContent) {
                window.infoContent.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Erro ao carregar informações do documento: ${error.message}
                    </div>
                `;
            } else {
                console.error('Elemento #documentInfoContent não encontrado no catch block');
                alert('Erro ao carregar dados do documento: ' + error.message);
            }
        });
}

function displayDocumentInfo(docData) {
    try {
        console.log('Exibindo informações do documento:', docData);
        
        // Verificar se o docData tem todas as propriedades necessárias
        if (!docData || !docData.id) {
            console.error('Objeto docData inválido:', docData);
            alert('Erro: Dados do documento inválidos');
            return;
        }
        
        // Usar a variável global window.infoContent
        if (!window.infoContent) {
            window.infoContent = document.querySelector('#documentInfoContent');
            if (!window.infoContent) {
                console.error('Element not found: documentInfoContent');
                alert('Erro ao exibir informações: Elemento não encontrado');
                return;
            }
        }
    } catch (e) {
        console.error('Erro ao iniciar displayDocumentInfo:', e);
        return;
    }

    const content = `
        <div class="row">
            <div class="col-md-8">
                <h6 class="text-primary mb-3">
                    <i class="fas fa-file-alt me-2"></i>
                    ${docData.name || 'Sem nome'}
                </h6>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Arquivo original:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.original_name || 'N/A'}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Tamanho:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.size_formatted || 'N/A'}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Tipo de arquivo:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.mime_type || 'N/A'}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Enviado por:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.uploader || 'N/A'}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Data de envio:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.created_at_formatted || 'N/A'}
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Projeto:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.project_name || 'N/A'}
                    </div>
                </div>
                
                ${docData.description ? `
                <div class="row mb-3">
                    <div class="col-sm-4">
                        <strong>Descrição:</strong>
                    </div>
                    <div class="col-sm-8">
                        ${docData.description}
                    </div>
                </div>
                ` : ''}
            </div>
            
            <div class="col-md-4">
                <div class="card bg-light">
                    <div class="card-body text-center">
                        <h6 class="card-title">Status</h6>
                        <span class="badge fs-6 bg-${getStatusColor(docData.status || 'pendente')}">
                            ${docData.status_label || 'Pendente'}
                        </span>
                        
                        ${docData.approved_by ? `
                        <hr>
                        <small class="text-muted">
                            <strong>Aprovado por:</strong><br>
                            ${docData.approved_by}<br>
                            ${docData.approved_at ? new Date(docData.approved_at).toLocaleString('pt-BR') : 'N/A'}
                        </small>
                        ` : ''}
                        
                        ${docData.rejected_by ? `
                        <hr>
                        <small class="text-muted">
                            <strong>Rejeitado por:</strong><br>
                            ${docData.rejected_by}<br>
                            ${docData.rejected_at ? new Date(docData.rejected_at).toLocaleString('pt-BR') : 'N/A'}
                        </small>
                        ${docData.rejection_reason ? `<br><br><strong>Motivo:</strong><br>${docData.rejection_reason}` : ''}
                        ` : ''}
                        
                        ${docData.comments ? `
                        <hr>
                        <small class="text-muted">
                            <strong>Comentários:</strong><br>
                            ${docData.comments}
                        </small>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    
    window.infoContent.innerHTML = content;
    
    // Mostrar botão de download
    try {
        const downloadBtn = document.querySelector('#downloadFromModal');
        if (downloadBtn) {
            console.log('Download button found, setting up click handler for document ID:', docData.id);
            downloadBtn.style.display = 'inline-block';
            downloadBtn.onclick = function() {
                console.log('Download button clicked for document ID:', docData.id);
                downloadDocument(docData.id);
            };
        } else {
            console.error('Element not found: downloadFromModal');
        }
    } catch (err) {
        console.error('Error setting up download button:', err);
    }
}

function getStatusColor(status) {
    switch (status) {
        case 'aprovado': return 'success';
        case 'rejeitado': return 'danger';
        case 'em_analise': return 'warning';
        default: return 'secondary';
    }
}

// Função de download movida para download-helper.js
// Esta função permanece aqui para compatibilidade, mas chama a implementação global
function downloadDocument(documentId) {
    if (window.downloadDocument) {
        window.downloadDocument(documentId);
    } else {
        console.error('Função de download global não encontrada');
        showAlert('Erro: função de download não disponível', 'error');
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
$scripts = '<script src="/assets/js/workflow-stages.js"></script><script src="/assets/js/download-helper.js"></script>';
include __DIR__ . '/../layouts/app.php';
?>
