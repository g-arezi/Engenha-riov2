<?php
// Direct route for ticket replies with fewer dependencies
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

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /support');
    exit;
}

// Get the ticket ID from the URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = 'ID do ticket não fornecido.';
    header('Location: /support');
    exit;
}

$db = new Database();
$ticket = $db->find('support_tickets', $id);

if (!$ticket) {
    $_SESSION['error'] = 'Ticket não encontrado.';
    header('Location: /support');
    exit;
}

// Check if user has permission to reply to this ticket
if (!Auth::hasPermission('admin.view') && 
    !Auth::hasPermission('support.manage') && 
    $ticket['user_id'] !== Auth::id()) {
    $_SESSION['error'] = 'Você não tem permissão para responder a este ticket.';
    header('Location: /support');
    exit;
}

// Store reply data
$data = [
    'ticket_id' => $id,
    'message' => $_POST['message'] ?? '',
    'user_id' => Auth::id(),
    'is_staff' => Auth::hasPermission('support.manage')
];

$db->insert('support_replies', $data);

// Update ticket status if provided
if (isset($_POST['status']) && !empty($_POST['status'])) {
    $db->update('support_tickets', $id, ['status' => $_POST['status']]);
}

$_SESSION['success'] = 'Resposta enviada com sucesso!';
header('Location: /view-ticket.php?id=' . $id);
exit;
