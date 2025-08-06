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
        
        <!-- Checklist de Documentos Obrigatórios -->
        <?php if (!empty($documentChecklist['required_documents']) || !empty($documentChecklist['optional_documents'])): ?>
        <div class="card mt-4">
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
                                                onclick="uploadNewVersion('<?= $doc['name'] ?>', <?= $doc['index'] ?>)">
                                            <i class="fas fa-upload"></i> Nova versão
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (Auth::hasPermission('documents.upload')): ?>
                                    <button class="btn btn-primary btn-sm" onclick="uploadDocument('<?= $doc['name'] ?>', <?= $doc['index'] ?>)">
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
                                                onclick="uploadNewVersion('<?= $doc['name'] ?>', <?= $doc['index'] ?>)">
                                            <i class="fas fa-upload"></i> Nova versão
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?php if (Auth::hasPermission('documents.upload')): ?>
                                    <button class="btn btn-outline-primary btn-sm" onclick="uploadDocument('<?= $doc['name'] ?>', <?= $doc['index'] ?>)">
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
                    <p><?= htmlspecialchars($project['client_name'] ?? $project['client_id'] ?? 'Não atribuído') ?></p>
                </div>
                
                <div class="mb-3">
                    <strong>Nº Orçamento:</strong>
                    <p><?= htmlspecialchars($project['budget_number'] ?? 'Não definido') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>

<!-- Modal para Upload de Documento Específico -->
<div class="modal fade" id="uploadDocumentModal" tabindex="-1" aria-labelledby="uploadDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadDocumentModalLabel">Enviar Documento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="uploadDocumentForm" enctype="multipart/form-data">
                    <input type="hidden" id="projectId" value="<?= $project['id'] ?>">
                    <input type="hidden" id="documentIndex" value="">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Tipo de Documento:</strong></label>
                        <div id="documentTypeInfo" class="alert alert-info">
                            <!-- Informações do documento serão inseridas aqui -->
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="documentFile" class="form-label">Selecionar Arquivo *</label>
                        <input type="file" class="form-control" id="documentFile" name="document_file" required>
                        <div class="form-text">
                            <span id="formatInfo"></span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="documentDescription" class="form-label">Descrição (opcional)</label>
                        <textarea class="form-control" id="documentDescription" name="description" rows="3" 
                                  placeholder="Adicione observações sobre o documento..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="submitUpload">
                    <i class="fas fa-upload me-1"></i>
                    Enviar Documento
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Dados dos documentos do template para o JavaScript
const documentTemplate = <?= json_encode($documentChecklist) ?>;

function uploadDocument(documentName, documentIndex) {
    // Encontrar informações do documento
    const allDocs = [...(documentTemplate.required_documents || []), ...(documentTemplate.optional_documents || [])];
    const docInfo = allDocs.find(doc => doc.index === documentIndex);
    
    if (!docInfo) {
        alert('Erro: Informações do documento não encontradas.');
        return;
    }
    
    // Preencher modal com informações
    document.getElementById('documentIndex').value = documentIndex;
    document.getElementById('uploadDocumentModalLabel').textContent = 'Enviar ' + documentName;
    
    // Mostrar informações do documento
    const infoDiv = document.getElementById('documentTypeInfo');
    infoDiv.innerHTML = `
        <h6>${docInfo.name} ${docInfo.required ? '<span class="badge bg-danger">Obrigatório</span>' : '<span class="badge bg-info">Opcional</span>'}</h6>
        <p class="mb-1">${docInfo.description}</p>
        <small><strong>Formato aceito:</strong> ${docInfo.format} | <strong>Tamanho máximo:</strong> ${docInfo.max_size}</small>
    `;
    
    // Atualizar info de formato
    document.getElementById('formatInfo').textContent = `Formato aceito: ${docInfo.format}, Tamanho máximo: ${docInfo.max_size}`;
    
    // Limpar form
    document.getElementById('uploadDocumentForm').reset();
    document.getElementById('projectId').value = '<?= $project['id'] ?>';
    document.getElementById('documentIndex').value = documentIndex;
    
    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
    modal.show();
}

function uploadNewVersion(documentName, documentIndex) {
    uploadDocument(documentName, documentIndex);
    document.getElementById('uploadDocumentModalLabel').textContent = 'Nova Versão - ' + documentName;
}

// Submit do upload
document.getElementById('submitUpload').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar submit normal do form
    
    const form = document.getElementById('uploadDocumentForm');
    const fileInput = document.getElementById('documentFile');
    const submitBtn = this;
    
    if (!fileInput.files[0]) {
        alert('Por favor, selecione um arquivo.');
        return;
    }
    
    // Validar tamanho do arquivo
    const file = fileInput.files[0];
    const documentIndex = parseInt(document.getElementById('documentIndex').value);
    const allDocs = [...(documentTemplate.required_documents || []), ...(documentTemplate.optional_documents || [])];
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
    formData.append('document', file);
    formData.append('project_id', document.getElementById('projectId').value);
    formData.append('document_type', docInfo ? docInfo.name : 'Template Document');
    formData.append('document_index', documentIndex);
    formData.append('description', document.getElementById('documentDescription').value || '');
    formData.append('template_based', '1');
    
    // Enviar arquivo
    fetch('/documents/upload-project-file', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Fechar modal
            bootstrap.Modal.getInstance(document.getElementById('uploadDocumentModal')).hide();
            
            // Mostrar sucesso
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <strong>Sucesso!</strong> ${data.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.project-header').after(alertDiv);
            
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

// Função para aprovar documento
function approveDocument(documentId) {
    if (!confirm('Deseja aprovar este documento?')) return;
    
    fetch(`/documents/project/${documentId}/approve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão. Tente novamente.');
    });
}

// Função para rejeitar documento
function rejectDocument(documentId) {
    const reason = prompt('Motivo da rejeição:');
    if (!reason) return;
    
    fetch(`/documents/project/${documentId}/reject`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Erro: ' + (data.message || 'Erro desconhecido'));
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        alert('Erro de conexão. Tente novamente.');
    });
}
</script>
