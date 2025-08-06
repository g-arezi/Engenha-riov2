<?php
require_once '../autoload.php';

use App\Core\Auth;
use App\Core\Database;

session_start();

// Debug da sessão e autenticação
echo "<h3>Debug da Sessão e Autenticação</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session data: " . json_encode($_SESSION) . "</p>";

// Verificar autenticação
if (Auth::check()) {
    $user = Auth::user();
    echo "<p>Usuário autenticado: " . $user['name'] . " (ID: " . $user['id'] . ")</p>";
    echo "<p>Role do usuário: " . $user['role'] . "</p>";
    
    // Verificar permissão
    if (Auth::hasPermission('projects.manage_workflow')) {
        echo "<p>✅ Usuário tem permissão 'projects.manage_workflow'</p>";
    } else {
        echo "<p>❌ Usuário NÃO tem permissão 'projects.manage_workflow'</p>";
    }
} else {
    echo "<p>❌ Usuário NÃO está autenticado</p>";
}

// Testar chamada direta para o DocumentWorkflowController
echo "<h3>Teste do DocumentWorkflowController::advance()</h3>";

try {
    // Simular uma requisição POST JSON
    $_POST = [];
    file_put_contents('php://input', json_encode(['project_id' => 'proj_1753892536_9899']));
    
    // Headers necessários
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/json';
    $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    
    // Instanciar o controller
    $controller = new App\Controllers\DocumentWorkflowController();
    
    // Capturar output
    ob_start();
    $controller->advance();
    $output = ob_get_clean();
    
    echo "<p>Output do controller: " . htmlspecialchars($output) . "</p>";
    
} catch (Exception $e) {
    echo "<p>❌ Erro ao chamar controller: " . $e->getMessage() . "</p>";
    echo "<p>Stack trace: " . $e->getTraceAsString() . "</p>";
}
