<?php
// Direct route for ticket replies with fewer dependencies
require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure user is logged in
if (!Auth::check()) {
    header('Location: /login');
    exit;
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /support');
    exit;
}

// Get the ticket ID from the URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $_SESSION['error'] = 'ID do ticket não fornecido.';
    header('Location: /support');
    exit;
}

$db = new Database();
$ticket = $db->find('support_tickets', $id);

if (!$ticket) {
    $_SESSION['error'] = 'Ticket não encontrado.';
    header('Location: /support');
    exit;
}

// Check if user has permission to reply to this ticket
if (!(Auth::hasPermission('admin.view') || 
     Auth::hasPermission('support.manage') || 
     $ticket['user_id'] === Auth::id())) {
    $_SESSION['error'] = 'Você não tem permissão para responder a este ticket.';
    header('Location: /support');
    exit;
}

// Get user data for the current user
$currentUser = Auth::id();
$currentUserName = $currentUser; // Default to ID

// Get users data to find the name
$users = [];
if (file_exists(__DIR__ . '/../data/users.json')) {
    $users = json_decode(file_get_contents(__DIR__ . '/../data/users.json'), true) ?: [];
    
    // Check if user exists directly by ID as key
    if (isset($users[$currentUser])) {
        $currentUserName = $users[$currentUser]['name'];
    } else {
        // Search through all users
        foreach ($users as $user) {
            if (isset($user['id']) && $user['id'] === $currentUser) {
                $currentUserName = $user['name'];
                break;
            }
        }
    }
}

// Processar upload de imagem, se houver
$attachment_path = null;
$attachment_name = null;

if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $max_size = 2 * 1024 * 1024; // 2MB
    
    // Verificar tipo de arquivo
    $file_info = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($file_info, $_FILES['attachment']['tmp_name']);
    finfo_close($file_info);
    
    if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['error'] = 'Tipo de arquivo não permitido. Use somente imagens (JPG, PNG ou GIF).';
        header('Location: /view-ticket.php?id=' . $id);
        exit;
    }
    
    // Verificar tamanho
    if ($_FILES['attachment']['size'] > $max_size) {
        $_SESSION['error'] = 'Arquivo muito grande. Tamanho máximo permitido é 2MB.';
        header('Location: /view-ticket.php?id=' . $id);
        exit;
    }
    
    // Gerar nome único para o arquivo
    $upload_dir = __DIR__ . '/uploads/tickets/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Usar o ID do ticket para organizar os arquivos
    $ticket_dir = $upload_dir . $id . '/';
    if (!is_dir($ticket_dir)) {
        mkdir($ticket_dir, 0755, true);
    }
    
    $file_extension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
    $attachment_name = uniqid() . '.' . $file_extension;
    $attachment_path = '/uploads/tickets/' . $id . '/' . $attachment_name;
    $full_path = __DIR__ . $attachment_path;
    
    // Salvar o arquivo
    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $full_path)) {
        $_SESSION['error'] = 'Erro ao fazer upload da imagem. Tente novamente.';
        header('Location: /view-ticket.php?id=' . $id);
        exit;
    }
}

// Store reply data
$data = [
    'ticket_id' => $id,
    'message' => $_POST['message'] ?? '',
    'user_id' => $currentUser,
    'user_name' => $currentUserName, // Add name directly to the reply
    'is_staff' => Auth::hasPermission('support.manage')
];

// Adicionar caminho da imagem se tiver upload
if ($attachment_path) {
    $data['attachment'] = $attachment_path;
    $data['attachment_name'] = $_FILES['attachment']['name'];
}

$db->insert('support_replies', $data);

// Update ticket status if provided
if (isset($_POST['status']) && !empty($_POST['status'])) {
    $db->update('support_tickets', $id, ['status' => $_POST['status']]);
}

$_SESSION['success'] = 'Resposta enviada com sucesso!';

// Redirecionamento corrigido: usar view-ticket.php diretamente
header('Location: /view-ticket.php?id=' . $id);
exit;
