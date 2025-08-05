<?php
// Direct route for tickets with fewer dependencies and complications
require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Debug helper function
function debugLog($message) {
    error_log("[SUPPORT-VIEW DEBUG] " . $message);
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Debug session info
debugLog("Session ID: " . session_id());
debugLog("Session data: " . json_encode($_SESSION));

// Enhanced debugging for Auth::check()
$isAuthenticated = Auth::check();
debugLog("Auth::check() result: " . ($isAuthenticated ? "TRUE" : "FALSE"));

// If not authenticated, check why and log details
if (!$isAuthenticated) {
    debugLog("Authentication failed - redirecting to login");
    
    // Try to get more info about why auth failed
    if (!isset($_SESSION['user_id'])) {
        debugLog("user_id not set in session");
    } else {
        debugLog("user_id exists in session: " . $_SESSION['user_id']);
        
        // Try to load the user manually to see if we can find them
        $db = new Database();
        $user = $db->find('users', $_SESSION['user_id']);
        if ($user) {
            debugLog("User exists in database but Auth::check() still failed");
        } else {
            debugLog("User not found in database for ID: " . $_SESSION['user_id']);
        }
    }
    
    header('Location: /login');
    exit;
}

// Get the ticket ID from the URL
$uri = $_SERVER['REQUEST_URI'];
debugLog("Request URI: " . $uri);

// Extract ticket ID from URL - handle multiple possible formats
if (strpos($uri, '/support/') === 0) {
    $id = str_replace('/support/', '', $uri);
    // Remove any query parameters
    if (($pos = strpos($id, '?')) !== false) {
        $id = substr($id, 0, $pos);
    }
    debugLog("Extracted ID from URL path: " . $id);
} else {
    // Try to get ID from query parameter first
    $id = $_GET['id'] ?? null;
    
    // If we still don't have an ID, try one more fallback method
    if (!$id && preg_match('/\/([^\/]+?)(?:\?.*)?$/', $uri, $matches)) {
        $id = $matches[1];
        debugLog("Extracted ID using regex: " . $id);
    }
    
    debugLog("Got ID from query parameter or fallback: " . $id);
}

if (!$id) {
    $_SESSION['error'] = 'ID do ticket não fornecido.';
    header('Location: /support');
    exit;
}

// Create a database instance
$db = new Database();

// Try to find the ticket directly from the JSON file
$ticketFound = false;
$ticket = null;

// Read the tickets file directly
$jsonFile = __DIR__ . '/../data/support_tickets.json';
if (file_exists($jsonFile)) {
    $tickets = json_decode(file_get_contents($jsonFile), true) ?? [];
    debugLog("Loaded tickets directly from JSON file. Found " . count($tickets) . " tickets.");
    
    if (isset($tickets[$id])) {
        $ticket = $tickets[$id];
        $ticketFound = true;
        debugLog("Found ticket with ID: " . $id);
    } else {
        debugLog("Ticket ID not found in tickets array. Available IDs: " . implode(", ", array_keys($tickets)));
    }
}

// If not found via direct access, try using the database helper
if (!$ticketFound) {
    $ticket = $db->find('support_tickets', $id);
    if ($ticket) {
        $ticketFound = true;
        debugLog("Found ticket using Database::find()");
    }
}

if (!$ticket) {
    $_SESSION['error'] = 'Ticket não encontrado.';
    debugLog("Ticket not found with ID: " . $id);
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
