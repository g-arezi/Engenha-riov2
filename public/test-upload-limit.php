<?php
// Exibir todas as configurações relevantes de upload
echo "<h1>Verificação de Configurações de Upload</h1>";
echo "<pre>";
echo "Data e Hora: " . date('Y-m-d H:i:s') . "\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "</pre>";

// Formulário simples para teste de upload
echo "<h2>Formulário de Teste de Upload</h2>";
echo "<form action='' method='post' enctype='multipart/form-data'>";
echo "<input type='file' name='test_upload'>";
echo "<button type='submit'>Enviar</button>";
echo "</form>";

// Verificar se houve upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Resultado do Upload</h2>";
    echo "<pre>";
    if (isset($_FILES['test_upload'])) {
        print_r($_FILES['test_upload']);
    } else {
        echo "Nenhum arquivo enviado ou erro no upload";
    }
    echo "</pre>";
}
?>
