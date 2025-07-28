<?php
/**
 * Teste de Conexões - Engenha Rio
 * Verifica se todas as rotas e controllers estão funcionando
 */

require_once 'autoload.php';

use App\Core\Database;
use App\Core\Auth;
use App\Controllers\ProjectController;
use App\Controllers\DocumentWorkflowController;

echo "=== TESTE DE CONEXÕES ENGENHA RIO ===\n\n";

// 1. Teste Database
try {
    $db = new Database();
    echo "✅ Database: OK\n";
} catch (Exception $e) {
    echo "❌ Database: ERRO - " . $e->getMessage() . "\n";
}

// 2. Teste Auth
try {
    $auth = new Auth();
    echo "✅ Auth: OK\n";
} catch (Exception $e) {
    echo "❌ Auth: ERRO - " . $e->getMessage() . "\n";
}

// 3. Teste ProjectController
try {
    $projectController = new ProjectController();
    
    // Verificar se método updateStatus existe
    if (method_exists($projectController, 'updateStatus')) {
        echo "✅ ProjectController::updateStatus: OK\n";
    } else {
        echo "❌ ProjectController::updateStatus: MÉTODO NÃO EXISTE\n";
    }
    
    // Verificar outros métodos críticos
    $methods = ['index', 'show', 'store', 'update', 'delete'];
    foreach ($methods as $method) {
        if (method_exists($projectController, $method)) {
            echo "✅ ProjectController::{$method}: OK\n";
        } else {
            echo "❌ ProjectController::{$method}: MÉTODO NÃO EXISTE\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ProjectController: ERRO - " . $e->getMessage() . "\n";
}

// 4. Teste DocumentWorkflowController
try {
    $workflowController = new DocumentWorkflowController();
    
    $workflowMethods = ['updateStage', 'updateStatus', 'advance', 'revert', 'finalize'];
    foreach ($workflowMethods as $method) {
        if (method_exists($workflowController, $method)) {
            echo "✅ DocumentWorkflowController::{$method}: OK\n";
        } else {
            echo "❌ DocumentWorkflowController::{$method}: MÉTODO NÃO EXISTE\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ DocumentWorkflowController: ERRO - " . $e->getMessage() . "\n";
}

// 5. Teste arquivos de dados
$dataFiles = [
    'data/users.json',
    'data/projects.json',
    'data/documents.json',
    'data/notifications.json',
    'data/support_tickets.json'
];

foreach ($dataFiles as $file) {
    if (file_exists($file) && is_readable($file)) {
        $data = json_decode(file_get_contents($file), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ {$file}: OK (" . count($data) . " registros)\n";
        } else {
            echo "❌ {$file}: JSON INVÁLIDO\n";
        }
    } else {
        echo "❌ {$file}: ARQUIVO NÃO ENCONTRADO OU ILEGÍVEL\n";
    }
}

echo "\n=== FIM DO TESTE ===\n";
