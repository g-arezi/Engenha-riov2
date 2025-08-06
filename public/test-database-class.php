<?php
require_once '../autoload.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');
echo "<h2>Teste da Classe Database</h2>";

try {
    $database = new Database();
    echo "<p>✅ Database instanciada com sucesso</p>";

    $projectId = 'proj_1753892536_9899';
    
    // Buscar projeto
    echo "<h3>1. Buscando projeto:</h3>";
    $project = $database->find('projects', $projectId);
    
    if ($project) {
        echo "<p>✅ Projeto encontrado</p>";
        echo "<p>Stage atual: " . $project['workflow_stage'] . "</p>";
        echo "<p>Updated_at atual: " . $project['updated_at'] . "</p>";
    } else {
        echo "<p>❌ Projeto não encontrado</p>";
        exit;
    }

    // Atualizar projeto
    echo "<h3>2. Atualizando projeto:</h3>";
    $currentStage = (int)$project['workflow_stage'];
    $nextStage = $currentStage + 1;
    
    $updateData = [
        'workflow_stage' => $nextStage
    ];

    echo "<p>Tentando atualizar stage de $currentStage para $nextStage</p>";
    
    $result = $database->update('projects', $projectId, $updateData);
    
    if ($result) {
        echo "<p>✅ Método update retornou true</p>";
    } else {
        echo "<p>❌ Método update retornou false</p>";
    }

    // Verificar resultado
    echo "<h3>3. Verificando resultado:</h3>";
    $updatedProject = $database->find('projects', $projectId);
    
    if ($updatedProject) {
        echo "<p>Stage após update: " . $updatedProject['workflow_stage'] . "</p>";
        echo "<p>Updated_at após update: " . $updatedProject['updated_at'] . "</p>";
        
        if ((int)$updatedProject['workflow_stage'] === $nextStage) {
            echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO: Stage foi atualizado!</p>";
        } else {
            echo "<p style='color: red; font-weight: bold;'>❌ FALHA: Stage não foi atualizado!</p>";
        }
    }

    echo "<h3>4. Verificando logs:</h3>";
    $logFile = '../database-debug.log';
    if (file_exists($logFile)) {
        echo "<p>✅ Arquivo de log existe</p>";
        echo "<pre>" . file_get_contents($logFile) . "</pre>";
    } else {
        echo "<p>❌ Arquivo de log não existe: $logFile</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>Erro: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
