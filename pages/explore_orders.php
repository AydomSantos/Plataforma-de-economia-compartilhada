<?php

require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Inicializar variáveis de filtro
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at'; // Inicializar sort_by

// Construir a query base
$base_query = "SELECT r.*, u.name as user_name FROM requests r JOIN users u ON r.user_id = u.id WHERE r.status = 'publicado'";

// Adicionar filtros à query
$params = [];
$types = "";

if (!empty($search_term)) {
    $base_query .= " AND (r.title LIKE ? OR r.description LIKE ?)";
    $search_param = "%{$search_term}%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($category_filter)) {
    $base_query .= " AND r.category = ?";
    $params[] = $category_filter;
    $types .= "s";
}

// Adicionar ordenação
$allowed_sorts = ['created_at', 'title', 'category'];
if (in_array($sort_by, $allowed_sorts)) {
    $base_query .= " ORDER BY r." . $sort_by . " DESC";
} else {
    $base_query .= " ORDER BY r.created_at DESC";
}

// Preparar a query
$stmt = $conn->prepare($base_query);

// Verificar se a preparação foi bem-sucedida
if (!$stmt) {
    die("Erro na preparação da query: " . $conn->error);
}

// Fazer bind dos parâmetros se existirem
if (!empty($params)) {
    if (!$stmt->bind_param($types, ...$params)) {
        die("Erro no bind de parâmetros: " . $stmt->error);
    }
}

// Executar a query
if (!$stmt->execute()) {
    die("Erro na execução da query: " . $stmt->error);
}

$result = $stmt->get_result();
$orders = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
}

$stmt->close();

// Obter categorias para o filtro
$categories = [];
$categories_query = "SELECT DISTINCT category FROM requests WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories_result = $conn->query($categories_query);

if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explorar Pedidos - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Header simples (substituindo o include) -->

    <div class="container mt-4">
        <!-- Formulário de filtros -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Filtros de Busca</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="search" name="search" 
                                   placeholder="Digite palavras-chave..." 
                                   value="<?php echo htmlspecialchars($search_term); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="category" class="form-label">Categoria</label>
                            <select class="form-select" id="category" name="category">
                                <option value="">Todas as categorias</option>
                                <?php if (!empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category); ?>" 
                                                <?php echo ($category_filter === $category) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(ucfirst($category)); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="sort" class="form-label">Ordenar por</label>
                            <select class="form-select" id="sort" name="sort">
                                <option value="created_at" <?php echo ($sort_by === 'created_at') ? 'selected' : ''; ?>>Data de criação</option>
                                <option value="title" <?php echo ($sort_by === 'title') ? 'selected' : ''; ?>>Título</option>
                                <option value="category" <?php echo ($sort_by === 'category') ? 'selected' : ''; ?>>Categoria</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">Buscar</button>
                            <a href="explore_orders.php" class="btn btn-secondary">Limpar Filtros</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultados da pesquisa -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?php 
                        if (!empty($search_term)) {
                            echo 'Resultados para: "' . htmlspecialchars($search_term) . '"';
                        } elseif (!empty($category_filter)) {
                            echo 'Categoria: ' . ucfirst(htmlspecialchars($category_filter));
                        } else {
                            echo 'Todos os pedidos';
                        }
                        ?>
                    </h5>
                    <span class="badge bg-primary"><?php echo count($orders); ?> pedido(s) encontrado(s)</span>
                </div>
            </div>
        </div>
        
        <!-- Lista de pedidos -->
        <?php if (!empty($orders)): ?>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($order['title']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($order['description'], 0, 100)) . '...'; ?></p>
                                
                                <?php if (!empty($order['category'])): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($order['category'])); ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        Por: <?php echo htmlspecialchars($order['user_name']); ?>
                                    </small>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        Criado em: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                                    </small>
                                </div>
                                
                                <?php if (!empty($order['status'])): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-success"><?php echo htmlspecialchars(ucfirst($order['status'])); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="card-footer">
                                <div class="d-grid gap-2">
                                    <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-primary btn-sm">
                                        Ver Detalhes
                                    </a>
                                    <?php if ($order['user_id'] != $user_id): ?>
                                        <a href="chat.php?user_id=<?php echo $order['user_id']; ?>" class="btn btn-outline-primary btn-sm">
                                            Conversar
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                <h4>Nenhum pedido encontrado</h4>
                <p>Tente ajustar os filtros de busca ou <a href="create_order.php">criar um novo pedido</a>.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Footer simples (substituindo o include) -->
    <footer class="bg-light mt-5 py-4">
        <div class="container text-center">
            <p class="mb-0">&copy; 2023 Plataforma de Economia Compartilhada</p>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>