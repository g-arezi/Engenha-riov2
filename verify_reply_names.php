<?php
// Script para verificar e corrigir os nomes de usuários nas respostas de tickets

require_once __DIR__ . '/autoload.php';

// Função para log
function logMessage($message) {
    echo $message . "\n";
    error_log($message);
}

// Carregar dados dos usuários
logMessage("Carregando dados de usuários...");
$usersData = json_decode(file_get_contents(__DIR__ . '/data/users.json'), true);
if (!$usersData) {
    logMessage("Erro: Não foi possível carregar os dados de usuários.");
    exit(1);
}

// Mapear IDs de usuários para nomes
$userNames = [];
foreach ($usersData as $userId => $userData) {
    if (isset($userData['id'])) {
        $userNames[$userData['id']] = $userData['name'];
    } else {
        $userNames[$userId] = $userData['name'];
    }
}

logMessage("Mapeamento de usuários concluído. Total: " . count($userNames));
foreach ($userNames as $id => $name) {
    logMessage("ID: $id -> Nome: $name");
}

// Carregar respostas de tickets
logMessage("\nCarregando respostas de tickets...");
$repliesFile = __DIR__ . '/data/support_replies.json';
$repliesData = json_decode(file_get_contents($repliesFile), true);
if (!$repliesData) {
    logMessage("Erro: Não foi possível carregar as respostas de tickets.");
    exit(1);
}

// Verificar e adicionar nomes às respostas se necessário
$updatedCount = 0;
foreach ($repliesData as $replyId => &$reply) {
    $userId = $reply['user_id'];
    if (!isset($reply['user_name']) || empty($reply['user_name'])) {
        if (isset($userNames[$userId])) {
            $reply['user_name'] = $userNames[$userId];
            logMessage("Adicionado nome '{$userNames[$userId]}' à resposta $replyId para usuário $userId");
            $updatedCount++;
        } else {
            logMessage("AVISO: Não foi possível encontrar nome para o usuário $userId na resposta $replyId");
        }
    } else {
        logMessage("Resposta $replyId já possui nome de usuário: {$reply['user_name']}");
    }
}

// Salvar respostas atualizadas
if ($updatedCount > 0) {
    file_put_contents($repliesFile, json_encode($repliesData, JSON_PRETTY_PRINT));
    logMessage("\nRespostas atualizadas com sucesso. Total: $updatedCount");
} else {
    logMessage("\nNenhuma resposta precisou ser atualizada.");
}

logMessage("\nVerificação concluída!");
