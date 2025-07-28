<?php
require_once __DIR__ . '/autoload.php';

try {
    $controller = new \App\Controllers\TemplateController();
    echo "TemplateController criado com sucesso!\n";
    
    // Testar método index
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    echo "Método index executado com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro ao criar TemplateController: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>
