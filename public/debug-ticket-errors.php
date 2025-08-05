<?php
// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Set debug data
$_SESSION['user_id'] = 'admin_user';
$_SESSION['user_role'] = 'administrador';
$_SESSION['user_name'] = 'Administrator';
$_SESSION['logged_in'] = true;

// Get ticket ID
$ticketId = $_GET['id'] ?? null;

if (!$ticketId) {
    echo '<h1>Error: No ticket ID provided</h1>';
    echo '<p>Please provide a ticket ID in the URL: ?id=TICKET_ID</p>';
    exit;
}

echo '<h1>Debug Mode Enabled</h1>';
echo '<p>Loading ticket: ' . htmlspecialchars($ticketId) . '</p>';

// Try to include the ticket view file
try {
    // Include ticket view file
    include __DIR__ . '/ticket-view.php';
} catch (Throwable $e) {
    echo '<div style="background-color: #f8d7da; color: #721c24; padding: 20px; margin: 20px 0; border-radius: 5px;">';
    echo '<h2>Error Caught:</h2>';
    echo '<p><strong>Message:</strong> ' . $e->getMessage() . '</p>';
    echo '<p><strong>File:</strong> ' . $e->getFile() . '</p>';
    echo '<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
    echo '<h3>Stack Trace:</h3>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
    echo '</div>';
}
