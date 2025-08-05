<?php
// debug-diagnose.php - Script específico para diagnóstico do problema com os detalhes de tickets
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Function to log to both file and screen
function diagnose($message, $isError = false) {
    echo '<div style="padding: 5px; margin: 2px; border-left: 3px solid ' . ($isError ? 'red' : 'blue') . ';">' . $message . '</div>';
    error_log("[DIAGNOSE] " . $message);
}

diagnose("Diagnóstico de problemas com detalhes de tickets");
diagnose("PHP Version: " . PHP_VERSION);
diagnose("Session ID: " . session_id());
diagnose("Current User ID: " . ($_SESSION['user_id'] ?? 'Não autenticado'));

// Testa os caminhos dos arquivos críticos
$paths = [
    'tickets_json' => __DIR__ . '/../data/support_tickets.json',
    'replies_json' => __DIR__ . '/../data/support_replies.json',
    'users_json' => __DIR__ . '/../data/users.json',
    'get_ticket_detail' => __DIR__ . '/get-ticket-detail.php',
    'ticket_js' => __DIR__ . '/assets/js/ticket-detail-view.js'
];

diagnose("====== TESTANDO CAMINHOS DE ARQUIVOS ======");
foreach ($paths as $name => $path) {
    if (file_exists($path)) {
        diagnose("✓ Arquivo $name existe: $path");
        
        // Verificar permissões
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        diagnose("  Permissões: $perms");
        
        // Se for arquivo JSON, testar leitura e parsing
        if (pathinfo($path, PATHINFO_EXTENSION) == 'json') {
            $content = @file_get_contents($path);
            if ($content === false) {
                diagnose("✕ Erro ao ler arquivo $name", true);
            } else {
                $json = json_decode($content, true);
                if ($json === null) {
                    diagnose("✕ Erro ao decodificar JSON em $name: " . json_last_error_msg(), true);
                } else {
                    diagnose("  JSON válido, contém " . count($json) . " itens");
                }
            }
        }
    } else {
        diagnose("✕ Arquivo $name não existe: $path", true);
    }
}

// Teste direto de uma requisição para get-ticket-detail.php
diagnose("====== TESTANDO REQUISIÇÃO AO GET-TICKET-DETAIL.PHP ======");

// Pegar um ID de ticket válido do arquivo JSON
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
$testTicketId = null;

if (file_exists($ticketsFile)) {
    $tickets = json_decode(file_get_contents($ticketsFile), true);
    if ($tickets) {
        // Pegar o primeiro ticket como teste
        reset($tickets);
        $testTicketId = key($tickets);
        diagnose("Usando ticket ID para teste: $testTicketId");
    }
}

