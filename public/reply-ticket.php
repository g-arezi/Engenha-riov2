<?php
// Debug para identificar o erro JSON
ini_set('display_errors', 0); // Desabilitar exibição de erros para o navegador
ini_set('log_errors', 1);     // Ativar log de erros
error_log("REPLY-DEBUG: Iniciando depuração do erro JSON");

// Capturar saída
ob_start();

// Direct route for ticket replies with fewer dependencies
require_once __DIR__ . '/../autoload.php';
use App\Core\Auth;
use App\Core\Database;

// Verificar se é uma requisição AJAX
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
error_log("REPLY-DEBUG: isAjax = " . ($isAjax ? "true" : "false"));

// Incluir o corretor de autenticação AJAX
require_once __DIR__ . '/ajax-auth-fix.php';

// Debug logs
error_log("REPLY-DEBUG: Session ID: " . session_id());
error_log("REPLY-DEBUG: USER ID: " . ($_SESSION['user_id'] ?? 'NOT SET'));

// Helper function to send JSON responses
function sendJsonResponse($success, $message, $data = []) {
    // Limpar qualquer saída anterior
    if (ob_get_level()) ob_end_clean();
    
    // Definir cabeçalho
    header('Content-Type: application/json');
    
    // Preparar resposta
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    // Adicionar dados extras se fornecidos
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    // Codificar e enviar resposta
    echo json_encode($response);
    exit;
}

// Simplificada verificação de login sem Auth
if (!isset($_SESSION['user_id'])) {
    error_log("REPLY-DEBUG: Usuário não autenticado");
    if ($isAjax) {
        sendJsonResponse(false, 'Não autenticado');
    } else {
        header('Location: /login');
        exit;
    }
}

// Ensure this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("REPLY-DEBUG: Método não permitido: " . $_SERVER['REQUEST_METHOD']);
    if ($isAjax) {
        sendJsonResponse(false, 'Método não permitido');
    } else {
        header('Location: /support');
        exit;
    }
}

// Get the ticket ID from the URL
$id = $_GET['id'] ?? null;

if (!$id) {
    $errorMsg = 'ID do ticket não fornecido.';
    error_log("REPLY-TICKET ERROR: $errorMsg");
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    } else {
        $_SESSION['error'] = $errorMsg;
        header('Location: /support');
    }
    exit;
}

$db = new Database();
$ticket = $db->find('support_tickets', $id);

if (!$ticket) {
    $errorMsg = "Ticket não encontrado (ID: $id).";
    error_log("REPLY-TICKET ERROR: $errorMsg");
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    } else {
        $_SESSION['error'] = $errorMsg;
        header('Location: /support');
    }
    exit;
}

// Check if user has permission to reply to this ticket
if (!(Auth::hasPermission('admin.view') || 
     Auth::hasPermission('support.manage') || 
     $ticket['user_id'] === Auth::id())) {
    
    $errorMsg = 'Você não tem permissão para responder a este ticket.';
    error_log("REPLY-TICKET ERROR: $errorMsg (User: " . Auth::id() . ", Ticket Owner: " . $ticket['user_id'] . ")");
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    } else {
        $_SESSION['error'] = $errorMsg;
        header('Location: /support');
    }
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

// Verificar se a mensagem está vazia
if (empty($_POST['message'])) {
    $errorMsg = 'A mensagem não pode estar vazia.';
    error_log("REPLY-TICKET ERROR: $errorMsg");
    
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $errorMsg]);
    } else {
        $_SESSION['error'] = $errorMsg;
        header('Location: /support/view/' . $id);
    }
    exit;
}

error_log("REPLY-TICKET: Mensagem recebida: " . substr($_POST['message'], 0, 50) . "...");

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
        $errorMsg = 'Tipo de arquivo não permitido. Use somente imagens (JPG, PNG ou GIF).';
        error_log("REPLY-TICKET ERROR: $errorMsg (Mime: $mime_type)");
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        } else {
            $_SESSION['error'] = $errorMsg;
            header('Location: /support/view/' . $id);
        }
        exit;
    }
    
    // Verificar tamanho
    if ($_FILES['attachment']['size'] > $max_size) {
        $errorMsg = 'Arquivo muito grande. Tamanho máximo permitido é 2MB.';
        error_log("REPLY-TICKET ERROR: $errorMsg (Size: " . $_FILES['attachment']['size'] . ")");
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        } else {
            $_SESSION['error'] = $errorMsg;
            header('Location: /support/view/' . $id);
        }
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
        $errorMsg = 'Erro ao fazer upload da imagem. Tente novamente.';
        error_log("REPLY-TICKET ERROR: $errorMsg (Path: $full_path)");
        
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => $errorMsg]);
        } else {
            $_SESSION['error'] = $errorMsg;
            header('Location: /support/view/' . $id);
        }
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

// Insert reply and get ID
$replyId = $db->insert('support_replies', $data);

// Update ticket status if provided
if (isset($_POST['status']) && !empty($_POST['status'])) {
    $db->update('support_tickets', $id, [
        'status' => $_POST['status'],
        'updated_at' => date('Y-m-d H:i:s')
    ]);
}

// Set success message
$_SESSION['success'] = 'Resposta enviada com sucesso!';

// Return JSON response for AJAX requests
if ($isAjax) {
    sendJsonResponse(true, 'Resposta enviada com sucesso!', [
        'reply_id' => $replyId,
        'ticket_id' => $id
    ]);
}

// Redirect for regular form submissions
header('Location: /support/view/' . $id);
exit;
