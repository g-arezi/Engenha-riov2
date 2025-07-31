<?php

// Arquivo para debug de sessões
session_start();

echo "Conteúdo atual da sessão:\n";
var_dump($_SESSION);

// Criar uma variável de sessão para teste
$_SESSION['test'] = 'Sessão de teste - ' . date('Y-m-d H:i:s');

echo "\n\nSessão após adicionar variável de teste:\n";
var_dump($_SESSION);

echo "\n\nInformações da sessão:\n";
echo "Session ID: " . session_id() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session status: " . session_status() . "\n";
echo "Session save path: " . session_save_path() . "\n";

// Verificar configurações do PHP
echo "\n\nConfigurações de sessão do PHP:\n";
echo "session.save_path = " . ini_get('session.save_path') . "\n";
echo "session.name = " . ini_get('session.name') . "\n";
echo "session.cookie_lifetime = " . ini_get('session.cookie_lifetime') . "\n";
echo "session.cookie_path = " . ini_get('session.cookie_path') . "\n";
echo "session.cookie_domain = " . ini_get('session.cookie_domain') . "\n";
echo "session.cookie_secure = " . ini_get('session.cookie_secure') . "\n";
echo "session.cookie_httponly = " . ini_get('session.cookie_httponly') . "\n";
echo "session.use_cookies = " . ini_get('session.use_cookies') . "\n";
echo "session.use_only_cookies = " . ini_get('session.use_only_cookies') . "\n";
