<?php
// Página de teste para templates sem autenticação

$templatesFile = __DIR__ . '/../data/document_templates.json';
$templates = [];

if (file_exists($templatesFile)) {
    $content = file_get_contents($templatesFile);
    $templates = json_decode($content, true) ?? [];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Templates Test - Engenha Rio</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Templates Test Page</h1>
        <p class="text-muted">Esta é uma página de teste para verificar se os templates estão sendo carregados corretamente.</p>
        
        <div class="card">
            <div class="card-header">
                <h5>Templates Encontrados: <?= count($templates) ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($templates)): ?>
                    <p class="text-warning">Nenhum template encontrado.</p>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($templates as $id => $template): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title"><?= htmlspecialchars($template['name']) ?></h6>
                                    <p class="card-text small"><?= htmlspecialchars($template['description']) ?></p>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($template['category']) ?></span>
                                    <span class="badge bg-<?= $template['status'] === 'ativo' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($template['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-4">
            <a href="/admin/templates" class="btn btn-primary">Ir para Templates (Com Auth)</a>
            <a href="/admin" class="btn btn-secondary">Ir para Admin</a>
            <a href="/login" class="btn btn-outline-primary">Login</a>
        </div>
    </div>
</body>
</html>
