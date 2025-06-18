<?php
require_once __DIR__ . '/../config.php';

// Criar diretório do banco se não existir
$dbDir = dirname(DB_PATH);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    $conn = new PDO('sqlite:' . DB_PATH);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    setupDatabase($conn);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

function setupDatabase($conn) {
    $tables = [
        // Tabela users
        "CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            bio TEXT,
            profile_picture VARCHAR(500),
            user_type TEXT DEFAULT 'ambos' CHECK(user_type IN ('cliente','prestador','ambos')),
            status TEXT DEFAULT 'ativo' CHECK(status IN ('ativo','inativo','suspenso')),
            email_verified INTEGER DEFAULT 0,
            latitude REAL DEFAULT NULL,
            longitude REAL DEFAULT NULL,
            rating REAL DEFAULT 0.00,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Tabela categories
        "CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL UNIQUE,
            description TEXT DEFAULT NULL,
            icon VARCHAR(100) DEFAULT NULL,
            color VARCHAR(7) DEFAULT NULL,
            status TEXT DEFAULT 'ativo' CHECK(status IN ('ativo','inativo')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )",
        
        // Tabela services (equivalente aos orders antigos)
        "CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            category_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT NOT NULL,
            price REAL NOT NULL,
            price_unit TEXT DEFAULT 'hora' CHECK(price_unit IN ('hora','dia','projeto','mes')),
            location VARCHAR(255) DEFAULT NULL,
            service_type TEXT DEFAULT 'ambos' CHECK(service_type IN ('presencial','remoto','ambos')),
            duration_estimate VARCHAR(100) DEFAULT NULL,
            requirements TEXT DEFAULT NULL,
            image_url VARCHAR(500) DEFAULT NULL,
            status TEXT DEFAULT 'ativo' CHECK(status IN ('ativo','inativo','pausado')),
            views_count INTEGER DEFAULT 0,
            rating_average REAL DEFAULT 0.00,
            rating_count INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id)
        )",
        
        // Tabela contracts
        "CREATE TABLE IF NOT EXISTS contracts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_id INTEGER NOT NULL,
            client_id INTEGER NOT NULL,
            provider_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            description TEXT DEFAULT NULL,
            proposed_price REAL NOT NULL,
            agreed_price REAL DEFAULT NULL,
            estimated_duration VARCHAR(100) DEFAULT NULL,
            location VARCHAR(255) DEFAULT NULL,
            status TEXT DEFAULT 'pendente' CHECK(status IN ('pendente','aceito','rejeitado','em_andamento','concluido','cancelado')),
            start_date DATETIME DEFAULT NULL,
            end_date DATETIME DEFAULT NULL,
            completion_date DATETIME DEFAULT NULL,
            client_notes TEXT DEFAULT NULL,
            provider_notes TEXT DEFAULT NULL,
            cancellation_reason TEXT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            FOREIGN KEY (client_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (provider_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Tabela messages
        "CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            contract_id INTEGER DEFAULT NULL,
            sender_id INTEGER NOT NULL,
            receiver_id INTEGER NOT NULL,
            subject VARCHAR(255) DEFAULT NULL,
            content TEXT NOT NULL,
            message_type TEXT DEFAULT 'geral' CHECK(message_type IN ('negociacao','duvida','suporte','geral')),
            is_read INTEGER DEFAULT 0,
            parent_message_id INTEGER DEFAULT NULL,
            sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            read_at DATETIME NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
            FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_message_id) REFERENCES messages(id) ON DELETE SET NULL
        )",
        
        // Tabela ratings
        "CREATE TABLE IF NOT EXISTS ratings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            contract_id INTEGER NOT NULL,
            rater_id INTEGER NOT NULL,
            rated_id INTEGER NOT NULL,
            service_id INTEGER NOT NULL,
            rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
            comment TEXT DEFAULT NULL,
            rating_type TEXT NOT NULL CHECK(rating_type IN ('cliente_para_prestador','prestador_para_cliente')),
            is_visible INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (contract_id) REFERENCES contracts(id) ON DELETE CASCADE,
            FOREIGN KEY (rater_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (rated_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            UNIQUE(contract_id, rater_id, rating_type)
        )",
        
        // Tabela notifications
        "CREATE TABLE IF NOT EXISTS notifications (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            type TEXT NOT NULL CHECK(type IN ('contract','message','rating','system')),
            related_id INTEGER DEFAULT NULL,
            is_read INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )",
        
        // Tabela favorites
        "CREATE TABLE IF NOT EXISTS favorites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            service_id INTEGER NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
            UNIQUE(user_id, service_id)
        )",
        
        // Tabela service_images
        "CREATE TABLE IF NOT EXISTS service_images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            service_id INTEGER NOT NULL,
            image_url VARCHAR(500) NOT NULL,
            alt_text VARCHAR(255) DEFAULT NULL,
            is_primary INTEGER DEFAULT 0,
            display_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
        )",
        
        // Tabela user_sessions
        "CREATE TABLE IF NOT EXISTS user_sessions (
            id VARCHAR(128) PRIMARY KEY,
            user_id INTEGER DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            last_activity DATETIME DEFAULT CURRENT_TIMESTAMP,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )"
    ];
    
    foreach ($tables as $sql) {
        try {
            $conn->exec($sql);
        } catch (PDOException $e) {
            throw new Exception("Erro na criação da tabela: " . $e->getMessage());
        }
    }
    
    // Inserir categorias padrão se não existirem
    insertDefaultCategories($conn);
}

function insertDefaultCategories($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM categories");
    $stmt->execute();
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        $categories = [
            ['Tecnologia', 'Desenvolvimento, design, suporte técnico', 'bi-laptop', '#3B82F6'],
            ['Educação', 'Aulas particulares, cursos, tutoria', 'bi-book', '#10B981'],
            ['Casa e Jardim', 'Limpeza, jardinagem, reparos domésticos', 'bi-house', '#F59E0B'],
            ['Saúde e Bem-estar', 'Massagem, personal trainer, nutrição', 'bi-heart', '#EF4444'],
            ['Eventos', 'Fotografia, decoração, música', 'bi-camera', '#8B5CF6'],
            ['Transporte', 'Mudanças, entregas, motorista', 'bi-truck', '#06B6D4'],
            ['Consultoria', 'Business, marketing, jurídico', 'bi-briefcase', '#84CC16'],
            ['Arte e Design', 'Ilustração, web design, artesanato', 'bi-palette', '#F97316']
        ];
        
        $stmt = $conn->prepare("INSERT INTO categories (name, description, icon, color) VALUES (?, ?, ?, ?)");
        foreach ($categories as $category) {
            $stmt->execute($category);
        }
    }
}

function calculateDistance($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}
?>
