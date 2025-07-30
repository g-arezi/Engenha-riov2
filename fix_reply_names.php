<?php
// Script para corrigir nomes de usuários nas respostas de tickets

require_once __DIR__ . '/autoload.php';

// Carregar dados dos usuários
$usersData = [];
if (file_exists(__DIR__ . '/data/users.json')) {
    $usersData = json_decode(file_get_contents(__DIR__ . '/data/users.json'), true) ?: [];
}

echo "Carregados " . count($usersData) . " usuários.\n";

// Mapear IDs para nomes
$userMap = [];
foreach ($usersData as $key => $user) {
    if (isset($user['id'])) {
        $userMap[$user['id']] = $user['name'];
    } else {
        $userMap[$key] = $user['name'];
    }
}

echo "Mapeados " . count($userMap) . " IDs de usuários para nomes.\n";

// Carregar respostas de tickets
$repliesData = [];
if (file_exists(__DIR__ . '/data/support_replies.json')) {
    $repliesData = json_decode(file_get_contents(__DIR__ . '/data/support_replies.json'), true) ?: [];
}

echo "Carregadas " . count($repliesData) . " respostas de tickets.\n";

// Adicionar nome do usuário a cada resposta
$updatedCount = 0;
foreach ($repliesData as $id => &$reply) {
    $userId = $reply['user_id'];
    
    // Verificar se o nome do usuário já existe e está correto
    if (isset($reply['user_name']) && !empty($reply['user_name'])) {
        if ($userId === 'admin' && $reply['user_name'] !== 'Administrador') {
            $reply['user_name'] = 'Administrador';
            $updatedCount++;
            echo "Corrigido nome para admin: Administrador\n";
        } elseif (isset($userMap[$userId]) && $reply['user_name'] !== $userMap[$userId]) {
            $reply['user_name'] = $userMap[$userId];
            $updatedCount++;
            echo "Atualizado nome para $userId: {$userMap[$userId]}\n";
        }
    } else {
        // Adicionar o nome se não existir
        if (isset($userMap[$userId])) {
            $reply['user_name'] = $userMap[$userId];
            $updatedCount++;
            echo "Adicionado nome para $userId: {$userMap[$userId]}\n";
        } else {
            echo "AVISO: Usuário $userId não encontrado.\n";
        }
    }
}

// Salvar as alterações
if ($updatedCount > 0) {
    file_put_contents(__DIR__ . '/data/support_replies.json', json_encode($repliesData, JSON_PRETTY_PRINT));
    echo "\nAtualizadas $updatedCount respostas com nomes corretos de usuários.\n";
} else {
    echo "\nNenhuma resposta precisou ser atualizada.\n";
}

echo "Concluído!\n";
