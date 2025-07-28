<?php
$title = 'Gerenciar Templates - Engenha Rio';
$showSidebar = true;
$showNavbar = true;

ob_start();
?>

<div class="admin-header">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Templates de Documentos</h1>
            <p class="text-muted mb-0">Gerencie os templates disponíveis para projetos</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/admin/templates/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>
                Novo Template
            </a>
            <a href="/admin" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Voltar
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-file-alt me-1"></i>
                    Templates Disponíveis (<?= count($templates) ?>)
                </h6>
            </div>
            <div class="card-body">
                <?php if (empty($templates)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Nenhum template encontrado</h5>
                    <p class="text-muted">Comece criando um novo template</p>
                    <a href="/admin/templates/create" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Criar Primeiro Template
                    </a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Template</th>
                                <th>Categoria</th>
                                <th>Documentos Requeridos</th>
                                <th>Status</th>
                                <th>Atualizado em</th>
                                <th width="120">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $template): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= htmlspecialchars($template['name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= htmlspecialchars($template['description']) ?></small>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $categories = [
                                        'documentacao_legal' => 'Documentação Legal',
                                        'engenharia_estrutural' => 'Engenharia Estrutural',
                                        'engenharia_arquitetonica' => 'Engenharia Arquitetônica',
                                        'engenharia_hidraulica' => 'Engenharia Hidráulica',
                                        'engenharia_eletrica' => 'Engenharia Elétrica',
                                        'engenharia_sanitaria' => 'Engenharia Sanitária',
                                        'outros' => 'Outros'
                                    ];
                                    $categoryName = $categories[$template['category']] ?? $template['category'];
                                    ?>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($categoryName) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($template['required_documents'])): ?>
                                        <div class="small">
                                            <strong><?= count($template['required_documents']) ?> documento(s):</strong>
                                            <ul class="list-unstyled mb-0 mt-1">
                                                <?php foreach (array_slice($template['required_documents'], 0, 3) as $doc): ?>
                                                <li>
                                                    <i class="fas fa-file-alt me-1 text-muted"></i>
                                                    <?= htmlspecialchars($doc['name']) ?>
                                                    <?php if ($doc['required'] ?? true): ?>
                                                    <span class="badge badge-sm bg-danger ms-1">Obrigatório</span>
                                                    <?php endif; ?>
                                                </li>
                                                <?php endforeach; ?>
                                                <?php if (count($template['required_documents']) > 3): ?>
                                                <li class="text-muted">
                                                    <small>+ <?= count($template['required_documents']) - 3 ?> mais...</small>
                                                </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-exclamation-triangle me-1 text-warning"></i>
                                            Nenhum documento definido
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($template['status'] === 'ativo'): ?>
                                    <span class="badge bg-success">Ativo</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($template['updated_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="/admin/templates/<?= $template['id'] ?>/edit" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger" 
                                                onclick="deleteTemplate('<?= $template['id'] ?>', '<?= htmlspecialchars($template['name']) ?>')"
                                                title="Excluir">
                                            <i class="fas fa-trash"></i>
                                        </button>
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
</div>

<script>
function deleteTemplate(id, name) {
    if (confirm(`Tem certeza que deseja excluir o template "${name}"?`)) {
        fetch(`/admin/templates/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erro ao excluir template: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao excluir template');
        });
    }
}
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/../layouts/app.php';
?>
