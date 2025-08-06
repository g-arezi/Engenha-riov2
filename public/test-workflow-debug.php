<?php
require_once '../autoload.php';

use Core\Database;

header('Content-Type: text/html');

// Criar arquivo de log específico
$logFile = '../debug.log';

function debugLog($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    echo "<p>[$timestamp] $message</p>\n";
}

debugLog("=== TESTE DE DEBUG WORKFLOW INICIADO ===");

$database = new Database();
$projectId = $_GET['project_id'] ?? 'proj_1753892536_9899';

debugLog("ID do projeto: $projectId");

// Buscar o projeto atual
$project = $database->find('projects', $projectId);

if (!$project) {
    debugLog("Projeto não encontrado: $projectId");
    echo json_encode(['error' => 'Projeto não encontrado']);
    exit;
}

debugLog("Projeto encontrado. Stage atual: " . $project['workflow_stage']);

// Calcular próximo stage
$currentStage = (int)$project['workflow_stage'];
$nextStage = $currentStage + 1;

debugLog("Stage atual: $currentStage, Próximo stage: $nextStage");

// Dados para atualização
$updateData = [
    'workflow_stage' => $nextStage,
    'updated_at' => date('Y-m-d H:i:s')
];

debugLog("Dados para atualização: " . json_encode($updateData));

// Tentar atualizar
try {
    $result = $database->update('projects', $projectId, $updateData);
    debugLog("Resultado da atualização: " . ($result ? 'true' : 'false'));
    
    // Verificar se a atualização funcionou
    $updatedProject = $database->find('projects', $projectId);
    debugLog("Projeto após atualização - Stage: " . $updatedProject['workflow_stage'] . ", Updated: " . $updatedProject['updated_at']);
    
    echo "<pre>" . json_encode([
        'success' => true,
        'currentStage' => $currentStage,
        'nextStage' => $nextStage,
        'updateResult' => $result,
        'projectAfterUpdate' => $updatedProject
    ], JSON_PRETTY_PRINT) . "</pre>";
    
} catch (Exception $e) {
    debugLog("Erro na atualização: " . $e->getMessage());
    echo "<p>Erro na atualização: " . $e->getMessage() . "</p>";
}

debugLog("=== TESTE DE DEBUG WORKFLOW FINALIZADO ===");
?>
