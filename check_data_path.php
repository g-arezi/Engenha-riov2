<?php
// Script para verificar o caminho do diretório de dados
require_once __DIR__ . '/autoload.php';

$config = require __DIR__ . '/config/app.php';
$dataPath = $config['database']['path'];

echo "Caminho de dados configurado: " . $dataPath . "\n";
echo "Caminho absoluto: " . realpath($dataPath) . "\n";

// Verificar se o diretório existe
if (is_dir($dataPath)) {
    echo "O diretório de dados existe.\n";
} else {
    echo "ERRO: O diretório de dados NÃO existe!\n";
}

// Listar arquivos no diretório de dados
if (is_dir($dataPath)) {
    $files = scandir($dataPath);
    echo "Arquivos no diretório:\n";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- " . $file . " (" . filesize($dataPath . $file) . " bytes)\n";
        }
    }
}

// Verificar especificamente o arquivo de tickets
$ticketFile = $dataPath . 'support_tickets.json';
if (file_exists($ticketFile)) {
    echo "\nArquivo de tickets existe: " . $ticketFile . "\n";
    $tickets = json_decode(file_get_contents($ticketFile), true);
    echo "Tickets encontrados: " . count($tickets) . "\n";
    echo "IDs: " . implode(", ", array_keys($tickets)) . "\n";
} else {
    echo "\nERRO: Arquivo de tickets NÃO existe: " . $ticketFile . "\n";
}

echo "\nFinalizando verificação.\n";
