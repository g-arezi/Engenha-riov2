<?php
$title = 'Criar Template - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="admin-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Novo Template</h1>
            <p class="text-muted mb-0">Criar um novo template de documento</p>
        </div>
        <a href="/admin/templates" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 col-md-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/admin/templates/create" data-validate="true">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nome do Template <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                        <div class="form-text">Nome que aparecerá na lista de templates</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="description" class="form-label">Descrição <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="3" required placeholder="Descreva o propósito deste template..."></textarea>
                        <div class="form-text">Descrição detalhada do template</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="category" class="form-label">Categoria <span class="text-danger">*</span></label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Selecione uma categoria</option>
                                    <option value="documentacao_legal">Documentação Legal</option>
                                    <option value="engenharia_estrutural">Engenharia Estrutural</option>
                                    <option value="engenharia_arquitetonica">Engenharia Arquitetônica</option>
                                    <option value="engenharia_hidraulica">Engenharia Hidráulica</option>
                                    <option value="engenharia_eletrica">Engenharia Elétrica</option>
                                    <option value="engenharia_sanitaria">Engenharia Sanitária</option>
                                    <option value="engenharia_ambiental">Engenharia Ambiental</option>
                                    <option value="engenharia_mecanica">Engenharia Mecânica</option>
                                    <option value="engenharia_civil">Engenharia Civil</option>
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
                    
                    <!-- Documentos Requeridos -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-file-alt me-1 text-primary"></i>
                            Documentos que o Cliente deve Enviar <span class="text-danger">*</span>
                        </label>
                        <div class="form-text mb-3">
                            Defina quais documentos o cliente precisará enviar quando este template for selecionado
                        </div>
                        
                        <div id="documentsContainer">
                            <div class="document-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label">Nome do Documento <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="documents[]" 
                                               placeholder="Ex: RG do Responsável Técnico" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Formato Aceito</label>
                                        <select class="form-select" name="doc_formats[]">
                                            <option value="PDF">PDF</option>
                                            <option value="PDF,JPG,PNG">PDF, JPG, PNG</option>
                                            <option value="PDF,DOC,DOCX">PDF, DOC, DOCX</option>
                                            <option value="PDF,DWG">PDF, DWG</option>
                                            <option value="Todos">Todos os formatos</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-8">
                                        <label class="form-label">Descrição/Instrução</label>
                                        <input type="text" class="form-control" name="doc_descriptions[]" 
                                               placeholder="Ex: Documento com foto legível, frente e verso">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Tamanho Máx.</label>
                                        <select class="form-select" name="doc_max_sizes[]">
                                            <option value="5MB">5MB</option>
                                            <option value="10MB" selected>10MB</option>
                                            <option value="20MB">20MB</option>
                                            <option value="50MB">50MB</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label">Obrigatório</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="doc_required[]" value="1" checked>
                                            <label class="form-check-label">Sim</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-document" 
                                            onclick="removeDocument(this)" style="display: none;">
                                        <i class="fas fa-trash me-1"></i>Remover
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-outline-primary" onclick="addDocument()">
                            <i class="fas fa-plus me-1"></i>
                            Adicionar Outro Documento
                        </button>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Criar Template
                        </button>
                        <a href="/admin/templates" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-lightbulb me-1 text-warning"></i>
                        <strong>Dica:</strong> Use nomes descritivos para facilitar a identificação
                    </small>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-tags me-1 text-info"></i>
                        <strong>Categorias:</strong> Organize templates por área de engenharia
                    </small>
                </div>
                <div class="mb-0">
                    <small class="text-muted">
                        <i class="fas fa-toggle-on me-1 text-success"></i>
                        <strong>Status:</strong> Apenas templates ativos aparecem na criação de projetos
                    </small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-list me-1"></i>
                    Exemplos de Templates
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small text-muted mb-0">
                    <li><i class="fas fa-file-alt me-1"></i> Memorial de Cálculo Estrutural</li>
                    <li><i class="fas fa-file-alt me-1"></i> Projeto de Fundações</li>
                    <li><i class="fas fa-file-alt me-1"></i> Planta Baixa Arquitetônica</li>
                    <li><i class="fas fa-file-alt me-1"></i> Projeto Hidrossanitário</li>
                    <li><i class="fas fa-file-alt me-1"></i> Projeto Elétrico Predial</li>
                    <li><i class="fas fa-file-alt me-1"></i> Laudo Técnico</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
let documentCounter = 1;

function addDocument() {
    documentCounter++;
    const container = document.getElementById('documentsContainer');
    const newItem = document.createElement('div');
    newItem.className = 'document-item border rounded p-3 mb-3';
    newItem.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <label class="form-label">Nome do Documento <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="documents[]" 
                       placeholder="Ex: Comprovante de Endereço" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Formato Aceito</label>
                <select class="form-select" name="doc_formats[]">
                    <option value="PDF">PDF</option>
                    <option value="PDF,JPG,PNG">PDF, JPG, PNG</option>
                    <option value="PDF,DOC,DOCX">PDF, DOC, DOCX</option>
                    <option value="PDF,DWG">PDF, DWG</option>
                    <option value="Todos">Todos os formatos</option>
                </select>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-8">
                <label class="form-label">Descrição/Instrução</label>
                <input type="text" class="form-control" name="doc_descriptions[]" 
                       placeholder="Ex: Documento atualizado, máximo 3 meses">
            </div>
            <div class="col-md-2">
                <label class="form-label">Tamanho Máx.</label>
                <select class="form-select" name="doc_max_sizes[]">
                    <option value="5MB">5MB</option>
                    <option value="10MB" selected>10MB</option>
                    <option value="20MB">20MB</option>
                    <option value="50MB">50MB</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Obrigatório</label>
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="doc_required[]" value="${documentCounter}" checked>
                    <label class="form-check-label">Sim</label>
                </div>
            </div>
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-danger remove-document" 
                    onclick="removeDocument(this)">
                <i class="fas fa-trash me-1"></i>Remover
            </button>
        </div>
    `;
    
    container.appendChild(newItem);
    updateRemoveButtons();
}

function removeDocument(button) {
    button.closest('.document-item').remove();
    updateRemoveButtons();
}

function updateRemoveButtons() {
    const items = document.querySelectorAll('.document-item');
    items.forEach((item, index) => {
        const removeBtn = item.querySelector('.remove-document');
        if (items.length > 1) {
            removeBtn.style.display = 'inline-block';
        } else {
            removeBtn.style.display = 'none';
        }
    });
}

// Validação de formulário
document.addEventListener('DOMContentLoaded', function() {
    updateRemoveButtons();
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
