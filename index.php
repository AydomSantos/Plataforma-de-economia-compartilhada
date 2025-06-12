<?php
session_start(); 

// Inclui a conexão com o banco de dados UMA ÚNICA VEZ
require_once __DIR__ . '/includes/db.php'; // Ajuste o caminho se seu includes/db.php estiver em outro lugar

// Obter a página solicitada
$page = $_GET['page'] ?? 'home'; // Valor padrão para 'home'

// --- Lógica de Autenticação CENTRALIZADA ---
$public_pages = ['login', 'register', 'about', 'terms', 'privacy']; // Páginas que não exigem login
$auth_required = !in_array($page, $public_pages);

if ($auth_required && !isset($_SESSION['user_id'])) {
    header("Location: index.php?page=login"); // Redireciona para a página de login se não estiver logado e a página exigir
    exit();
}
// --- Fim da Lógica de Autenticação Centralizada ---


// Mapeamento de páginas para arquivos
$pages = [
    'home' => 'pages/home.php',
    'explore_orders' => 'pages/explore_orders.php',
    'create_order' => 'pages/create_order.php',
    'view_order' => 'pages/view_order.php',
    'profile' => 'pages/profile.php',
    'edit_profile' => 'pages/edit_profile.php',
    'change_password' => 'pages/change_password.php',
    'chat' => 'pages/chat.php',
    'find_users' => 'pages/find_users.php',
    'get_messages' => 'pages/get_messages.php',
    'send_message' => 'pages/send_message.php',
    'delete_order' => 'pages/delete_order.php',
    'edit_order' => 'pages/edit_order.php',
    'create_request' => 'pages/create_request.php',
    'dashboard' => 'pages/dashboard.php',
    'check_new_messages' => 'pages/check_new_messages.php',
    'rate_user' => 'pages/rate_user.php',
    'register' => 'pages/register.php',
    'login' => 'pages/login.php', 
    'logout' => 'pages/logout.php', 
    'update_location' => 'pages/update_location.php'
];

// Inclui a página solicitada
if (array_key_exists($page, $pages) && file_exists($pages[$page])) {
    require_once $pages[$page];
} else {
    // Página não encontrada, exibe um erro 404
    http_response_code(404);
    echo "<h1>404 Not Found</h1><p>A página solicitada não foi encontrada.</p>";
}

