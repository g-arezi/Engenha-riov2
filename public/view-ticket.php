<?php
// Direct route for tickets with fewer dependencies and complications
require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Debug helper function
function debugLog($message) {
    error_log("[VIEW-TICKET DEBUG] " . $message);
}

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
debugLog("Checking permissions for user: " . Auth::id());
debugLog("User role: " . Auth::role());
debugLog("Has admin.view permission: " . (Auth::hasPermission('admin.view') ? 'Yes' : 'No'));
debugLog("Has support.manage permission: " . (Auth::hasPermission('support.manage') ? 'Yes' : 'No'));
debugLog("Ticket owner: " . $ticket['user_id']);
debugLog("Matches current user: " . ($ticket['user_id'] === Auth::id() ? 'Yes' : 'No'));

if (!(Auth::hasPermission('admin.view') || 
     Auth::hasPermission('support.manage') || 
     $ticket['user_id'] === Auth::id())) {
    $_SESSION['error'] = 'Você não tem permissão para visualizar este ticket.';
    debugLog("Permission denied");
    header('Location: /support');
    exit;
}

debugLog("Permission granted");

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

// Get user information for display
$users = [];
if (file_exists(__DIR__ . '/../data/users.json')) {
    $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true) ?: [];
}

// Get creator name
$creatorName = $ticket['user_id'];
if (isset($users[$ticket['user_id']])) {
    $creatorName = $users[$ticket['user_id']]['name'];
} else {
    foreach ($users as $user) {
        if (isset($user['id']) && $user['id'] === $ticket['user_id']) {
            $creatorName = $user['name'];
            break;
        }
    }
}

// Add user names to replies - com debug para verificar o processo
foreach ($replies as &$reply) {
    $reply['user_name'] = $reply['user_id']; // Default to user_id if name not found
    
    // Debug info
    debugLog("Processing reply for user_id: " . $reply['user_id']);
    
    // First try direct access if user is stored with ID as key
    if (isset($users[$reply['user_id']])) {
        $reply['user_name'] = $users[$reply['user_id']]['name'];
        debugLog("Found user directly: " . $reply['user_name']);
    } else {
        // Search through all users to find a matching ID
        foreach ($users as $userKey => $user) {
            debugLog("Checking user: " . $userKey . " with ID: " . ($user['id'] ?? 'no-id'));
            if (isset($user['id']) && $user['id'] === $reply['user_id']) {
                $reply['user_name'] = $user['name'];
                debugLog("Found user by iteration: " . $reply['user_name']);
                break;
            }
        }
    }
    
    debugLog("Final user_name for reply: " . $reply['user_name']);
}

// Sort replies by creation date to ensure consistent display
usort($replies, function($a, $b) {
    return strtotime($a['created_at']) - strtotime($b['created_at']);
});

// Include the view
require_once __DIR__ . '/../views/support/show.php';
