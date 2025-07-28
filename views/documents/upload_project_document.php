<?php
use App\Core\Auth;

$title = 'Upload Documento do Projeto - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="project-document-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Upload de Documento</h1>
            <p class="text-muted mb-0">Enviar documento para o projeto: <strong><?= htmlspecialchars($project['name']) ?></strong></p>
        </div>
        <a href="/documents/project/<?= $project['id'] ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar ao Projeto
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-upload me-2"></i>
                    <?= htmlspecialchars($template['name']) ?>
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/documents/project/upload" enctype="multipart/form-data" id="documentUploadForm">
                    <input type="hidden" name="project_id" value="<?= htmlspecialchars($project['id']) ?>">
                    <input type="hidden" name="document_type" value="<?= htmlspecialchars($template['code']) ?>">
                    
                    <div class="mb-3">
                        <label for="document" class="form-label">Arquivo <span class="text-danger">*</span></label>
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" class="form-control" id="document" name="document" required 
                                   accept=".pdf,.doc,.docx,.dwg,.jpg,.jpeg,.png,.xls,.xlsx">
                            <div class="upload-placeholder">
                                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Arraste o arquivo para esta área</h5>
                                <p class="text-muted mb-3">ou clique para buscar no seu computador</p>
                                <div class="upload-formats">
                                    <small class="text-muted">
                                        Formatos aceitos: PDF, DOC, DOCX, DWG, JPG, PNG, XLS, XLSX (máx. 10MB)
                                    </small>
                                </div>
                            </div>
                            <div class="upload-preview" id="uploadPreview" style="display: none;">
                                <div class="d-flex align-items-center">
                                    <div class="file-icon me-3">
                                        <i class="fas fa-file fa-2x text-primary"></i>
                                    </div>
                                    <div class="file-info flex-grow-1">
                                        <div class="file-name fw-bold"></div>
                                        <div class="file-size text-muted"></div>
                                        <div class="upload-progress mt-2">
                                            <div class="progress" style="height: 6px;">
                                                <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="file-actions">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeFile()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Documento</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($template['name']) ?>"
                               placeholder="Nome personalizado (opcional)">
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3" 
                                  placeholder="Adicione observações sobre este documento..."></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload me-1"></i>
                            Enviar Documento
                        </button>
                        <a href="/documents/project/<?= $project['id'] ?>" class="btn btn-outline-secondary">
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
                <h6 class="mb-0">Informações do Documento</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">TIPO:</label>
                    <div class="fw-medium"><?= htmlspecialchars($template['name']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small">PROJETO:</label>
                    <div class="fw-medium"><?= htmlspecialchars($project['name']) ?></div>
                </div>
                
                <div class="mb-3">
                    <label class="text-muted small">STATUS ATUAL:</label>
                    <div><span class="badge bg-warning">Pendente Upload</span></div>
                </div>
                
                <?php if (!empty($template['description'])): ?>
                <div class="mb-3">
                    <label class="text-muted small">DESCRIÇÃO:</label>
                    <div class="small"><?= htmlspecialchars($template['description']) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Processo de Aprovação</h6>
            </div>
            <div class="card-body">
                <div class="approval-steps">
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-content">
                            <div class="step-title">Upload do Documento</div>
                            <div class="step-description">Cliente envia o documento</div>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-content">
                            <div class="step-title">Análise Técnica</div>
                            <div class="step-description">Equipe analisa o documento</div>
                        </div>
                    </div>
                    
                    <div class="step">
                        <div class="step-number">3</div>
                        <div class="step-content">
                            <div class="step-title">Aprovação</div>
                            <div class="step-description">Documento aprovado ou rejeitado</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.upload-zone {
    border: 2px dashed #e9ecef;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    transition: all 0.3s ease;
    position: relative;
    min-height: 200px;
    background-color: #f8f9fa;
}

.upload-zone:hover {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.05);
}

.upload-zone.drag-over {
    border-color: #0d6efd;
    background-color: rgba(13, 110, 253, 0.1);
}

.upload-zone input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
}

.upload-preview {
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 1rem;
}

.approval-steps .step {
    display: flex;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.approval-steps .step:last-child {
    margin-bottom: 0;
}

.step-number {
    width: 24px;
    height: 24px;
    background-color: #e9ecef;
    color: #6c757d;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    margin-right: 0.75rem;
    flex-shrink: 0;
}

.step-title {
    font-weight: 600;
    font-size: 0.875rem;
    color: #212529;
}

.step-description {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 0.25rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('document');
    const uploadPreview = document.getElementById('uploadPreview');
    const uploadPlaceholder = uploadZone.querySelector('.upload-placeholder');
    
    // Drag and drop functionality
    uploadZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        uploadZone.classList.add('drag-over');
    });
    
    uploadZone.addEventListener('dragleave', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
    });
    
    uploadZone.addEventListener('drop', function(e) {
        e.preventDefault();
        uploadZone.classList.remove('drag-over');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });
    
    // File input change
    fileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            handleFileSelect(e.target.files[0]);
        }
    });
    
    function handleFileSelect(file) {
        // Validate file
        if (!validateFile(file)) {
            return;
        }
        
        // Show preview
        uploadPlaceholder.style.display = 'none';
        uploadPreview.style.display = 'block';
        
        // Update preview info
        uploadPreview.querySelector('.file-name').textContent = file.name;
        uploadPreview.querySelector('.file-size').textContent = formatFileSize(file.size);
        
        // Simulate upload progress
        simulateUploadProgress();
    }
    
    function validateFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        const allowedTypes = ['pdf', 'doc', 'docx', 'dwg', 'jpg', 'jpeg', 'png', 'xls', 'xlsx'];
        
        const fileExtension = file.name.split('.').pop().toLowerCase();
        
        if (file.size > maxSize) {
            alert('Arquivo muito grande. Tamanho máximo: 10MB');
            return false;
        }
        
        if (!allowedTypes.includes(fileExtension)) {
            alert('Tipo de arquivo não permitido');
            return false;
        }
        
        return true;
    }
    
    function simulateUploadProgress() {
        const progressBar = uploadPreview.querySelector('.progress-bar');
        let progress = 0;
        
        const interval = setInterval(() => {
            progress += Math.random() * 30;
            if (progress >= 100) {
                progress = 100;
                clearInterval(interval);
            }
            
            progressBar.style.width = progress + '%';
            
            if (progress === 100) {
                progressBar.classList.add('bg-success');
            }
        }, 200);
    }
    
    window.removeFile = function() {
        fileInput.value = '';
        uploadPreview.style.display = 'none';
        uploadPlaceholder.style.display = 'block';
    };
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
