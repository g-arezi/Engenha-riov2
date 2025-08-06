<?php
// reply-ticket-new.php - Versão corrigida do processador de respostas de tickets
ini_set('display_errors', 0); // Desativa a exibição de erros para evitar HTML na saída JSON
ini_set('log_errors', 1);     // Ativa o log de erros para o arquivo de log

// Inicia captura de saída para prevenir caracteres extras
ob_start();

// Incluir autoloader e classes necessárias
require_once __DIR__ . '/../autoload.php';
use App\Core\Auth;
use App\Core\Database;

/**
 * Função para enviar respostas JSON padronizadas
 */
function sendJsonResponse($success, $message, $extraData = []) {
    // Limpa qualquer saída anterior
    if (ob_get_length() > 0) ob_end_clean();
    
    // Define o cabeçalho para JSON
    header('Content-Type: application/json');
    
    // Prepara a resposta base
    $response = [
        'success' => $success,
        'message' => $message
    ];
    
    // Adiciona dados extras se fornecidos
    if (!empty($extraData)) {
        $response = array_merge($response, $extraData);
    }
    
    // Envia a resposta JSON
    echo json_encode($response);
    exit;
}

// Registrar início do processamento
error_log("REPLY-TICKET: Iniciando processamento");

// Iniciar sessão se ainda não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar se é uma requisição AJAX
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] === '1';
error_log("REPLY-TICKET: Modo AJAX: " . ($isAjax ? 'Sim' : 'Não'));
error_log("REPLY-TICKET: Session ID: " . session_id());
error_log("REPLY-TICKET: User ID: " . ($_SESSION['user_id'] ?? 'Não autenticado'));

// Incluir o corretor de autenticação AJAX
require_once __DIR__ . '/ajax-auth-fix.php';

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    error_log("REPLY-TICKET: Usuário não autenticado");
    if ($isAjax) {
        sendJsonResponse(false, 'Você precisa estar logado para responder a tickets.');
    } else {
        header('Location: /login');
        exit;
    }
}

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("REPLY-TICKET: Método inválido: " . $_SERVER['REQUEST_METHOD']);
    if ($isAjax) {
        sendJsonResponse(false, 'Esta operação requer o método POST.');
    } else {
        header('Location: /support');
        exit;
    }
}

// Obter ID do ticket
$ticketId = $_GET['id'] ?? null;
if (!$ticketId) {
    error_log("REPLY-TICKET: ID do ticket não fornecido");
    if ($isAjax) {
        sendJsonResponse(false, 'ID do ticket não fornecido.');
    } else {
        $_SESSION['error'] = 'ID do ticket não fornecido.';
        header('Location: /support');
        exit;
    }
}

try {
    // Verificar se o ticket existe
    $db = new Database();
    $ticket = $db->find('support_tickets', $ticketId);
    
    if (!$ticket) {
        throw new Exception("Ticket não encontrado (ID: $ticketId)");
    }
    
    // Verificar permissões
    $userHasPermission = Auth::hasPermission('admin.view') || 
                         Auth::hasPermission('support.manage') || 
                         $ticket['user_id'] === Auth::id();
    
    if (!$userHasPermission) {
        throw new Exception("Você não tem permissão para responder a este ticket.");
    }
    
    // Verificar se a mensagem foi fornecida
    if (empty($_POST['message'])) {
        throw new Exception("A mensagem não pode estar vazia.");
    }
    
    // Obter dados do usuário atual
    $userId = Auth::id();
    $userName = $userId; // Valor padrão
    
    // Buscar nome de usuário
    $usersFile = __DIR__ . '/../data/users.json';
    if (file_exists($usersFile)) {
        $users = json_decode(file_get_contents($usersFile), true) ?? [];
        
        if (isset($users[$userId]) && isset($users[$userId]['name'])) {
            $userName = $users[$userId]['name'];
        } else {
            // Busca alternativa
            foreach ($users as $user) {
                if (isset($user['id']) && $user['id'] === $userId) {
                    $userName = $user['name'] ?? $userName;
                    break;
                }
            }
        }
    }
    
    // Processar upload de arquivo, se existir
    $attachmentPath = null;
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $_FILES['attachment']['tmp_name']);
        finfo_close($fileInfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            throw new Exception("Tipo de arquivo não permitido. Use apenas imagens (JPG, PNG, GIF).");
        }
        
        // Validar tamanho
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($_FILES['attachment']['size'] > $maxSize) {
            throw new Exception("Arquivo muito grande. O limite é 2MB.");
        }
        
        // Preparar diretórios
        $uploadDir = __DIR__ . '/uploads/tickets/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $ticketDir = $uploadDir . $ticketId . '/';
        if (!is_dir($ticketDir)) {
            mkdir($ticketDir, 0755, true);
        }
        
        // Gerar nome único para o arquivo
        $fileExt = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '.' . $fileExt;
        $attachmentPath = '/uploads/tickets/' . $ticketId . '/' . $fileName;
        $fullPath = __DIR__ . $attachmentPath;
        
        // Mover o arquivo
        if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $fullPath)) {
            throw new Exception("Falha ao salvar o arquivo.");
        }
    }
    
    // Preparar dados da resposta
    $replyData = [
        'id' => uniqid('', true),
        'ticket_id' => $ticketId,
        'user_id' => $userId,
        'user_name' => $userName,
        'message' => $_POST['message'],
        'created_at' => date('Y-m-d H:i:s'),
        'is_staff' => Auth::hasPermission('support.manage')
    ];
    
    // Adicionar informações do anexo, se existir
    if ($attachmentPath) {
        $replyData['attachment'] = $attachmentPath;
        $replyData['attachment_name'] = $_FILES['attachment']['name'];
    }
    
    // Salvar resposta no banco de dados
    $replyId = $db->insert('support_replies', $replyData);
    
    // Atualizar status do ticket, se solicitado
    if (isset($_POST['status']) && !empty($_POST['status'])) {
        $db->update('support_tickets', $ticketId, [
            'status' => $_POST['status'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    // Enviar resposta de sucesso
    error_log("REPLY-TICKET: Resposta salva com sucesso. ID: $replyId");
    
    if ($isAjax) {
        sendJsonResponse(true, 'Resposta enviada com sucesso!', [
            'reply_id' => $replyId,
            'ticket_id' => $ticketId
        ]);
    } else {
        $_SESSION['success'] = 'Resposta enviada com sucesso!';
        header("Location: /support/view/$ticketId");
        exit;
    }
    
} catch (Exception $e) {
    error_log("REPLY-TICKET ERROR: " . $e->getMessage());
    
    if ($isAjax) {
        sendJsonResponse(false, $e->getMessage());
    } else {
        $_SESSION['error'] = $e->getMessage();
        header("Location: /support/view/$ticketId");
        exit;
    }
}
?>
