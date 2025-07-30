<?php
// Script para limpar todos os tickets de suporte e criar novos para teste
require_once __DIR__ . '/autoload.php';

use App\Core\Database;

// Inicializar banco de dados
$db = new Database();

// Limpar tickets existentes
$emptyTickets = [];
$ticketsFile = __DIR__ . '/data/support_tickets.json';
file_put_contents($ticketsFile, json_encode($emptyTickets, JSON_PRETTY_PRINT));
echo "Todos os tickets foram removidos.\n";

// Limpar respostas de tickets
$emptyReplies = [];
$repliesFile = __DIR__ . '/data/support_replies.json';
file_put_contents($repliesFile, json_encode($emptyReplies, JSON_PRETTY_PRINT));
echo "Todas as respostas de tickets foram removidas.\n";

// Criar tickets de teste
$tickets = [
    [
        "subject" => "Ticket de teste Admin",
        "description" => "Este é um ticket criado pelo administrador",
        "priority" => "media",
        "status" => "aberto",
        "user_id" => "admin"
    ],
    [
        "subject" => "Problema urgente - Cliente",
        "description" => "Preciso de ajuda urgente com um problema no sistema",
        "priority" => "alta",
        "status" => "aberto",
        "user_id" => "6887cdaf0cf3a8.51328972" // usuarioteste2 (cliente)
    ],
    [
        "subject" => "Relatório com falhas - Analista",
        "description" => "Estou tendo problemas ao gerar relatórios no sistema",
        "priority" => "media",
        "status" => "aberto",
        "user_id" => "6887cb7c384b75.22527396" // usuarioteste (analista)
    ],
    [
        "subject" => "Ticket fechado - Admin",
        "description" => "Este é um ticket resolvido",
        "priority" => "baixa",
        "status" => "fechado",
        "user_id" => "admin"
    ]
];

// Inserir os tickets
foreach ($tickets as $ticket) {
    $db->insert('support_tickets', $ticket);
    echo "Ticket criado: {$ticket['subject']} (Usuário: {$ticket['user_id']})\n";
}

echo "\nOperação concluída. Agora você pode testar o sistema com estes novos tickets.\n";
