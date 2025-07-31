<?php
/**
 * Arquivo para debugar as chamadas de API relacionadas ao workflow
 */

require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Verificar autenticação
if (!Auth::check()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado']);
    exit;
}

// Log de acesso
$logFile = __DIR__ . '/../logs/workflow-debug.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}

// Dados para o log
$timestamp = date('Y-m-d H:i:s');
$user = Auth::user();
$userId = Auth::id();
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$payload = file_get_contents('php://input');
$headers = getallheaders();

// Formatar o log
$log = "[$timestamp] User: $userId ({$user['name']})\n";
$log .= "Method: $method\n";
$log .= "URI: $uri\n";
$log .= "Headers: " . json_encode($headers) . "\n";
$log .= "Payload: $payload\n";
$log .= "-----------------------------------\n";

// Escrever no arquivo de log
file_put_contents($logFile, $log, FILE_APPEND);

// Testar o endpoint de avançar etapa
if ($_GET['action'] === 'test-advance') {
    $projectId = $_GET['project_id'] ?? null;
    
    if (!$projectId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID do projeto é obrigatório']);
        exit;
    }
    
    $db = new Database();
    $project = $db->find('projects', $projectId);
    
    if (!$project) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Projeto não encontrado']);
        exit;
    }
    
    $currentStage = $project['workflow_stage'] ?? 1;
    
    if ($currentStage >= 5) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Projeto já está na etapa final']);
        exit;
    }
    
    $newStage = $currentStage + 1;
    $updateData = [
        'workflow_stage' => $newStage,
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Atualizar o projeto
    $updated = $db->update('projects', $projectId, $updateData);
    
    if ($updated) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true, 
            'message' => 'Projeto avançado para a próxima etapa!',
            'old_stage' => $currentStage,
            'new_stage' => $newStage
        ]);
    } else {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar etapa do projeto']);
    }
    exit;
}

// Retornar informações para debug
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Debug info captured',
    'timestamp' => $timestamp,
    'user' => [
        'id' => $userId,
        'name' => $user['name'],
        'role' => $user['role']
    ],
    'request' => [
        'method' => $method,
        'uri' => $uri,
        'headers' => $headers,
        'payload' => json_decode($payload, true) ?? $payload
    ]
]);
?>
