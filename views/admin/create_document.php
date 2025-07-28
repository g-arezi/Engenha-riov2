<?php
$title = 'Criar Template de Documento - Engenha Rio';
$showSidebar = true;
$showNavbar = true;
ob_start();
?>

<div class="admin-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Criar Template de Documento</h1>
            <p class="text-muted mb-0">Adicionar novo template de documento personalizado</p>
        </div>
        <a href="/admin#documentos" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar
        </a>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php
        $errorMessage = match($_GET['error']) {
            'template_exists' => 'Já existe um template com este código.',
            'invalid_data' => 'Dados inválidos fornecidos.',
            default => 'Erro ao criar template. Tente novamente.'
        };
        echo $errorMessage;
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Informações do Template</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/documents/create">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome do Template <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required 
                                       placeholder="Ex: Projeto Estrutural">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="code" class="form-label">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="code" name="code" required 
                                       placeholder="Ex: projeto_estrutural" 
                                       pattern="[a-z0-9_]+" 
                                       title="Apenas letras minúsculas, números e underscores">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição</label>
                        <textarea class="form-control" id="description" name="description" rows="3"
                                  placeholder="Descreva o propósito deste template..."></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Selecione uma categoria</option>
                                    <option value="documentacao_legal">Documentação Legal</option>
                                    <option value="documentacao_tecnica">Documentação Técnica</option>
                                    <option value="engenharia_estrutural">Engenharia Estrutural</option>
                                    <option value="engenharia_arquitetonica">Engenharia Arquitetônica</option>
                                    <option value="engenharia_hidraulica">Engenharia Hidráulica</option>
                                    <option value="engenharia_eletrica">Engenharia Elétrica</option>
                                    <option value="engenharia_sanitaria">Engenharia Sanitária</option>
                                    <option value="meio_ambiente">Meio Ambiente</option>
                                    <option value="outros">Outros</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="ativo" selected>Ativo</option>
                                    <option value="inativo">Inativo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Formatos Aceitos</label>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="format_pdf" name="formats[]" value="pdf" checked>
                                    <label class="form-check-label" for="format_pdf">PDF</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="format_doc" name="formats[]" value="doc">
                                    <label class="form-check-label" for="format_doc">DOC/DOCX</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="format_img" name="formats[]" value="img">
                                    <label class="form-check-label" for="format_img">JPG/PNG</label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="format_dwg" name="formats[]" value="dwg">
                                    <label class="form-check-label" for="format_dwg">DWG/DXF</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="/admin#documentos" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Criar Template
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Dicas</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6><i class="fas fa-info-circle me-1"></i> Código do Template</h6>
                    <p class="mb-0 small">Use apenas letras minúsculas, números e underscores. Este código será usado internamente pelo sistema.</p>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="fas fa-exclamation-triangle me-1"></i> Categorias</h6>
                    <p class="mb-0 small">Escolha a categoria adequada para facilitar a organização e busca dos documentos.</p>
                </div>
                
                <div class="alert alert-success">
                    <h6><i class="fas fa-check-circle me-1"></i> Formatos</h6>
                    <p class="mb-0 small">Selecione os formatos de arquivo que serão aceitos para este tipo de documento.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-gerar código baseado no nome
    const nameInput = document.getElementById('name');
    const codeInput = document.getElementById('code');
    
    nameInput.addEventListener('input', function() {
        if (!codeInput.dataset.userModified) {
            const code = this.value
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '') // Remove acentos
                .replace(/[^a-z0-9\s]/g, '') // Remove caracteres especiais
                .replace(/\s+/g, '_') // Substitui espaços por underscores
                .substring(0, 50); // Limita o tamanho
            codeInput.value = code;
        }
    });
    
    codeInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });
});
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/app.php';
?>
