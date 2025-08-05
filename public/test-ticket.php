<?php
// Special test page that directly loads a ticket view with predefined session data
session_start();

// Set session data for testing
$_SESSION['user_id'] = $_GET['user_id'] ?? 'admin_user';
$_SESSION['user_name'] = $_GET['user_name'] ?? 'Administrator';
$_SESSION['user_role'] = $_GET['role'] ?? 'administrador';
$_SESSION['logged_in'] = true;

// Get ticket ID
$ticketId = $_GET['ticket_id'] ?? '688a689fa15ee7.57357003'; // Default ID from our test

// Redirect to the ticket view
header("Location: /ticket-view.php?id=$ticketId");
exit;
