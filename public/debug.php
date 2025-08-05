<?php
// Debug file for testing session and permissions without Auth class
session_start();

// Handle mock login actions
if (isset($_POST['action']) && $_POST['action'] === 'mock_login') {
    // Set mock session data
    $_SESSION['user_id'] = $_POST['user_id'] ?? 'test_user';
    $_SESSION['user_role'] = $_POST['user_role'] ?? 'administrador';
    $_SESSION['user_name'] = $_POST['user_name'] ?? 'Test User';
    $_SESSION['logged_in'] = true;
    
    // Redirect to refresh the page
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Display all session information
echo '<h1>Session Information</h1>';
echo '<pre>' . print_r($_SESSION, true) . '</pre>';

// Add mock login form
echo '<h2>Mock Login</h2>';
echo '<form method="post" action="" style="padding: 15px; background-color: #f8f9fa; border-radius: 5px; margin-bottom: 20px;">';
echo '<input type="hidden" name="action" value="mock_login">';

echo '<div style="margin-bottom: 10px;">';
echo '<label style="display: block; margin-bottom: 5px;">User ID:</label>';
echo '<input type="text" name="user_id" value="admin_user" style="padding: 5px; width: 250px;">';
echo '</div>';

echo '<div style="margin-bottom: 10px;">';
echo '<label style="display: block; margin-bottom: 5px;">User Name:</label>';
echo '<input type="text" name="user_name" value="Administrator" style="padding: 5px; width: 250px;">';
echo '</div>';

echo '<div style="margin-bottom: 15px;">';
echo '<label style="display: block; margin-bottom: 5px;">Role:</label>';
echo '<select name="user_role" style="padding: 5px; width: 250px;">';
echo '<option value="administrador">Administrador</option>';
echo '<option value="analista">Analista</option>';
echo '<option value="cliente">Cliente</option>';
echo '</select>';
echo '</div>';

echo '<button type="submit" style="background-color: #28a745; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer;">Set Session Data</button>';
echo '</form>';

// Check if there's a user role set
$role = $_SESSION['user_role'] ?? null;

echo '<h2>Permissions</h2>';
echo 'Role: ' . ($role ?: 'Not set') . '<br>';

// Define permissions based on role - simplified version from our template
$permissions = [];
if ($role === 'administrador') {
    $permissions = [
        'dashboard.view', 'projects.view', 'projects.create', 'projects.edit', 'documents.view', 'support.view', 'support.manage'
    ];
} elseif ($role === 'analista' || $role === 'coordenador') {
    $permissions = [
        'dashboard.view', 'projects.view', 'projects.edit', 'documents.view', 'support.view', 'support.manage'
    ];
} else {
    $permissions = [
        'dashboard.view', 'projects.view', 'documents.view', 'support.view'
    ];
}

echo '<h3>Available Permissions</h3>';
echo '<ul>';
foreach ($permissions as $permission) {
    echo '<li>' . $permission . '</li>';
}
echo '</ul>';

// Get the user data from users.json
echo '<h2>User Data From File</h2>';
$usersFile = __DIR__ . '/../data/users.json';
if (file_exists($usersFile)) {
    $users = json_decode(file_get_contents($usersFile), true) ?? [];
    $currentUserId = $_SESSION['user_id'] ?? null;
    
    if ($currentUserId) {
        // Find the user data
        $userData = null;
        if (isset($users[$currentUserId])) {
            $userData = $users[$currentUserId];
        } else {
            foreach ($users as $user) {
                if (isset($user['id']) && $user['id'] === $currentUserId) {
                    $userData = $user;
                    break;
                }
            }
        }
        
        if ($userData) {
            echo '<pre>' . print_r($userData, true) . '</pre>';
        } else {
            echo 'User data not found for ID: ' . $currentUserId;
        }
    } else {
        echo 'No user ID in session';
    }
} else {
    echo 'Users file not found';
}

// Add button to test the ticket-view.php
echo '<h2>Test Ticket View</h2>';
echo '<p>Use the following button to test the ticket-view.php file:</p>';

// Load all tickets to display them
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
if (file_exists($ticketsFile)) {
    $tickets = json_decode(file_get_contents($ticketsFile), true) ?? [];
    
    foreach ($tickets as $id => $ticket) {
        // Make sure the ID is available
        $ticketId = isset($ticket['id']) ? $ticket['id'] : $id;
        $subject = isset($ticket['subject']) ? $ticket['subject'] : 'No subject';
        
        echo '<a href="/ticket-view.php?id=' . $ticketId . '" class="button" style="display: inline-block; padding: 10px; margin: 5px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px;">';
        echo 'Ticket #' . substr($ticketId, 0, 8) . ' - ' . htmlspecialchars($subject);
        echo '</a><br>';
    }
} else {
    echo 'Tickets file not found';
}
