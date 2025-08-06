<?php
// check-session.php - Verificar estado da sessão
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

echo "<h1>Estado da Sessão</h1>";

echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "Session Data: " . print_r($_SESSION, true) . "\n";
echo "</pre>";

echo "<h2>Teste de Cookies</h2>";
echo "<pre>";
echo "Cookies: " . print_r($_COOKIE, true) . "\n";
echo "</pre>";

// Adicionar teste para escrever na sessão
if (isset($_GET['set_user_id'])) {
    $user_id = $_GET['set_user_id'];
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_role'] = 'administrador'; // Para teste
    echo "<div style='color:green'>Sessão atualizada com user_id=$user_id</div>";
}

?>

<form method="get">
    <input type="text" name="set_user_id" placeholder="Set user_id">
    <button type="submit">Set Session</button>
</form>

<h2>Teste de Tickets</h2>

<button onclick="testTicket()">Test AJAX Request</button>
<div id="result" style="margin-top:10px; padding:10px; border:1px solid #ddd;"></div>

<script>
function testTicket() {
    document.getElementById('result').innerHTML = 'Testando...';
    
    fetch('/get-ticket-detail.php?id=68920660c65683.50614491&debug=1')
        .then(response => {
            document.getElementById('result').innerHTML += `<p>Status: ${response.status} ${response.statusText}</p>`;
            return response.text();
        })
        .then(data => {
            document.getElementById('result').innerHTML += `<p>Recebido ${data.length} bytes</p><div>${data}</div>`;
        })
        .catch(error => {
            document.getElementById('result').innerHTML += `<p style="color:red">Erro: ${error}</p>`;
        });
}
</script>