// Se encontrou um ID, fazer uma requisição para get-ticket-detail.php
if ($testTicketId) {
    diagnose("Fazendo requisição direta para get-ticket-detail.php com ID: $testTicketId");
    
    // Use file_get_contents para fazer uma requisição interna
    $detailUrl = "http://" . $_SERVER['HTTP_HOST'] . "/get-ticket-detail.php?id=" . $testTicketId;
    diagnose("URL: $detailUrl");
    
    // Configurar contexto para incluir cookies de sessão
    $opts = [
        'http' => [
            'header' => "Cookie: PHPSESSID=" . session_id() . "\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    
    try {
        $response = @file_get_contents($detailUrl, false, $context);
        
        if ($response === false) {
            diagnose("✕ Erro ao fazer requisição: " . error_get_last()['message'], true);
        } else {
            diagnose("✓ Requisição bem-sucedida");
            diagnose("Tamanho da resposta: " . strlen($response) . " bytes");
            
            // Mostrar primeiros 300 caracteres da resposta
            diagnose("Início da resposta: " . substr($response, 0, 300) . "...");
        }
    } catch (Exception $e) {
        diagnose("✕ Exceção ao fazer requisição: " . $e->getMessage(), true);
    }
}

// Teste direto da leitura e parsing dos arquivos
diagnose("====== TESTANDO PARSING DIRETO DOS ARQUIVOS JSON ======");

// 1. Tickets
if (file_exists($ticketsFile)) {
    try {
        $content = file_get_contents($ticketsFile);
        diagnose("Lido arquivo de tickets: " . strlen($content) . " bytes");
        
        $tickets = json_decode($content, true);
        if ($tickets === null) {
            diagnose("✕ Erro ao decodificar JSON de tickets: " . json_last_error_msg(), true);
        } else {
            diagnose("✓ JSON de tickets decodificado com sucesso: " . count($tickets) . " tickets");
        }
    } catch (Exception $e) {
        diagnose("✕ Exceção ao ler/parsear arquivo de tickets: " . $e->getMessage(), true);
    }
}

// 2. Testar especificamente o acesso ao get-ticket-detail.php
diagnose("====== TESTANDO INCLUSÃO DIRETA DO ARQUIVO GET-TICKET-DETAIL.PHP ======");

// Verificar se o arquivo existe
if (file_exists(__DIR__ . '/get-ticket-detail.php')) {
    diagnose("Arquivo get-ticket-detail.php existe, testando include...");
    
    // Iniciar buffer de saída para capturar qualquer erro
    ob_start();
    
    // Definir o ID do ticket para o teste
    $_GET['id'] = $testTicketId;
    
    try {
        // Incluir o arquivo e capturar qualquer erro
        include __DIR__ . '/get-ticket-detail.php';
        diagnose("✓ Arquivo incluído com sucesso");
    } catch (Exception $e) {
        diagnose("✕ Exceção ao incluir arquivo: " . $e->getMessage(), true);
    }
    
    // Pegar a saída e limpar o buffer
    $output = ob_get_clean();
    diagnose("Tamanho da saída: " . strlen($output) . " bytes");
}

// Exibir uma sugestão de correção
diagnose("====== SUGESTÃO DE CORREÇÃO ======");
diagnose("Se os testes acima mostraram erros, pode ser necessário corrigir o arquivo get-ticket-detail.php ou verificar as permissões de acesso dos arquivos JSON.");
diagnose("Certifique-se de que o caminho '../data/support_tickets.json' está correto a partir do diretório public/.");
diagnose("Verifique também se a sessão está sendo mantida corretamente entre as requisições AJAX.");

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Diagnóstico de Tickets</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .test-area { 
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #response { 
            padding: 10px; 
            border: 1px solid #ddd; 
            margin-top: 10px;
            min-height: 100px;
            max-height: 400px;
            overflow: auto;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Teste Manual de Detalhes do Ticket</h1>
    
    <div class="test-area">
        <h3>Testar Chamada AJAX</h3>
        <div>
            <label for="ticket-id">ID do Ticket:</label>
            <input type="text" id="ticket-id" value="<?= htmlspecialchars($testTicketId ?? '') ?>">
            <button onclick="testAjax()">Testar AJAX</button>
        </div>
        <div id="response"></div>
    </div>
    
    <script>
        function testAjax() {
            const ticketId = document.getElementById('ticket-id').value;
            const responseDiv = document.getElementById('response');
            
            if (!ticketId) {
                responseDiv.innerHTML = '<p style="color:red">Por favor, insira um ID de ticket</p>';
                return;
            }
            
            responseDiv.innerHTML = '<p>Carregando...</p>';
            
            fetch(`/get-ticket-detail.php?id=${ticketId}`)
                .then(response => {
                    responseDiv.innerHTML += `<p>Status: ${response.status} ${response.statusText}</p>`;
                    return response.text();
                })
                .then(data => {
                    responseDiv.innerHTML += `<p>Dados recebidos (${data.length} bytes)</p>`;
                    responseDiv.innerHTML += `<pre>${data.substring(0, 300)}...</pre>`;
                })
                .catch(error => {
                    responseDiv.innerHTML = `<p style="color:red">Erro: ${error.message}</p>`;
                });
        }
    </script>
</body>
</html>
