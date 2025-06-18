
<?php
require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Inicializar variáveis de filtro
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
$category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';
$price_filter = isset($_GET['price']) ? $_GET['price'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';

// Construir a query base
$base_query = "SELECT s.*, u.name as user_name, c.name as category_name, c.color as category_color
               FROM services s 
               JOIN users u ON s.user_id = u.id 
               LEFT JOIN categories c ON s.category_id = c.id 
               WHERE s.status = 'ativo'";

$params = [];

if (!empty($search_term)) {
    $base_query .= " AND (s.title LIKE ? OR s.description LIKE ?)";
    $search_param = "%{$search_term}%";
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category_filter)) {
    $base_query .= " AND c.id = ?";
    $params[] = $category_filter;
}

if (!empty($price_filter)) {
    switch ($price_filter) {
        case 'low':
            $base_query .= " AND s.price <= 50";
            break;
        case 'medium':
            $base_query .= " AND s.price BETWEEN 51 AND 200";
            break;
        case 'high':
            $base_query .= " AND s.price > 200";
            break;
    }
}

// Ordenação
$allowed_sorts = ['created_at', 'title', 'price'];
if (in_array($sort_by, $allowed_sorts)) {
    $order_direction = ($sort_by == 'price') ? 'ASC' : 'DESC';
    $base_query .= " ORDER BY s." . $sort_by . " " . $order_direction;
} else {
    $base_query .= " ORDER BY s.created_at DESC";
}

// Executar query
$stmt = $conn->prepare($base_query);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Buscar categorias para filtro
$categories_stmt = $conn->prepare("SELECT id, name FROM categories WHERE status = 'ativo' ORDER BY name");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();

include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Cabeçalho -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Explorar Serviços</h1>
            <p class="text-muted">Encontre os melhores profissionais para suas necessidades</p>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filtros de Busca</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <input type="hidden" name="page" value="explore_services">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="search" class="form-label">Buscar</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Digite palavras-chave..." 
                               value="<?php echo htmlspecialchars($search_term); ?>">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="category" class="form-label">Categoria</label>
                        <select class="form-select" id="category" name="category">
                            <option value="">Todas as categorias</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" 
                                        <?php echo ($category_filter == $category['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="price" class="form-label">Faixa de Preço</label>
                        <select class="form-select" id="price" name="price">
                            <option value="">Todos os preços</option>
                            <option value="low" <?php echo ($price_filter === 'low') ? 'selected' : ''; ?>>Até R$ 50</option>
                            <option value="medium" <?php echo ($price_filter === 'medium') ? 'selected' : ''; ?>>R$ 51 - R$ 200</option>
                            <option value="high" <?php echo ($price_filter === 'high') ? 'selected' : ''; ?>>Acima de R$ 200</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="sort" class="form-label">Ordenar por</label>
                        <select class="form-select" id="sort" name="sort">
                            <option value="created_at" <?php echo ($sort_by === 'created_at') ? 'selected' : ''; ?>>Mais recentes</option>
                            <option value="price" <?php echo ($sort_by === 'price') ? 'selected' : ''; ?>>Menor preço</option>
                            <option value="title" <?php echo ($sort_by === 'title') ? 'selected' : ''; ?>>Nome A-Z</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                        <a href="index.php?page=explore_services" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-clockwise"></i> Limpar Filtros
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <?php if (!empty($search_term)): ?>
                Resultados para "<?php echo htmlspecialchars($search_term); ?>"
            <?php else: ?>
                Todos os serviços
            <?php endif; ?>
        </h5>
        <span class="badge bg-primary fs-6"><?php echo count($services); ?> serviço(s) encontrado(s)</span>
    </div>

    <!-- Lista de Serviços -->
    <?php if (!empty($services)): ?>
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <?php if (!empty($service['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($service['image_url']); ?>" 
                                 class="card-img-top" style="height: 200px; object-fit: cover;" 
                                 alt="<?php echo htmlspecialchars($service['title']); ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h5>
                                <?php if (!empty($service['category_name'])): ?>
                                    <span class="badge" style="background-color: <?php echo $service['category_color'] ?? '#6c757d'; ?>">
                                        <?php echo htmlspecialchars($service['category_name']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars(substr($service['description'], 0, 100)) . '...'; ?>
                            </p>
                            
                            <div class="mb-2">
                                <span class="h5 text-success">
                                    R$ <?php echo number_format($service['price'], 2, ',', '.'); ?>
                                </span>
                                <small class="text-muted">/ <?php echo htmlspecialchars($service['price_unit']); ?></small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> <?php echo htmlspecialchars($service['user_name']); ?>
                                </small>
                            </div>
                            
                            <div class="mb-2">
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($service['location'] ?: 'Local flexível'); ?>
                                </small>
                            </div>
                        </div>
                        
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <a href="index.php?page=service_details&id=<?php echo $service['id']; ?>" 
                                   class="btn btn-primary btn-sm">
                                    <i class="bi bi-eye"></i> Ver Detalhes
                                </a>
                                <?php if ($service['user_id'] != $user_id): ?>
                                    <a href="index.php?page=chat&user_id=<?php echo $service['user_id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-chat-dots"></i> Conversar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="bi bi-search text-muted" style="font-size: 4rem;"></i>
                    <h4 class="mt-3">Nenhum serviço encontrado</h4>
                    <p class="text-muted">Tente ajustar os filtros de busca ou explore outras categorias.</p>
                    <a href="index.php?page=create_service" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Criar Novo Serviço
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>
