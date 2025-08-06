<?php
// Teste direto da funcionalidade sem usar autoload
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Teste Direto de Atualização do Workflow</h2>";

// Caminho para o arquivo de projetos
$projectsFile = '../data/projects.json';

echo "<h3>1. Verificando se o arquivo existe:</h3>";
if (!file_exists($projectsFile)) {
    echo "<p style='color: red;'>Arquivo não encontrado: $projectsFile</p>";
    exit;
}
echo "<p style='color: green;'>Arquivo encontrado: $projectsFile</p>";

echo "<h3>2. Lendo arquivo atual:</h3>";
$jsonContent = file_get_contents($projectsFile);
if ($jsonContent === false) {
    echo "<p style='color: red;'>Erro ao ler arquivo</p>";
    exit;
}

$data = json_decode($jsonContent, true);
if ($data === null) {
    echo "<p style='color: red;'>Erro ao decodificar JSON: " . json_last_error_msg() . "</p>";
    exit;
}

echo "<p style='color: green;'>JSON decodificado com sucesso</p>";

$projectId = 'proj_1753892536_9899';

echo "<h3>3. Verificando projeto específico:</h3>";
if (!isset($data[$projectId])) {
    echo "<p style='color: red;'>Projeto $projectId não encontrado</p>";
    exit;
}

$project = $data[$projectId];
echo "<p>Projeto encontrado:</p>";
echo "<pre>" . json_encode($project, JSON_PRETTY_PRINT) . "</pre>";

$currentStage = (int)$project['workflow_stage'];
$nextStage = $currentStage + 1;

echo "<h3>4. Atualizando dados:</h3>";
echo "<p>Stage atual: $currentStage</p>";
echo "<p>Próximo stage: $nextStage</p>";

// Atualizar os dados
$data[$projectId]['workflow_stage'] = $nextStage;
$data[$projectId]['updated_at'] = date('Y-m-d H:i:s');

echo "<h3>5. Salvando arquivo:</h3>";
$newJsonContent = json_encode($data, JSON_PRETTY_PRINT);
$writeResult = file_put_contents($projectsFile, $newJsonContent);

if ($writeResult === false) {
    echo "<p style='color: red;'>Erro ao salvar arquivo</p>";
} else {
    echo "<p style='color: green;'>Arquivo salvo com sucesso. Bytes escritos: $writeResult</p>";
}

echo "<h3>6. Verificando se a mudança foi salva:</h3>";
$verifyContent = file_get_contents($projectsFile);
$verifyData = json_decode($verifyContent, true);
$verifiedProject = $verifyData[$projectId];

echo "<p>Stage após atualização: " . $verifiedProject['workflow_stage'] . "</p>";
echo "<p>Updated_at após atualização: " . $verifiedProject['updated_at'] . "</p>";

if ((int)$verifiedProject['workflow_stage'] === $nextStage) {
    echo "<p style='color: green; font-weight: bold;'>✅ SUCESSO: Stage foi atualizado corretamente!</p>";
} else {
    echo "<p style='color: red; font-weight: bold;'>❌ FALHA: Stage não foi atualizado!</p>";
}
?>
