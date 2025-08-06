<?php
// diagnose-ticket-reply.php - Ferramenta para diagnosticar problemas no envio de respostas a tickets
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar a sessão se ainda não foi iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Função para exibir informações com formatação
function showSection($title, $content) {
    echo "<div style='margin: 15px 0; border: 1px solid #ddd; padding: 10px; border-radius: 5px;'>";
    echo "<h3 style='margin-top: 0;'>$title</h3>";
    echo "<div style='background: #f9f9f9; padding: 10px; border-radius: 3px; overflow-x: auto;'>";
    
    if (is_array($content) || is_object($content)) {
        echo "<pre>" . htmlspecialchars(print_r($content, true)) . "</pre>";
    } else {
        echo "<pre>" . htmlspecialchars($content) . "</pre>";
    }
    
    echo "</div></div>";
}

echo "<!DOCTYPE html>
<html lang='pt-BR'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Diagnóstico de Resposta a Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
        .status-ok { color: green; }
        .status-error { color: red; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; margin: 5px; border-radius: 3px; }
        button.test-btn { background: #2196F3; }
        input, textarea { width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ddd; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Diagnóstico de Resposta a Tickets</h1>";

// 1. Verificar sessão e autenticação
echo "<h2>1. Status da Sessão</h2>";
showSection("ID da Sessão", session_id());
showSection("Dados da Sessão", $_SESSION);
showSection("Cookies", $_COOKIE);

// 2. Verificar permissões
echo "<h2>2. Permissões do Usuário</h2>";
require_once __DIR__ . '/../autoload.php';

if (isset($_SESSION['user_id'])) {
    $auth = new \App\Core\Auth();
    
    $userId = $_SESSION['user_id'];
    $userRole = $_SESSION['user_role'] ?? 'Não definido';
    
    showSection("ID do Usuário", $userId);
    showSection("Papel do Usuário", $userRole);
    
    // Verificar permissões específicas
    $hasViewPermission = \App\Core\Auth::hasPermission('support.view');
    $hasManagePermission = \App\Core\Auth::hasPermission('support.manage');
    
    echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; " . 
         ($hasViewPermission ? "background-color: #dff0d8;" : "background-color: #f2dede;") . "'>";
    echo "<strong>Permissão para visualizar tickets: </strong>";
    echo $hasViewPermission ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "</div>";
    
    echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; " . 
         ($hasManagePermission ? "background-color: #dff0d8;" : "background-color: #f2dede;") . "'>";
    echo "<strong>Permissão para gerenciar tickets: </strong>";
    echo $hasManagePermission ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f2dede; padding: 10px; border-radius: 5px;'>";
    echo "<strong class='status-error'>ERRO: Usuário não está autenticado!</strong>";
    echo "</div>";
}

// 3. Verificar acesso ao arquivo de dados de tickets
echo "<h2>3. Acesso aos Dados de Tickets</h2>";
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
$repliesFile = __DIR__ . '/../data/support_replies.json';

if (file_exists($ticketsFile)) {
    $fileSize = filesize($ticketsFile);
    $isReadable = is_readable($ticketsFile);
    $isWritable = is_writable($ticketsFile);
    
    echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; " . 
         ($isReadable && $isWritable ? "background-color: #dff0d8;" : "background-color: #f2dede;") . "'>";
    echo "<strong>Arquivo de Tickets: </strong>" . $ticketsFile . "<br>";
    echo "<strong>Tamanho: </strong>" . number_format($fileSize / 1024, 2) . " KB<br>";
    echo "<strong>Permissão de Leitura: </strong>";
    echo $isReadable ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "<br>";
    echo "<strong>Permissão de Escrita: </strong>";
    echo $isWritable ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "</div>";
    
    // Exibir alguns tickets como exemplo
    $ticketsData = json_decode(file_get_contents($ticketsFile), true);
    if ($ticketsData) {
        $sampleTickets = array_slice($ticketsData, 0, 2, true);
        showSection("Amostra de Tickets", $sampleTickets);
    } else {
        echo "<div style='background-color: #f2dede; padding: 10px; border-radius: 5px;'>";
        echo "<strong class='status-error'>Erro ao decodificar JSON de tickets!</strong>";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f2dede; padding: 10px; border-radius: 5px;'>";
    echo "<strong class='status-error'>Arquivo de tickets não encontrado!</strong>";
    echo "</div>";
}

// Verificar arquivo de respostas
if (file_exists($repliesFile)) {
    $fileSize = filesize($repliesFile);
    $isReadable = is_readable($repliesFile);
    $isWritable = is_writable($repliesFile);
    
    echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; " . 
         ($isReadable && $isWritable ? "background-color: #dff0d8;" : "background-color: #f2dede;") . "'>";
    echo "<strong>Arquivo de Respostas: </strong>" . $repliesFile . "<br>";
    echo "<strong>Tamanho: </strong>" . number_format($fileSize / 1024, 2) . " KB<br>";
    echo "<strong>Permissão de Leitura: </strong>";
    echo $isReadable ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "<br>";
    echo "<strong>Permissão de Escrita: </strong>";
    echo $isWritable ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "</div>";
} else {
    echo "<div style='background-color: #f2dede; padding: 10px; border-radius: 5px;'>";
    echo "<strong class='status-error'>Arquivo de respostas não encontrado!</strong>";
    echo "</div>";
}

// 4. Teste de upload de arquivos
echo "<h2>4. Teste de Upload de Arquivos</h2>";
$uploadsDir = __DIR__ . '/uploads/tickets/';
$testDir = $uploadsDir . 'test/';

if (!is_dir($uploadsDir)) {
    echo "<div style='background-color: #f2dede; padding: 10px; border-radius: 5px;'>";
    echo "<strong class='status-error'>Diretório de uploads não existe!</strong>";
    echo "</div>";
} else {
    $isWritable = is_writable($uploadsDir);
    echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; " . 
         ($isWritable ? "background-color: #dff0d8;" : "background-color: #f2dede;") . "'>";
    echo "<strong>Diretório de Uploads: </strong>" . $uploadsDir . "<br>";
    echo "<strong>Permissão de Escrita: </strong>";
    echo $isWritable ? 
         "<span class='status-ok'>SIM</span>" : 
         "<span class='status-error'>NÃO</span>";
    echo "</div>";
    
    // Tentar criar diretório de teste
    if (!is_dir($testDir)) {
        $createResult = @mkdir($testDir, 0755, true);
        echo "<div style='margin: 15px 0; padding: 10px; border-radius: 5px; " . 
             ($createResult ? "background-color: #dff0d8;" : "background-color: #f2dede;") . "'>";
        echo "<strong>Criação de diretório de teste: </strong>";
        echo $createResult ? 
             "<span class='status-ok'>SUCESSO</span>" : 
             "<span class='status-error'>FALHA</span>";
        echo "</div>";
    } else {
        echo "<div style='background-color: #dff0d8; padding: 10px; border-radius: 5px;'>";
        echo "<strong class='status-ok'>Diretório de teste já existe.</strong>";
        echo "</div>";
    }
}

// 5. Formulário de teste
echo "<h2>5. Teste Manual de Envio de Resposta</h2>";
?>

<form id="test-form" method="post" enctype="multipart/form-data" style="margin: 20px 0; border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
    <div style="margin-bottom: 10px;">
        <label for="test-ticket-id"><strong>ID do Ticket:</strong></label>
        <input type="text" id="test-ticket-id" name="ticket_id" placeholder="Digite o ID do ticket" required>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label for="test-message"><strong>Mensagem:</strong></label>
        <textarea id="test-message" name="message" rows="4" placeholder="Digite a mensagem de resposta" required></textarea>
    </div>
    
    <div style="margin-bottom: 10px;">
        <label for="test-file"><strong>Anexo (opcional):</strong></label>
        <input type="file" id="test-file" name="attachment">
    </div>
    
    <button type="button" id="test-api-button" class="test-btn">Testar Envio via API</button>
    <div id="test-result" style="margin-top: 15px; padding: 10px; border-radius: 3px; display: none;"></div>
</form>

<script>
document.getElementById('test-api-button').addEventListener('click', function() {
    const ticketId = document.getElementById('test-ticket-id').value;
    const message = document.getElementById('test-message').value;
    const fileInput = document.getElementById('test-file');
    const resultDiv = document.getElementById('test-result');
    
    if (!ticketId || !message) {
        resultDiv.style.display = 'block';
        resultDiv.style.backgroundColor = '#f2dede';
        resultDiv.innerHTML = '<strong>Erro:</strong> Preencha todos os campos obrigatórios.';
        return;
    }
    
    // Preparar dados do formulário
    const formData = new FormData();
    formData.append('message', message);
    
    if (fileInput.files.length > 0) {
        formData.append('attachment', fileInput.files[0]);
    }
    
    // Mostrar loading
    resultDiv.style.display = 'block';
    resultDiv.style.backgroundColor = '#f9f9f9';
    resultDiv.innerHTML = '<strong>Enviando solicitação...</strong>';
    
    // Enviar solicitação
    fetch('/reply-ticket.php?id=' + encodeURIComponent(ticketId) + '&ajax=1', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => {
        const statusInfo = `Status: ${response.status} ${response.statusText}`;
        
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`${statusInfo}\n\nResposta do servidor:\n${text}`);
            });
        }
        
        return response.json().then(data => ({ data, statusInfo }));
    })
    .then(({ data, statusInfo }) => {
        resultDiv.style.backgroundColor = '#dff0d8';
        resultDiv.innerHTML = `
            <strong>Sucesso!</strong><br>
            <small>${statusInfo}</small>
            <pre>${JSON.stringify(data, null, 2)}</pre>
        `;
    })
    .catch(error => {
        resultDiv.style.backgroundColor = '#f2dede';
        resultDiv.innerHTML = `
            <strong>Erro:</strong><br>
            <pre>${error.message}</pre>
        `;
        console.error('Erro no teste:', error);
    });
});
</script>

<?php
echo "</body></html>";
?>
