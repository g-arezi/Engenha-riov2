<?php
use App\Core\Auth;

$title = 'Criar Projeto - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="projects-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Novo Projeto</h1>
            <p class="text-muted mb-0">Criar um novo projeto</p>
        </div>
        <a href="/projects" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Voltar
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/projects/create" data-validate="true">
                    <!-- Título do Projeto -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-project-diagram me-1 text-primary"></i>
                            Título do Projeto <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               placeholder="Ex: Projeto Residencial João Silva" required>
                    </div>
                    
                    <!-- Descrição do Projeto -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1 text-info"></i>
                            Descrição do Projeto <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Descreva detalhadamente o projeto, incluindo objetivos, especificações e requisitos..." required></textarea>
                    </div>
                    
                    <div class="row">
                        <!-- Tipo de Projeto -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="project_type" class="form-label">
                                    <i class="fas fa-cogs me-1 text-warning"></i>
                                    Tipo de Projeto <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="project_type" name="project_type" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="residencial">Projeto Residencial</option>
                                    <option value="comercial">Projeto Comercial</option>
                                    <option value="industrial">Projeto Industrial</option>
                                    <option value="infraestrutura">Infraestrutura</option>
                                    <option value="reforma">Reforma/Retrofit</option>
                                    <option value="consultoria">Consultoria Técnica</option>
                                    <option value="laudo">Laudo Técnico</option>
                                    <option value="outros">Outros</option>
                                </select>
                                <div class="form-text">Defina o tipo para melhor organização do projeto</div>
                            </div>
                        </div>
                        
                        <!-- Template de Documentos -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="document_template" class="form-label">
                                    <i class="fas fa-file-alt me-1 text-success"></i>
                                    Template de Documentos <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="document_template" name="document_template" required>
                                    <option value="">Selecione um template</option>
                                    <?php foreach ($templates ?? [] as $template): ?>
                                    <option value="<?= $template['id'] ?>" 
                                            data-category="<?= $template['category'] ?>"
                                            data-description="<?= htmlspecialchars($template['description']) ?>">
                                        <?= htmlspecialchars($template['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text">Define quais documentos o cliente deve enviar (obrigatório)</div>
                                <div id="template-description" class="mt-2 p-2 bg-light rounded d-none">
                                    <small class="text-muted"></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cliente Responsável -->
                    <div class="mb-3">
                        <label for="client_id" class="form-label">
                            <i class="fas fa-user me-1 text-primary"></i>
                            Cliente Responsável <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="client_id" name="client_id" required>
                            <option value="">Selecione o cliente responsável pelo projeto</option>
                            <?php foreach ($clients ?? [] as $client): ?>
                            <option value="<?= $client['id'] ?>"><?= htmlspecialchars($client['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Selecione o cliente que será responsável por este projeto</div>
                    </div>
                    
                    <div class="row">
                        <!-- Prazo de Entrega -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="deadline" class="form-label">
                                    <i class="fas fa-calendar me-1 text-warning"></i>
                                    Prazo de Entrega
                                </label>
                                <input type="date" class="form-control" id="deadline" name="deadline" 
                                       min="<?= date('Y-m-d') ?>">
                                <div class="form-text">Data limite para conclusão do projeto</div>
                            </div>
                        </div>
                        
                        <!-- Analista Responsável -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="analyst_id" class="form-label">
                                    <i class="fas fa-user-tie me-1 text-info"></i>
                                    Analista Responsável
                                </label>
                                <select class="form-select" id="analyst_id" name="analyst_id">
                                    <option value="">Selecionar mais tarde</option>
                                    <?php foreach ($analysts ?? [] as $analyst): ?>
                                    <option value="<?= $analyst['id'] ?>"><?= htmlspecialchars($analyst['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Prioridade -->
                    <div class="mb-3">
                        <label for="priority" class="form-label">
                            <i class="fas fa-exclamation-triangle me-1 text-danger"></i>
                            Prioridade
                        </label>
                        <select class="form-select" id="priority" name="priority">
                            <option value="baixa">Baixa</option>
                            <option value="media" selected>Normal</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    
                    <!-- Informações Importantes -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-1"></i> Informações Importantes</h6>
                        <ul class="mb-0 small">
                            <li><strong>Template obrigatório:</strong> Todo projeto deve ter um template de documentos definido</li>
                            <li><strong>Cliente obrigatório:</strong> Todo projeto deve ser vinculado a um cliente</li>
                            <li><strong>Após criar o projeto:</strong> O cliente poderá fazer upload apenas dos documentos definidos no template</li>
                            <li><strong>O analista será notificado:</strong> Por email sobre o novo projeto</li>
                            <li><strong>O cliente receberá atualizações:</strong> Sobre o progresso do projeto</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Criar Projeto
                        </button>
                        <a href="/projects" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>
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
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-1"></i>
                    Informações
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        Preencha os campos obrigatórios marcados com <span class="text-danger">*</span>
                    </small>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-user me-1 text-info"></i>
                        O analista responsável pode ser alterado posteriormente
                    </small>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1 text-warning"></i>
                        O prazo é opcional e pode ser definido conforme a necessidade
                    </small>
                </div>
                <div class="mb-0">
                    <small class="text-muted">
                        <i class="fas fa-file-alt me-1 text-success"></i>
                        O template é obrigatório e define exatamente quais documentos serão solicitados ao cliente
                    </small>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-cogs me-1"></i>
                    Tipos de Projeto
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled small text-muted mb-0">
                    <li><i class="fas fa-home me-1"></i> <strong>Residencial:</strong> Casas, apartamentos</li>
                    <li><i class="fas fa-building me-1"></i> <strong>Comercial:</strong> Lojas, escritórios</li>
                    <li><i class="fas fa-industry me-1"></i> <strong>Industrial:</strong> Fábricas, galpões</li>
                    <li><i class="fas fa-road me-1"></i> <strong>Infraestrutura:</strong> Pontes, vias</li>
                    <li><i class="fas fa-tools me-1"></i> <strong>Reforma:</strong> Modificações</li>
                    <li><i class="fas fa-clipboard-check me-1"></i> <strong>Consultoria:</strong> Assessoria técnica</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Mostrar descrição do template selecionado
function showTemplateDescription() {
    const templateSelect = document.getElementById('document_template');
    const descriptionDiv = document.getElementById('template-description');
    const descriptionText = descriptionDiv.querySelector('small');
    
    const selectedOption = templateSelect.options[templateSelect.selectedIndex];
    
    if (selectedOption && selectedOption.value && selectedOption.dataset.description) {
        descriptionText.textContent = selectedOption.dataset.description;
        descriptionDiv.classList.remove('d-none');
    } else {
        descriptionDiv.classList.add('d-none');
    }
}

// Event listener para mudanças no template
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('document_template');
    templateSelect.addEventListener('change', showTemplateDescription);
    
    // Validação de formulário aprimorada
    const form = document.querySelector('form[data-validate="true"]');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos obrigatórios, incluindo o template de documentos.');
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
