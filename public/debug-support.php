<?php
require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

// Get the ticket ID from the URL
$ticketId = $_GET['id'] ?? '688a5d447682c3.90147134';

// Create a database instance
$db = new Database();

// Try to find the ticket
$ticket = $db->find('support_tickets', $ticketId);

// Display the result
echo '<h1>Debug Support Ticket</h1>';
echo '<p>Looking for ticket ID: ' . htmlspecialchars($ticketId) . '</p>';

if ($ticket) {
    echo '<h2>Ticket found!</h2>';
    echo '<pre>';
    print_r($ticket);
    echo '</pre>';
} else {
    echo '<h2>Ticket not found!</h2>';
    
    // Display all tickets for debugging
    echo '<h3>All tickets in database:</h3>';
    $allTickets = $db->findAll('support_tickets');
    echo '<pre>';
    print_r($allTickets);
    echo '</pre>';
}
