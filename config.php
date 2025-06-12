<?php
// Configurações de sessão seguras
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    session_start();
}

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'del_teste');

// URL base do projeto
$URL_BASE = "http://localhost/Plataforma-de-economia-compartilhada";

// Configuração de timezone
date_default_timezone_set('America/Sao_Paulo');

// Exibir erros (desative em produção)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Função utilitária global para redirecionamento
function redirect($url) {
    header("Location: $url");
    exit;
}
?>
