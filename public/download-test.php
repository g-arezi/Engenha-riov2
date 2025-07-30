<?php
// Script simples para testar download de arquivos

// Verificar se há algum problema com os cabeçalhos
header('Content-Description: File Transfer Test');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="test-download.pdf"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');

// Caminho para um arquivo de teste
$filePath = __DIR__ . '/uploads/projects/test.pdf';

// Se o arquivo não existir, criar um arquivo de teste
if (!file_exists($filePath)) {
    file_put_contents($filePath, 'Este é um arquivo de teste para download.');
}

// Verificar se o arquivo existe
if (file_exists($filePath)) {
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
} else {
    echo "Erro: Arquivo não encontrado - " . $filePath;
}
exit;
