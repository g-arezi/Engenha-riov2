<?php
// ticket-view.php - Simplified direct handler for viewing tickets
// No complex routing or authentication checks
// This version doesn't use Auth class to avoid errors

// Display all PHP errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Debug function
function debugLog($message) {
    error_log("[TICKET-VIEW] " . $message);
}

debugLog("Starting ticket view with session ID: " . session_id());
debugLog("Session data: " . json_encode($_SESSION));
// No complex routing or authentication checks
require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;

// Start session
session_start();

// Debug function
function debugLog($message) {
    error_log("[TICKET-VIEW] " . $message);
}

debugLog("Starting ticket view with session ID: " . session_id());
debugLog("Session data: " . json_encode($_SESSION));

// Setup user data for permission checks in the view
if (isset($_SESSION['user_id'])) {
    // Load user directly from the JSON file to avoid Auth class issues
    $usersFile = __DIR__ . '/../data/users.json';
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
        $currentUserId = $_SESSION['user_id'];
        
        // Set the user role and name in session if not already there
        if (isset($users[$currentUserId])) {
            $_SESSION['user_role'] = $users[$currentUserId]['role'] ?? 'cliente';
            $_SESSION['user_name'] = $users[$currentUserId]['name'] ?? $currentUserId;
            $_SESSION['user_avatar'] = $users[$currentUserId]['avatar'] ?? '';
        } else {
            foreach ($users as $user) {
                if (isset($user['id']) && $user['id'] === $currentUserId) {
                    $_SESSION['user_role'] = $user['role'] ?? 'cliente';
                    $_SESSION['user_name'] = $user['name'] ?? $currentUserId;
                    $_SESSION['user_avatar'] = $user['avatar'] ?? '';
                    break;
                }
            }
        }
    }
}

// Define permission helper functions needed by the template
function hasPermission($permission) {
    // Initialize permissions array
    $userPermissions = [];
    
    // Get user role from session
    $role = $_SESSION['user_role'] ?? 'cliente';
    
    // Define permissions based on role
    switch ($role) {
        case 'administrador':
            $userPermissions = [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.delete', 'documents.approve', 'documents.reject',
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'admin.view', 'admin.manage', 'admin.manage_permissions', 'admin.manage_documents', 'admin.manage_users',
                'support.view', 'support.manage'
            ];
            break;
        case 'analista':
        case 'coordenador':
            $userPermissions = [
                'dashboard.view',
                'projects.view', 'projects.create', 'projects.edit', 'projects.manage_workflow',
                'documents.view', 'documents.upload', 'documents.download', 'documents.approve', 'documents.reject',
                'admin.view', 'admin.manage_users',
                'support.view', 'support.manage'
            ];
            break;
        case 'cliente':
            $userPermissions = [
                'dashboard.view',
                'projects.view',
                'documents.view', 'documents.upload', 'documents.download',
                'support.view'
            ];
            break;
    }
    
    return in_array($permission, $userPermissions);
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

// Get the ticket ID from query parameter
$id = $_GET['id'] ?? null;

if (!$id) {
    debugLog("No ticket ID provided");
    $_SESSION['error'] = 'ID do ticket não fornecido.';
    header('Location: /support');
    exit;
}

debugLog("Looking for ticket with ID: " . $id);

// Load ticket data directly
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
if (!file_exists($ticketsFile)) {
    debugLog("Tickets file not found");
    $_SESSION['error'] = 'Arquivo de tickets não encontrado.';
    header('Location: /support');
    exit;
}

// Read ticket data
$ticketsData = json_decode(file_get_contents($ticketsFile), true) ?? [];
debugLog("Loaded " . count($ticketsData) . " tickets");

// Check if ticket exists
if (!isset($ticketsData[$id])) {
    debugLog("Ticket not found in data");
    $_SESSION['error'] = 'Ticket não encontrado.';
    header('Location: /support');
    exit;
}

$ticket = $ticketsData[$id];
debugLog("Found ticket: " . json_encode($ticket));

// Get replies for this ticket
$replies = [];
$repliesFile = __DIR__ . '/../data/support_replies.json';
if (file_exists($repliesFile)) {
    $repliesData = json_decode(file_get_contents($repliesFile), true) ?? [];
    
    foreach ($repliesData as $reply) {
        if ($reply['ticket_id'] === $id) {
            $replies[] = $reply;
        }
    }
}
debugLog("Found " . count($replies) . " replies for this ticket");

// Get user data for display names
$users = [];
$usersFile = __DIR__ . '/../data/users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?? [];
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

// Add user names to replies
foreach ($replies as &$reply) {
    $reply['user_name'] = $reply['user_id']; // Default value
    
    if (isset($users[$reply['user_id']])) {
        $reply['user_name'] = $users[$reply['user_id']]['name'];
    } else {
        foreach ($users as $user) {
            if (isset($user['id']) && $user['id'] === $reply['user_id']) {
                $reply['user_name'] = $user['name'];
                break;
            }
        }
    }
}

// Sort replies by creation date
usort($replies, function($a, $b) {
    return strtotime($a['created_at'] ?? 0) - strtotime($b['created_at'] ?? 0);
});

// Load the view template
require_once __DIR__ . '/../views/support/ticket-show.php';
