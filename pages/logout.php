// content of pages/logout.php
<?php
// No need for session_start() here if it's already done in index.php (the front controller)
// But it doesn't hurt to have it for standalone testing of logout.php

// Destrói todas as variáveis de sessão
$_SESSION = array();

// Se existir um cookie de sessão, apaga-o
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

header("Location: index.php?page=login");
exit();

?>