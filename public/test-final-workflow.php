<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Teste Final do Sistema de Workflow</h2>";

// Testar com vários cenários
$scenarios = [
    'Teste 1: Com sessão vazia' => [],
    'Teste 2: Com user_id na sessão' => ['user_id' => 'admin', 'user_role' => 'administrador'],
];

foreach ($scenarios as $scenarioName => $sessionData) {
    echo "<h3>$scenarioName</h3>";
    
    // Preparar dados para POST
    $postData = json_encode(['project_id' => 'proj_1753892536_9899']);
    
    // Configurar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000/advance-workflow.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . session_id());
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP Code:</strong> $httpCode</p>";
    echo "<p><strong>Response:</strong></p>";
    echo "<pre>$response</pre>";
    echo "<hr>";
}

// Verificar estado atual do projeto
echo "<h3>Estado Atual do Projeto:</h3>";
$jsonContent = file_get_contents('../data/projects.json');
$data = json_decode($jsonContent, true);
$project = $data['proj_1753892536_9899'] ?? null;

if ($project) {
    echo "<p><strong>workflow_stage:</strong> " . $project['workflow_stage'] . "</p>";
    echo "<p><strong>updated_at:</strong> " . $project['updated_at'] . "</p>";
    echo "<p><strong>status:</strong> " . $project['status'] . "</p>";
} else {
    echo "<p style='color: red;'>Projeto não encontrado</p>";
}

// Verificar logs da Database
$logFile = '../database-debug.log';
if (file_exists($logFile)) {
    echo "<h3>Últimos Logs da Database:</h3>";
    $logContent = file_get_contents($logFile);
    $logLines = explode("\n", $logContent);
    $lastLines = array_slice($logLines, -20);
    echo "<pre>" . implode("\n", $lastLines) . "</pre>";
}

echo "<h3>Instruções:</h3>";
echo "<p>1. Vá para a página do projeto: <a href='/documents/project/proj_1753892536_9899' target='_blank'>Abrir Projeto</a></p>";
echo "<p>2. Clique no botão 'Avançar Etapa'</p>";
echo "<p>3. Recarregue esta página para ver o resultado</p>";
?>
