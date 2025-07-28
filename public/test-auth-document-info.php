<?php
session_start();

// Simular login
$_SESSION['user_id'] = 'admin';
$_SESSION['user_role'] = 'administrador';
$_SESSION['user_email'] = 'admin@test.com';

require_once __DIR__ . '/../autoload.php';

use App\Controllers\DocumentWorkflowController;

$controller = new DocumentWorkflowController();

// Capturar a saída
ob_start();

try {
    // Testar o método getDocumentInfo
    $controller->getDocumentInfo('doc_proj_1');
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

$output = ob_get_clean();

header('Content-Type: application/json');
echo $output;
?>
