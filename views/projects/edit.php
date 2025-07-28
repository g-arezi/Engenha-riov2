<?php
use App\Core\Auth;

$title = 'Editar Projeto - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="projects-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Editar Projeto</h1>
            <p class="text-muted mb-0">Editar informações do projeto: <?= htmlspecialchars($project['name'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="/projects/<?= $project['id'] ?>" class="btn btn-outline-primary">
                <i class="fas fa-eye me-1"></i>
                Ver Projeto
            </a>
            <a href="/projects" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/projects/<?= $project['id'] ?>/edit" data-validate="true">
                    <!-- Título do Projeto -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-project-diagram me-1 text-primary"></i>
                            Título do Projeto <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?= htmlspecialchars($project['name'] ?? '') ?>"
                               placeholder="Ex: Projeto Residencial João Silva" required>
                    </div>
                    
                    <!-- Descrição do Projeto -->
                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1 text-info"></i>
                            Descrição do Projeto <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4" 
                                  placeholder="Descreva detalhadamente o projeto, incluindo objetivos, especificações e requisitos..." required><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
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
                                    <option value="residencial" <?= ($project['project_type'] ?? '') === 'residencial' ? 'selected' : '' ?>>Projeto Residencial</option>
                                    <option value="comercial" <?= ($project['project_type'] ?? '') === 'comercial' ? 'selected' : '' ?>>Projeto Comercial</option>
                                    <option value="industrial" <?= ($project['project_type'] ?? '') === 'industrial' ? 'selected' : '' ?>>Projeto Industrial</option>
                                    <option value="infraestrutura" <?= ($project['project_type'] ?? '') === 'infraestrutura' ? 'selected' : '' ?>>Infraestrutura</option>
                                    <option value="reforma" <?= ($project['project_type'] ?? '') === 'reforma' ? 'selected' : '' ?>>Reforma/Retrofit</option>
                                    <option value="consultoria" <?= ($project['project_type'] ?? '') === 'consultoria' ? 'selected' : '' ?>>Consultoria Técnica</option>
                                    <option value="laudo" <?= ($project['project_type'] ?? '') === 'laudo' ? 'selected' : '' ?>>Laudo Técnico</option>
                                    <option value="outros" <?= ($project['project_type'] ?? '') === 'outros' ? 'selected' : '' ?>>Outros</option>
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
                                            <?= ($project['document_template'] ?? '') === $template['id'] ? 'selected' : '' ?>
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
                            <option value="<?= $client['id'] ?>" <?= ($project['client_id'] ?? '') === $client['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($client['name']) ?>
                            </option>
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
                                       value="<?= $project['deadline'] ?? '' ?>"
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
                                    <option value="<?= $analyst['id'] ?>" <?= ($project['analyst_id'] ?? '') === $analyst['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($analyst['name']) ?>
                                    </option>
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
                            <option value="baixa" <?= ($project['priority'] ?? 'media') === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                            <option value="media" <?= ($project['priority'] ?? 'media') === 'media' ? 'selected' : '' ?>>Normal</option>
                            <option value="alta" <?= ($project['priority'] ?? 'media') === 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="urgente" <?= ($project['priority'] ?? 'media') === 'urgente' ? 'selected' : '' ?>>Urgente</option>
                        </select>
                    </div>

                    <!-- Status do Projeto -->
                    <div class="mb-3">
                        <label for="status" class="form-label">
                            <i class="fas fa-info-circle me-1 text-info"></i>
                            Status do Projeto
                        </label>
                        <select class="form-select" id="status" name="status">
                            <option value="pendente" <?= ($project['status'] ?? 'pendente') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="ativo" <?= ($project['status'] ?? 'pendente') === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                            <option value="em_analise" <?= ($project['status'] ?? 'pendente') === 'em_analise' ? 'selected' : '' ?>>Em Análise</option>
                            <option value="aguardando_cliente" <?= ($project['status'] ?? 'pendente') === 'aguardando_cliente' ? 'selected' : '' ?>>Aguardando Cliente</option>
                            <option value="concluido" <?= ($project['status'] ?? 'pendente') === 'concluido' ? 'selected' : '' ?>>Concluído</option>
                            <option value="cancelado" <?= ($project['status'] ?? 'pendente') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>
                    
                    <!-- Informações Importantes -->
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle me-1"></i> Informações Importantes</h6>
                        <ul class="mb-0 small">
                            <li><strong>Alterações no template:</strong> Podem afetar os documentos já enviados pelo cliente</li>
                            <li><strong>Mudança de cliente:</strong> Transfere a responsabilidade do projeto</li>
                            <li><strong>Alteração de analista:</strong> O novo analista será notificado</li>
                            <li><strong>Status do projeto:</strong> Afeta a visibilidade e ações disponíveis</li>
                        </ul>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Salvar Alterações
                        </button>
                        <a href="/projects/<?= $project['id'] ?>" class="btn btn-outline-secondary">
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
                    <i class="fas fa-info-circle me-1"></i>
                    Informações do Projeto
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted d-block"><strong>ID do Projeto:</strong></small>
                    <small><?= htmlspecialchars($project['id'] ?? '') ?></small>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block"><strong>Criado em:</strong></small>
                    <small><?= date('d/m/Y H:i', strtotime($project['created_at'] ?? '')) ?></small>
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block"><strong>Última atualização:</strong></small>
                    <small><?= date('d/m/Y H:i', strtotime($project['updated_at'] ?? '')) ?></small>
                </div>
                <div class="mb-0">
                    <small class="text-muted d-block"><strong>Criado por:</strong></small>
                    <small><?= htmlspecialchars($project['created_by'] ?? '') ?></small>
                </div>
            </div>
        </div>
        
        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-lightbulb me-1"></i>
                    Dicas
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        Alterações em campos obrigatórios podem afetar o fluxo do projeto
                    </small>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-user me-1 text-info"></i>
                        O analista será notificado sobre mudanças importantes
                    </small>
                </div>
                <div class="mb-3">
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1 text-warning"></i>
                        Alterar o prazo notifica automaticamente o cliente
                    </small>
                </div>
                <div class="mb-0">
                    <small class="text-muted">
                        <i class="fas fa-file-alt me-1 text-success"></i>
                        Mudanças no template podem requerer novos documentos
                    </small>
                </div>
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
    
    // Mostrar descrição inicial se já houver template selecionado
    showTemplateDescription();
    
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
                alert('Por favor, preencha todos os campos obrigatórios.');
            }
        });
    }
});
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
