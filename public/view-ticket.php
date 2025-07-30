<?php
// Direct route for tickets with fewer dependencies and complications
require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// Get the ticket ID from the URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = 'ID do ticket não fornecido.';
    header('Location: /support');
    exit;
}

// Create a database instance
$db = new Database();

// Try to find the ticket
$ticket = $db->find('support_tickets', $id);

if (!$ticket) {
    $_SESSION['error'] = 'Ticket não encontrado.';
    header('Location: /support');
    exit;
}

// Check if user has permission to view this ticket
if (!Auth::hasPermission('admin.view') && 
    !Auth::hasPermission('support.manage') && 
    $ticket['user_id'] !== Auth::id()) {
    $_SESSION['error'] = 'Você não tem permissão para visualizar este ticket.';
    header('Location: /support');
    exit;
}

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

// Include the view
require_once __DIR__ . '/../views/support/show.php';
