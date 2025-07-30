<?php
// Test the auto-login removal

echo "<h1>Engenha Rio - Auto-login Test</h1>";

// Check if session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Display current session info
echo "<h2>Session Information</h2>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Inactive") . "\n";
echo "</pre>";

// Check if user is logged in (by checking session variables)
echo "<h2>Login Status</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<p style='color:green'>✅ User is logged in</p>";
    echo "<pre>";
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    if (isset($_SESSION['user_role'])) {
        echo "User Role: " . $_SESSION['user_role'] . "\n";
    }
    echo "</pre>";
} else {
    echo "<p style='color:red'>❌ No user is logged in</p>";
    echo "<p>Auto-login has been successfully disabled!</p>";
}

// Show links to main sections
echo "<h2>Navigation</h2>";
echo "<ul>";
echo "<li><a href='/'>Home</a></li>";
echo "<li><a href='/login'>Login</a></li>";
echo "<li><a href='/dashboard'>Dashboard</a></li>";
echo "<li><a href='/projects'>Projects</a></li>";
echo "<li><a href='/documents'>Documents</a></li>";
echo "<li><a href='/support'>Support</a></li>";
echo "</ul>";

// Add a reset session link
echo "<h2>Actions</h2>";
echo "<p><a href='?action=reset' style='color:red'>Reset Session</a></p>";

// Handle session reset
if (isset($_GET['action']) && $_GET['action'] === 'reset') {
    session_destroy();
    echo "<p>Session has been reset. <a href='/test-auto-login.php'>Refresh</a></p>";
}
