
<?php
// Script de migração do MySQL para SQLite
// Execute este arquivo uma vez para migrar os dados existentes

require_once 'config.php';

// Configurações do MySQL antigo (ajuste conforme necessário)
$mysql_host = 'localhost';
$mysql_user = 'root';
$mysql_pass = '';
$mysql_db = 'del_teste'; // ou o nome do seu banco MySQL antigo

try {
    // Conectar ao MySQL
    $mysql = new PDO("mysql:host=$mysql_host;dbname=$mysql_db", $mysql_user, $mysql_pass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Conectar ao SQLite (será criado automaticamente)
    require_once 'includes/db.php';
    
    echo "Iniciando migração...\n";
    
    // Migrar usuários
    echo "Migrando usuários...\n";
    $stmt = $mysql->query("SELECT * FROM users");
    $users = $stmt->fetchAll();
    
    $insertUser = $conn->prepare("INSERT OR REPLACE INTO users (id, name, email, password, latitude, longitude, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($users as $user) {
        $insertUser->execute([
            $user['id'],
            $user['name'],
            $user['email'],
            $user['password'],
            $user['latitude'] ?? null,
            $user['longitude'] ?? null,
            $user['created_at']
        ]);
    }
    
    // Migrar orders (como services)
    echo "Migrando pedidos...\n";
    $stmt = $mysql->query("SELECT * FROM orders");
    $orders = $stmt->fetchAll();
    
    $insertService = $conn->prepare("INSERT OR REPLACE INTO services (id, user_id, category_id, title, description, price, image_url, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($orders as $order) {
        // Mapear categoria para ID (você pode ajustar isso)
        $category_id = 1; // Padrão: Tecnologia
        
        $insertService->execute([
            $order['id'],
            $order['user_id'],
            $category_id,
            $order['title'],
            $order['description'],
            0.00, // Preço padrão
            $order['product_image'] ?? null,
            $order['created_at']
        ]);
    }
    
    // Migrar mensagens
    echo "Migrando mensagens...\n";
    $stmt = $mysql->query("SELECT * FROM messages");
    $messages = $stmt->fetchAll();
    
    $insertMessage = $conn->prepare("INSERT OR REPLACE INTO messages (id, sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($messages as $message) {
        $insertMessage->execute([
            $message['id'],
            $message['sender_id'],
            $message['receiver_id'],
            $message['content'],
            $message['sent_at']
        ]);
    }
    
    echo "Migração concluída com sucesso!\n";
    
} catch (PDOException $e) {
    echo "Erro na migração: " . $e->getMessage() . "\n";
}
?>
