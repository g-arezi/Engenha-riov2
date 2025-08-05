<?php
// Direct route for updating ticket status
require_once __DIR__ . '/../autoload.php';

use App\Core\Database;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in and has permission
$isLoggedIn = isset($_SESSION['user_id']);
$isAdmin = isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], ['administrador', 'analista', 'coordenador']);

if (!$isLoggedIn || !$isAdmin) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

// Get the ticket ID from the URL
$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID não fornecido']);
    exit;
}

// Get the input data
$input = json_decode(file_get_contents('php://input'), true);
$newStatus = $input['status'] ?? '';

// Validate status
$allowedStatuses = ['aberto', 'em_andamento', 'fechado'];
if (!in_array($newStatus, $allowedStatuses)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Status inválido']);
    exit;
}

// Update the ticket
$db = new Database();
$updated = $db->update('support_tickets', $id, ['status' => $newStatus]);

if ($updated) {
    echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso']);
} else {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Ticket não encontrado']);
}
exit;
