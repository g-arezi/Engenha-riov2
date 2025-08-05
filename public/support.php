<?php
// This file serves as a URL rewriter for support ticket URLs
// It intercepts requests to /support/{id} and redirects to support-view.php

require_once __DIR__ . '/../autoload.php';

// Extract the ticket ID from the URL
$uri = $_SERVER['REQUEST_URI'];
$id = null;

// Parse the URI to extract the ID
if (preg_match('/\/support\/([^\/\?]+)/', $uri, $matches)) {
    $id = $matches[1];
    
    // Debug log
    error_log("Intercepted request to /support/{$id} - redirecting to support-view.php");
    
    // Redirect to the direct handler with the ID
    header("Location: /support-view.php?id={$id}");
    exit;
} else {
    // If no ID found, redirect to support index
    error_log("No ID found in URL path: {$uri} - redirecting to support index");
    header('Location: /support');
    exit;
}
