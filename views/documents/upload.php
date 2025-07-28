<?php
use App\Core\Auth;

$title = 'Upload de Documento - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<!-- Alertas de erro ou sucesso -->
<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    <strong>Erro no upload:</strong> <?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i>
    <strong>Sucesso!</strong> Documento enviado com sucesso!
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="documents-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Enviar Documento</h1>
            <p class="text-muted mb-0">Fazer upload de um novo documento</p>
        </div>
        <a href="/documents" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/documents/upload" enctype="multipart/form-data" data-validate="true">
                    <!-- Seleção de Projeto -->
                    <div class="mb-4">
                        <label for="project_id" class="form-label">
                            <i class="fas fa-project-diagram me-1 text-primary"></i>
                            Projeto <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="project_id" name="project_id" required onchange="updateRequiredDocuments()">
                            <option value="">Selecione o projeto</option>
                            <?php foreach ($projects as $project): ?>
                            <option value="<?= $project['id'] ?>" 
                                    data-template="<?= htmlspecialchars($project['document_template'] ?? '') ?>"
                                    <?= (isset($_GET['project_id']) && $_GET['project_id'] == $project['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($project['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecione o projeto para o qual deseja enviar documentos</div>
                    </div>

                    <!-- Documentos Requeridos (mostrados dinamicamente) -->
                    <?php if (!empty($requiredDocuments)): ?>
                    <div class="mb-4" id="requiredDocumentsSection">
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="fas fa-list-check me-1 text-success"></i>
                                    Documentos Necessários para Este Projeto
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($requiredDocuments as $index => $doc): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="border rounded p-3 h-100">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0">
                                                    <i class="fas fa-file-alt me-1 text-primary"></i>
                                                    <?= htmlspecialchars($doc['name']) ?>
                                                </h6>
                                                <?php if ($doc['required'] ?? true): ?>
                                                <span class="badge bg-danger">Obrigatório</span>
                                                <?php else: ?>
                                                <span class="badge bg-secondary">Opcional</span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if (!empty($doc['description'])): ?>
                                            <p class="small text-muted mb-2">
                                                <i class="fas fa-info-circle me-1"></i>
                                                <?= htmlspecialchars($doc['description']) ?>
                                            </p>
                                            <?php endif; ?>
                                            
                                            <div class="small text-muted">
                                                <i class="fas fa-file-upload me-1"></i>
                                                <strong>Formato:</strong> <?= htmlspecialchars($doc['format'] ?? 'PDF') ?>
                                                <br>
                                                <i class="fas fa-weight-hanging me-1"></i>
                                                <strong>Tamanho máx:</strong> <?= htmlspecialchars($doc['max_size'] ?? '10MB') ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="alert alert-info mt-3 mb-0">
                                    <i class="fas fa-lightbulb me-1"></i>
                                    <strong>Dica:</strong> Você pode enviar um documento de cada vez. 
                                    Após enviar, retorne a esta página para enviar os demais documentos.
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Upload do Documento -->
                    <div class="mb-3">
                        <label for="document_type" class="form-label">
                            <i class="fas fa-tag me-1 text-info"></i>
                            Tipo de Documento <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="document_type" name="document_type" required
                               placeholder="Ex: RG do Responsável Técnico">
                        <div class="form-text">Identifique claramente qual documento você está enviando</div>
                    </div>

                    <div class="mb-3">
                        <label for="document" class="form-label">
                            <i class="fas fa-cloud-upload-alt me-1 text-primary"></i>
                            Arquivo <span class="text-danger">*</span>
                        </label>
                        <div class="drop-zone">
                            <input type="file" class="form-control" id="document" name="document" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx">
                            <div class="drop-zone-text">
                                <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                <p class="mb-0">Clique para selecionar um arquivo ou arraste aqui</p>
                                <small class="text-muted">Formatos: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (máx. 10MB)</small>
                            </div>
                        </div>
                        <div class="file-preview mt-2" style="display: none;"></div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome do Documento</label>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Nome personalizado (opcional)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoria</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">Selecione uma categoria</option>
                                    <option value="Procedimento">Procedimento</option>
                                    <option value="Template">Template</option>
                                    <option value="Manual">Manual</option>
                                    <option value="Relatório">Relatório</option>
                                    <option value="Outros">Outros</option>
                                    <?php foreach ($categories ?? [] as $category): ?>
                                    <option value="<?= htmlspecialchars($category['name']) ?>"><?= htmlspecialchars($category['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3" placeholder="Descreva o documento..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Projeto Relacionado</label>
                        <select class="form-select" id="project_id" name="project_id">
                            <option value="">Nenhum projeto específico</option>
                            <?php foreach ($projects ?? [] as $project): ?>
                            <option value="<?= $project['id'] ?>"><?= htmlspecialchars($project['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>
                            Enviar Documento
                        </button>
                        <a href="/documents" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Informações de Upload</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-primary">Formatos Aceitos:</h6>
                    <ul class="list-unstyled small">
                        <li><i class="fas fa-file-pdf text-danger me-1"></i> PDF</li>
                        <li><i class="fas fa-file-word text-primary me-1"></i> DOC, DOCX</li>
                        <li><i class="fas fa-file-excel text-success me-1"></i> XLS, XLSX</li>
                        <li><i class="fas fa-file-image text-info me-1"></i> JPG, PNG</li>
                    </ul>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Limite de Tamanho:</h6>
                    <p class="small text-muted mb-0">Máximo 10MB por arquivo</p>
                </div>
                
                <div class="mb-3">
                    <h6 class="text-primary">Categorias:</h6>
                    <p class="small text-muted mb-0">Selecione uma categoria para organizar melhor seus documentos</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.drop-zone {
    border: 2px dashed #e9ecef;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    cursor: pointer;
}

.drop-zone:hover {
    border-color: #007bff;
    background: linear-gradient(135deg, #f0f8ff 0%, #ffffff 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 123, 255, 0.1);
}

.drop-zone.drag-over {
    border-color: #28a745;
    background: linear-gradient(135deg, #f0fff4 0%, #ffffff 100%);
    transform: scale(1.02);
}

.drop-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    opacity: 0;
    cursor: pointer;
}

.drop-zone-text i {
    color: #6c757d;
    transition: all 0.3s ease;
}

.drop-zone:hover .drop-zone-text i {
    color: #007bff;
    transform: translateY(-3px);
}

.file-preview {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border: 1px solid #e9ecef;
    border-radius: 12px;
    padding: 1rem;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.upload-progress {
    transition: all 0.3s ease;
}
</style>

<!-- Modal de Upload Simples e Elegante -->
<div class="modal fade" id="uploadProgressModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body text-center p-4">
                <!-- Estado: Enviando -->
                <div id="uploadingState">
                    <div class="upload-animation mb-3">
                        <div class="upload-circle">
                            <i class="fas fa-cloud-upload-alt fa-2x text-primary"></i>
                        </div>
                    </div>
                    <h6 class="mb-2">Enviando arquivo...</h6>
                    <div class="progress mb-3" style="height: 6px;">
                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="uploadProgress"></div>
                    </div>
                    <small id="uploadStatus" class="text-muted">Preparando...</small>
                </div>
                
                <!-- Estado: Sucesso -->
                <div id="successState" style="display: none;">
                    <div class="success-animation mb-3">
                        <div class="success-circle">
                            <i class="fas fa-check fa-2x text-white"></i>
                        </div>
                    </div>
                    <h6 class="text-success mb-2">Enviado com sucesso!</h6>
                    <p class="text-muted small mb-3">Seu arquivo foi processado.</p>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-primary btn-sm" onclick="redirectToDocuments()">
                            <i class="fas fa-folder me-1"></i> Ver Documentos
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="closeUploadModal()">
                            <i class="fas fa-plus me-1"></i> Enviar Outro
                        </button>
                    </div>
                </div>
                
                <!-- Estado: Erro -->
                <div id="errorState" style="display: none;">
                    <div class="error-animation mb-3">
                        <div class="error-circle">
                            <i class="fas fa-times fa-2x text-white"></i>
                        </div>
                    </div>
                    <h6 class="text-danger mb-2">Erro no envio</h6>
                    <p id="errorMessage" class="text-muted small mb-3">Algo deu errado.</p>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="closeUploadModal()">
                        <i class="fas fa-redo me-1"></i> Tentar Novamente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-circle, .success-circle, .error-circle {
    width: 60px;
    height: 60px;
    margin: 0 auto;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.upload-circle {
    background: linear-gradient(45deg, #f8f9fa, #e9ecef);
    border: 2px solid #e9ecef;
    animation: pulse 2s infinite;
}

.success-circle {
    background: linear-gradient(45deg, #28a745, #20c997);
    animation: bounceIn 0.6s ease-out;
}

.error-circle {
    background: linear-gradient(45deg, #dc3545, #fd7e14);
    animation: shake 0.5s ease-in-out;
}

@keyframes pulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 123, 255, 0.4); }
    50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(0, 123, 255, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(0, 123, 255, 0); }
}

@keyframes bounceIn {
    0% { transform: scale(0.3); opacity: 0; }
    50% { transform: scale(1.1); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

.modal-content {
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.progress {
    border-radius: 10px;
    background-color: #f1f3f4;
}

.progress-bar {
    border-radius: 10px;
}

.btn {
    border-radius: 8px;
    font-weight: 500;
}
</style>

<script>
// Interceptar submit do formulário para mostrar progresso
document.querySelector('form[action="/documents/upload"]').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('document');
    
    if (fileInput.files.length > 0) {
        e.preventDefault();
        showUploadProgress();
        
        // Simular progresso
        simulateUploadProgress();
        
        // Submeter formulário via AJAX
        submitFormWithProgress(this);
    }
});

function showUploadProgress() {
    const modal = new bootstrap.Modal(document.getElementById('uploadProgressModal'));
    modal.show();
    
    // Resetar estados
    document.getElementById('uploadingState').style.display = 'block';
    document.getElementById('successState').style.display = 'none';
    document.getElementById('errorState').style.display = 'none';
    document.getElementById('uploadProgress').style.width = '0%';
}

function simulateUploadProgress() {
    const progressBar = document.getElementById('uploadProgress');
    const statusText = document.getElementById('uploadStatus');
    
    let progress = 0;
    const messages = [
        'Validando arquivo...',
        'Processando...',
        'Enviando...',
        'Quase pronto...'
    ];
    
    const interval = setInterval(() => {
        progress += Math.random() * 20 + 5;
        if (progress > 90) progress = 90;
        
        progressBar.style.width = progress + '%';
        
        const messageIndex = Math.floor(progress / 25);
        if (messages[messageIndex]) {
            statusText.textContent = messages[messageIndex];
        }
    }, 300);
    
    // Parar simulação após 2.5 segundos
    setTimeout(() => clearInterval(interval), 2500);
}

function submitFormWithProgress(form) {
    const formData = new FormData(form);
    
    fetch('/documents/upload', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        // Completar barra de progresso
        document.getElementById('uploadProgress').style.width = '100%';
        document.getElementById('uploadStatus').textContent = 'Finalizando...';
        
        if (response.ok) {
            // Verificar se é JSON ou redirecionamento
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Se não é JSON, é redirecionamento - sucesso
                return { success: true, redirect: true };
            }
        } else {
            throw new Error(`Erro ${response.status}: ${response.statusText}`);
        }
    })
    .then(data => {
        setTimeout(() => {
            if (data.success) {
                showUploadSuccess(data);
            } else {
                showUploadError(data.message || 'Erro desconhecido');
            }
        }, 500);
    })
    .catch(error => {
        setTimeout(() => {
            showUploadError(error.message);
        }, 500);
    });
}

function showUploadSuccess(data) {
    document.getElementById('uploadingState').style.display = 'none';
    document.getElementById('successState').style.display = 'block';
    
    // Auto-fechar após 3 segundos e ir para documentos
    setTimeout(() => {
        redirectToDocuments();
    }, 2500);
}

function showUploadError(message) {
    document.getElementById('uploadingState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
    document.getElementById('errorMessage').textContent = message;
}

function redirectToDocuments() {
    window.location.href = '/documents?success=uploaded';
}

function closeUploadModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadProgressModal'));
    modal.hide();
    
    // Limpar formulário
    document.querySelector('form[action="/documents/upload"]').reset();
    
    // Limpar preview
    const preview = document.querySelector('.file-preview');
    if (preview) {
        preview.style.display = 'none';
        preview.innerHTML = '';
    }
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
