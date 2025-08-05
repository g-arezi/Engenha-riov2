<?php
// debug-ticket-view.php - Test the direct ticket viewing approach
session_start();

// Log session data
error_log("SESSION DATA: " . json_encode($_SESSION));

// If not logged in, set test session data
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 'test_user';
    $_SESSION['user_role'] = 'administrador';
    $_SESSION['user_name'] = 'Test User';
    error_log("Set test session data");
}

// Display page content
echo '<html><head><title>Ticket Debug</title>';
echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">';
echo '<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>';
echo '</head><body class="p-5">';

echo '<div class="container">';
echo '<h1>Ticket Debug</h1>';

echo '<div class="card mb-4">';
echo '<div class="card-header">Session Data</div>';
echo '<div class="card-body">';
echo '<pre>' . json_encode($_SESSION, JSON_PRETTY_PRINT) . '</pre>';
echo '</div></div>';

// List tickets from JSON file
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
if (file_exists($ticketsFile)) {
    $tickets = json_decode(file_get_contents($ticketsFile), true) ?? [];
    
    echo '<div class="card mb-4">';
    echo '<div class="card-header">Available Tickets</div>';
    echo '<div class="card-body">';
    echo '<div class="list-group">';
    
    foreach ($tickets as $id => $ticket) {
        // Make sure the ID is available
        if (!isset($ticket['id'])) {
            $ticket['id'] = $id;
        }
        
        echo '<a href="/ticket-view.php?id=' . $ticket['id'] . '" class="list-group-item list-group-item-action">';
        echo 'Ticket #' . substr($ticket['id'], 0, 8) . ' - ' . htmlspecialchars($ticket['subject']);
        echo '<span class="badge bg-' . ($ticket['status'] === 'aberto' ? 'success' : ($ticket['status'] === 'em_andamento' ? 'warning' : 'secondary')) . ' ms-2">';
        echo ucfirst(str_replace('_', ' ', $ticket['status'])) . '</span>';
        echo '</a>';
    }
    
    echo '</div></div></div>';
} else {
    echo '<div class="alert alert-warning">Tickets file not found</div>';
}

echo '<a href="/support" class="btn btn-primary">Go to Support Page</a>';
echo '</div></body></html>';
