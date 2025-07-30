<?php
require_once __DIR__ . '/../autoload.php';

use App\Core\Database;
use App\Core\Auth;

// Start session
session_start();

// Ensure user is logged in
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// Get the ticket ID from the URL
$ticketId = $_GET['id'] ?? null;

if (!$ticketId) {
    echo "No ticket ID provided";
    exit;
}

// Create a database instance
$db = new Database();

// Try to find the ticket
$ticket = $db->find('support_tickets', $ticketId);

// Display the result
echo '<h1>Support Ticket Test</h1>';
echo '<p>Looking for ticket ID: ' . htmlspecialchars($ticketId) . '</p>';

if ($ticket) {
    // Check if user has permission to view this ticket
    if (!Auth::hasPermission('admin.view') && 
        !Auth::hasPermission('support.manage') && 
        $ticket['user_id'] !== Auth::id()) {
        echo '<h2>Permission denied!</h2>';
        echo '<p>You do not have permission to view this ticket.</p>';
        exit;
    }

    echo '<h2>Ticket found!</h2>';
    echo '<pre>';
    print_r($ticket);
    echo '</pre>';
    
    // Get replies for this ticket
    $replies = [];
    if (file_exists(__DIR__ . '/../data/support_replies.json')) {
        $repliesData = json_decode(file_get_contents(__DIR__ . '/../data/support_replies.json'), true) ?: [];
        foreach ($repliesData as $reply) {
            if ($reply['ticket_id'] === $ticket['id']) {
                $replies[] = $reply;
            }
        }
    }
    
    echo '<h2>Replies:</h2>';
    if (empty($replies)) {
        echo '<p>No replies found.</p>';
    } else {
        echo '<pre>';
        print_r($replies);
        echo '</pre>';
    }
} else {
    echo '<h2>Ticket not found!</h2>';
    
    // Display all tickets for debugging
    echo '<h3>All tickets in database:</h3>';
    $allTickets = $db->findAll('support_tickets');
    echo '<pre>';
    print_r($allTickets);
    echo '</pre>';
}
