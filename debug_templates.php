<?php
session_start();
require_once __DIR__ . '/autoload.php';

use App\Core\Auth;
use App\Controllers\TemplateController;

echo "=== DEBUG TEMPLATE CONTROLLER ===\n";

// Verificar se há usuário logado
if (Auth::check()) {
    $user = Auth::user();
    echo "✅ Usuário logado: " . $user['name'] . " (" . $user['role'] . ")\n";
    
    // Verificar permissões
    if (Auth::hasPermission('admin.manage')) {
        echo "✅ Permissão admin.manage: SIM\n";
    } else {
        echo "❌ Permissão admin.manage: NÃO\n";
    }
} else {
    echo "❌ Nenhum usuário logado\n";
}

// Verificar se o arquivo de templates existe
$templatesFile = __DIR__ . '/data/document_templates.json';
if (file_exists($templatesFile)) {
    echo "✅ Arquivo de templates existe\n";
    $content = file_get_contents($templatesFile);
    $templates = json_decode($content, true);
    echo "✅ Templates carregados: " . count($templates) . " encontrados\n";
} else {
    echo "❌ Arquivo de templates NÃO existe\n";
}

// Testar instanciação do controller
try {
    $controller = new TemplateController();
    echo "✅ TemplateController instanciado com sucesso\n";
} catch (Exception $e) {
    echo "❌ Erro ao instanciar TemplateController: " . $e->getMessage() . "\n";
}

echo "\n=== FIM DEBUG ===\n";
?>
