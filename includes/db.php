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

// Função para criar as tabelas necessárias se não existirem
function setupDatabase($conn) {
    // Adicionar colunas de geolocalização na tabela users
    $sql_alter_users = "ALTER TABLE users ADD COLUMN IF NOT EXISTS latitude DECIMAL(10, 8) DEFAULT NULL, 
                                          ADD COLUMN IF NOT EXISTS longitude DECIMAL(11, 8) DEFAULT NULL";
    
    // Criar tabela de pedidos
    $sql_create_orders = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        category VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Executar as queries
    if (!$conn->query($sql_alter_users)) {
        echo "Erro ao alterar tabela users: " . $conn->error;
    }
    
    if (!$conn->query($sql_create_orders)) {
        echo "Erro ao criar tabela orders: " . $conn->error;
    }
}

// Executar setup do banco de dados
setupDatabase($conn);

// Função para calcular a distância entre duas coordenadas usando a fórmula de Haversine
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // Raio da Terra em quilômetros
    
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earthRadius * $c;
    
    return $distance; // Retorna a distância em quilômetros
}