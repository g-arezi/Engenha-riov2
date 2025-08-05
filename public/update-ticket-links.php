<?php
// Script to force-update all ticket links in index.php
$indexFile = __DIR__ . '/../views/support/index.php';

if (!file_exists($indexFile)) {
    echo "Index file not found!";
    exit;
}

$content = file_get_contents($indexFile);

// Replace all ticket-view.php with ticket-simple-view.php
$content = str_replace('ticket-view.php', 'ticket-simple-view.php', $content);

// Save the file
if (file_put_contents($indexFile, $content)) {
    echo "Successfully updated all links in index.php to use ticket-simple-view.php";
} else {
    echo "Failed to update links";
}
