<?php
session_start();
require_once __DIR__ . '/../autoload.php';

use App\Core\Auth;
use App\Core\Database;

// Fazer login como admin
$email = 'admin@engenhario.com';
$password = 'password';

$db = new Database();
$user = $db->findOne('users', ['email' => $email]);

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    echo "✅ Login realizado com sucesso como: " . $user['name'] . "<br>";
    echo "Redirecionando para templates...<br>";
    echo '<script>
        setTimeout(function() {
            window.location.href = "/admin/templates";
        }, 2000);
    </script>';
} else {
    echo "❌ Falha no login<br>";
    echo '<a href="/login">Tentar login manual</a>';
}
?>
