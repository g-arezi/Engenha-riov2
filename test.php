<?php

// Test file to verify system functionality
echo "🚀 Testando o Sistema Engenha Rio...\n\n";

// Test autoloader
try {
    require_once __DIR__ . '/autoload.php';
    echo "✅ Autoloader carregado com sucesso\n";
} catch (Exception $e) {
    echo "❌ Erro no autoloader: " . $e->getMessage() . "\n";
}

// Test class loading
try {
    $router = new App\Core\Router();
    echo "✅ Classe Router carregada com sucesso\n";
} catch (Exception $e) {
    echo "❌ Erro ao carregar Router: " . $e->getMessage() . "\n";
}

try {
    $db = new App\Core\Database();
    echo "✅ Classe Database carregada com sucesso\n";
} catch (Exception $e) {
    echo "❌ Erro ao carregar Database: " . $e->getMessage() . "\n";
}

try {
    $auth = new App\Core\Auth();
    echo "✅ Classe Auth carregada com sucesso\n";
} catch (Exception $e) {
    echo "❌ Erro ao carregar Auth: " . $e->getMessage() . "\n";
}

// Test data files
$dataFiles = [
    'users.json',
    'documents.json',
    'document_templates.json',
    'support_tickets.json'
];

foreach ($dataFiles as $file) {
    $path = __DIR__ . '/data/' . $file;
    if (file_exists($path)) {
        echo "✅ Arquivo {$file} existe\n";
    } else {
        echo "❌ Arquivo {$file} não encontrado\n";
    }
}

// Test directories
$directories = [
    'public/assets/css',
    'public/assets/js',
    'public/assets/images',
    'public/uploads'
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        echo "✅ Diretório {$dir} existe\n";
    } else {
        echo "❌ Diretório {$dir} não encontrado\n";
    }
}

echo "\n🎉 Teste concluído!\n";
echo "📍 Acesse: http://localhost:8000\n";
echo "👤 Login: admin@engenhario.com / password\n";
