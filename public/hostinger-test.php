<?php
/**
 * Hostinger Environment Test
 * This file helps verify that your application is properly set up on Hostinger
 */

// Check if we're running on Hostinger
function isHostinger() {
    $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
    $hostname = gethostname();
    
    return (
        stripos($serverSoftware, 'LiteSpeed') !== false ||
        stripos($hostname, 'hostinger') !== false ||
        file_exists('/etc/hostinger-system-version')
    );
}

// Display as HTML
function displayResult($test, $result, $success = true, $details = '') {
    $color = $success ? 'green' : 'red';
    $icon = $success ? '✓' : '✗';
    echo "<div style='margin-bottom: 10px;'>";
    echo "<span style='color: $color; font-weight: bold;'>$icon</span> ";
    echo "<strong>$test:</strong> $result";
    if (!empty($details)) {
        echo "<br><span style='margin-left: 20px; color: #777;'><em>$details</em></span>";
    }
    echo "</div>";
}

// Header
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Hostinger Environment Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        h1 { color: #333; }
        .section { margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .warning { background-color: #fff3cd; border: 1px solid #ffeeba; padding: 15px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>Hostinger Environment Test</h1>
    <div class='warning'>
        <strong>Important:</strong> Remove this file after testing for security reasons.
    </div>
    <div class='section'>";

// Basic environment info
echo "<h2>Environment</h2>";
displayResult("Server Software", $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown');
displayResult("PHP Version", phpversion(), version_compare(phpversion(), '7.4.0', '>='));
displayResult("Hostinger Detection", isHostinger() ? 'Yes' : 'No', true);

// File system checks
echo "</div><div class='section'><h2>File System</h2>";
$dataDir = __DIR__ . '/../data';
$uploadsDir = __DIR__ . '/uploads';
$sessionsDir = __DIR__ . '/../data/sessions';

displayResult("Data Directory", is_dir($dataDir) ? 'Exists' : 'Missing', is_dir($dataDir));
displayResult("Data Directory Writable", is_writable($dataDir) ? 'Yes' : 'No', is_writable($dataDir));

displayResult("Uploads Directory", is_dir($uploadsDir) ? 'Exists' : 'Missing', is_dir($uploadsDir));
displayResult("Uploads Directory Writable", is_writable($uploadsDir) ? 'Yes' : 'No', is_writable($uploadsDir));

displayResult("Sessions Directory", is_dir($sessionsDir) ? 'Exists' : 'Missing', is_dir($sessionsDir));
if (is_dir($sessionsDir)) {
    displayResult("Sessions Directory Writable", is_writable($sessionsDir) ? 'Yes' : 'No', is_writable($sessionsDir));
}

// Sessions test
echo "</div><div class='section'><h2>Session Test</h2>";

$sessionStarted = false;
try {
    session_start();
    $sessionStarted = true;
    $_SESSION['test_value'] = 'Working on ' . date('Y-m-d H:i:s');
    displayResult("Session Start", "Success", true);
    displayResult("Session Path", session_save_path(), true);
    displayResult("Session Test Value", $_SESSION['test_value'] ?? 'Not set', isset($_SESSION['test_value']));
} catch (Exception $e) {
    displayResult("Session Start", "Failed", false, $e->getMessage());
}

// PHP Settings
echo "</div><div class='section'><h2>PHP Settings</h2>";

displayResult("max_execution_time", ini_get('max_execution_time') . ' seconds');
displayResult("memory_limit", ini_get('memory_limit'));
displayResult("upload_max_filesize", ini_get('upload_max_filesize'));
displayResult("post_max_size", ini_get('post_max_size'));

// Footer
echo "</div>
    <div class='warning'>
        <strong>Reminder:</strong> Delete this file after you've verified your setup.
    </div>
</body>
</html>";
