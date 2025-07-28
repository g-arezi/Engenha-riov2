<?php
echo "<h2>Configurações de Upload do PHP</h2>";

echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>max_file_uploads:</strong> " . ini_get('max_file_uploads') . "</p>";
echo "<p><strong>file_uploads:</strong> " . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</p>";
echo "<p><strong>upload_tmp_dir:</strong> " . (ini_get('upload_tmp_dir') ?: 'Default') . "</p>";
echo "<p><strong>max_execution_time:</strong> " . ini_get('max_execution_time') . " seconds</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";

echo "<h3>Teste de Diretório de Upload</h3>";
$uploadDir = __DIR__ . '/uploads/';
echo "<p><strong>Diretório de upload:</strong> " . $uploadDir . "</p>";
echo "<p><strong>Diretório existe:</strong> " . (is_dir($uploadDir) ? 'Sim' : 'Não') . "</p>";
echo "<p><strong>Diretório é gravável:</strong> " . (is_writable($uploadDir) ? 'Sim' : 'Não') . "</p>";

echo "<h3>Teste de Sessão</h3>";
session_start();
echo "<p><strong>Sessão iniciada:</strong> " . (session_status() === PHP_SESSION_ACTIVE ? 'Sim' : 'Não') . "</p>";
echo "<p><strong>Session ID:</strong> " . session_id() . "</p>";
echo "<p><strong>Dados da sessão:</strong></p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";
?>
