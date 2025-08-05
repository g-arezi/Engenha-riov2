<?php
// check-ticket-links.php - Verificar formato dos IDs nos links de tickets
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Carregar dados dos tickets
$ticketsFile = __DIR__ . '/../data/support_tickets.json';
$ticketsData = [];

if (file_exists($ticketsFile)) {
    $content = file_get_contents($ticketsFile);
    $ticketsData = json_decode($content, true) ?? [];
}

echo "<h1>Verificação de Links de Tickets</h1>";

echo "<h2>Formato dos IDs nos Tickets</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>ID interno</th><th>Formato</th><th>Link</th></tr>";

foreach ($ticketsData as $id => $ticket) {
    $internalId = $ticket['id'] ?? 'N/A';
    $format = "ID da chave: " . gettype($id) . " (" . strlen($id) . " chars)<br>ID interno: " . gettype($internalId) . " (" . strlen($internalId) . " chars)";
    $link = "/get-ticket-detail.php?id=" . urlencode($id);
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($id) . "</td>";
    echo "<td>" . htmlspecialchars($internalId) . "</td>";
    echo "<td>" . $format . "</td>";
    echo "<td><a href='$link' target='_blank'>Testar</a></td>";
    echo "</tr>";
}

echo "</table>";

echo "<h2>Teste de Requisição</h2>";
echo "<p>Selecione um ticket para testar a requisição AJAX:</p>";

echo "<div id='ticket-links'>";
foreach ($ticketsData as $id => $ticket) {
    echo "<button onclick='testTicket(\"" . htmlspecialchars($id) . "\")' class='ticket-btn'>";
    echo htmlspecialchars($ticket['subject']);
    echo "</button> ";
}
echo "</div>";

echo "<div id='result' style='margin-top:20px; padding:10px; border:1px solid #ddd;'>";
echo "Resultado aparecerá aqui...";
echo "</div>";

?>
<style>
.ticket-btn {
    margin: 5px;
    padding: 5px 10px;
    background-color: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 3px;
    cursor: pointer;
}
.ticket-btn:hover {
    background-color: #e0e0e0;
}
</style>

<script>
function testTicket(id) {
    console.log('Testando ticket ID:', id);
    document.getElementById('result').innerHTML = `<p>Carregando ticket ID: ${id}...</p>`;
    
    fetch(`/get-ticket-detail.php?id=${encodeURIComponent(id)}&_=${Date.now()}`)
        .then(response => {
            document.getElementById('result').innerHTML += `<p>Status: ${response.status} ${response.statusText}</p>`;
            return response.text();
        })
        .then(html => {
            document.getElementById('result').innerHTML += `<p>Resposta recebida (${html.length} bytes)</p>`;
            document.getElementById('result').innerHTML += `<div style="max-height:300px; overflow:auto; border:1px solid #eee; padding:10px; margin-top:10px;">${html}</div>`;
        })
        .catch(error => {
            document.getElementById('result').innerHTML += `<p style="color:red">Erro: ${error.message}</p>`;
        });
}
</script>
