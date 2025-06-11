<?php
$page = $_GET['page'] ?? 'login';

switch ($page) {
    case 'login':
        require 'pages/login.php';
        break;
    case 'register':
        require 'pages/register.php';
        break;
    case 'home':
        require 'pages/home.php';
        break;
    case 'profile':
        require 'pages/profile.php';
        break;
    case 'edit_profile':
        require 'pages/edit_profile.php';
        break;
    case 'logout':
        require 'pages/logout.php';
        break;
    case 'change_password':
        require 'pages/change_password.php';
        break;
    // Adicione outras rotas conforme necessário
    default:
        require 'pages/login.php';
        break;
}