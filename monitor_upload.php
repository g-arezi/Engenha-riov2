<?php
/**
 * Monitor de Upload - Engenha Rio
 * Monitora e debug problemas de upload
 */

// Configurar log de erros
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload_debug.log');

echo "=== MONITOR DE UPLOAD ===\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

// Verificar configurações PHP
echo "CONFIGURAÇÕES PHP:\n";
echo "- file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "\n";
echo "- upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "- post_max_size: " . ini_get('post_max_size') . "\n";
echo "- max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "- memory_limit: " . ini_get('memory_limit') . "\n";
echo "- tmp_dir: " . (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) . "\n\n";

// Verificar diretórios
$uploadDir = __DIR__ . '/public/uploads/';
echo "DIRETÓRIOS:\n";
echo "- Upload dir: {$uploadDir}\n";
echo "- Existe: " . (is_dir($uploadDir) ? 'SIM' : 'NÃO') . "\n";
echo "- Gravável: " . (is_writable($uploadDir) ? 'SIM' : 'NÃO') . "\n";
echo "- Permissões: " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "\n\n";

// Verificar arquivos existentes
echo "ARQUIVOS EXISTENTES:\n";
if (is_dir($uploadDir)) {
    $files = scandir($uploadDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filePath = $uploadDir . $file;
            echo "- {$file} (" . filesize($filePath) . " bytes, " . date('Y-m-d H:i:s', filemtime($filePath)) . ")\n";
        }
    }
} else {
    echo "- Diretório não existe\n";
}

echo "\n";

// Verificar se há logs de upload
$logFile = __DIR__ . '/upload_debug.log';
if (file_exists($logFile)) {
    echo "LOGS DE UPLOAD:\n";
    $logs = file_get_contents($logFile);
    echo $logs . "\n";
} else {
    echo "Nenhum log de upload encontrado.\n";
}

echo "=== FIM DO MONITOR ===\n";
