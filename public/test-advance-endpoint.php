<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Teste Direto do advance-workflow.php</h2>";

$url = 'http://localhost:8000/advance-workflow.php';
$data = json_encode(['project_id' => 'proj_1753892536_9899']);

// Usar cURL para fazer a requisição POST
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($data)
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h3>Resposta do endpoint:</h3>";
echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
echo "<p><strong>Response:</strong></p>";
echo "<pre>$response</pre>";

// Verificar o arquivo JSON
echo "<h3>Estado atual do projeto:</h3>";
$jsonContent = file_get_contents('../data/projects.json');
$data = json_decode($jsonContent, true);
$project = $data['proj_1753892536_9899'] ?? null;

if ($project) {
    echo "<p><strong>workflow_stage:</strong> " . $project['workflow_stage'] . "</p>";
    echo "<p><strong>updated_at:</strong> " . $project['updated_at'] . "</p>";
} else {
    echo "<p style='color: red;'>Projeto não encontrado</p>";
}

// Verificar logs se existirem
$logFile = '../database-debug.log';
if (file_exists($logFile)) {
    echo "<h3>Logs da Database:</h3>";
    echo "<pre>" . file_get_contents($logFile) . "</pre>";
} else {
    echo "<h3>Sem logs da Database (arquivo não existe)</h3>";
}
?>
