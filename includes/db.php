<?php
// Configurações de conexão com o banco de dados
$db_host = 'localhost'; // Host do banco de dados (geralmente localhost para XAMPP)
$db_user = 'root';     // Usuário padrão do MySQL no XAMPP
$db_pass = '';         // Senha padrão vazia no XAMPP
$db_name = 'del_teste'; // Nome do banco de dados

// Estabelece a conexão com o banco de dados
$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Define o charset para utf8 para suportar caracteres especiais
$conn->set_charset("utf8");