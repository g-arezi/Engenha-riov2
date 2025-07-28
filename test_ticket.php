<?php
// Teste de criação de ticket
require_once __DIR__ . '/autoload.php';

use App\Core\Database;

$db = new Database();

$data = [
    'subject' => 'Teste via script',
    'description' => 'Este é um teste de criação de ticket via script PHP direto',
    'priority' => 'media',
    'status' => 'aberto',
    'user_id' => 'admin'
];

try {
    $id = $db->insert('support_tickets', $data);
    echo "Ticket criado com sucesso! ID: " . $id . "\n";
    
    // Verificar se foi salvo
    $tickets = $db->findAll('support_tickets');
    echo "Total de tickets: " . count($tickets) . "\n";
    
    foreach ($tickets as $ticket) {
        echo "- " . $ticket['subject'] . " (ID: " . $ticket['id'] . ")\n";
    }
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?>
