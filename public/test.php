<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste Documentos do Projeto - Engenha Rio</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
        .section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .document-item { padding: 10px; margin: 5px 0; background: #f8f9fa; border-radius: 4px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .info { background: #e2e3e5; border-color: #d6d8db; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Teste: Documentos do Projeto</h1>
    
    <div class="section">
        <h3>Documentos na tabela 'documents' com project_id</h3>
        <?php
        require_once '../autoload.php';
        use App\Core\Database;
        
        $db = new Database();
        $documents = $db->findAll('documents');
        
        echo "<p>Total de documentos encontrados: " . count($documents) . "</p>";
        
        foreach ($documents as $doc) {
            if (isset($doc['project_id']) && !empty($doc['project_id'])) {
                echo "<div class='document-item success'>";
                echo "<strong>" . htmlspecialchars($doc['name']) . "</strong><br>";
                echo "ID: " . htmlspecialchars($doc['id']) . "<br>";
                echo "Projeto: " . htmlspecialchars($doc['project_id']) . "<br>";
                echo "Arquivo: " . htmlspecialchars($doc['filename']) . "<br>";
                echo "Upload: " . htmlspecialchars($doc['uploaded_at'] ?? $doc['created_at']) . "<br>";
                echo "</div>";
            }
        }
        ?>
    </div>
    
    <div class="section">
        <h3>Documentos na tabela 'project_documents'</h3>
        <?php
        $projectDocs = $db->findAll('project_documents');
        echo "<p>Total de documentos de projeto: " . count($projectDocs) . "</p>";
        
        foreach ($projectDocs as $doc) {
            echo "<div class='document-item info'>";
            echo "<strong>" . htmlspecialchars($doc['name']) . "</strong><br>";
            echo "ID: " . htmlspecialchars($doc['id']) . "<br>";
            echo "Projeto: " . htmlspecialchars($doc['project_id']) . "<br>";
            echo "Tipo: " . htmlspecialchars($doc['document_type'] ?? 'N/A') . "<br>";
            echo "</div>";
        }
        ?>
    </div>
    
    <div class="section">
        <h3>Teste da busca combinada (como no ProjectController)</h3>
        <?php
        $projectId = 'projeto_1';
        
        // Simular o que faz o ProjectController
        $projectDocuments = $db->findAll('project_documents', ['project_id' => $projectId]);
        $generalDocuments = $db->findAll('documents', ['project_id' => $projectId]);
        $allDocuments = array_merge($projectDocuments, $generalDocuments);
        
        echo "<p>Documentos para projeto '{$projectId}': " . count($allDocuments) . "</p>";
        echo "<p>- project_documents: " . count($projectDocuments) . "</p>";
        echo "<p>- documents: " . count($generalDocuments) . "</p>";
        
        foreach ($allDocuments as $doc) {
            echo "<div class='document-item'>";
            echo "<strong>" . htmlspecialchars($doc['name']) . "</strong><br>";
            echo "Fonte: " . (isset($doc['document_type']) ? 'project_documents' : 'documents') . "<br>";
            echo "Arquivo: " . htmlspecialchars($doc['filename'] ?? 'N/A') . "<br>";
            echo "</div>";
        }
        ?>
    </div>
    
    <div class="section">
        <h3>Debug: JSON dos documentos recentes</h3>
        <?php
        $recentDocs = array_slice($documents, -3, 3, true);
        echo "<pre>" . json_encode($recentDocs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        ?>
    </div>
    
    <p><a href="/projects/projeto_1">Ver Projeto 1</a> | <a href="/documents">Ver Documentos</a></p>
</body>
</html>
