<?php
require_once __DIR__ . '/../includes/db.php';

// Buscar estatísticas da plataforma
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users WHERE status = 'ativo'");
    $stmt->execute();
    $total_users = $stmt->fetch()['total_users'];

    $stmt = $conn->prepare("SELECT COUNT(*) as total_services FROM services WHERE status = 'ativo'");
    $stmt->execute();
    $total_services = $stmt->fetch()['total_services'];

    $stmt = $conn->prepare("SELECT COUNT(*) as total_contracts FROM contracts WHERE status IN ('concluido', 'em_andamento')");
    $stmt->execute();
    $total_contracts = $stmt->fetch()['total_contracts'];

    // Buscar serviços em destaque
    $stmt = $conn->prepare("
        SELECT s.*, u.name as provider_name, c.name as category_name, c.icon, c.color
        FROM services s 
        JOIN users u ON s.user_id = u.id 
        JOIN categories c ON s.category_id = c.id 
        WHERE s.status = 'ativo' 
        ORDER BY s.views_count DESC, s.created_at DESC 
        LIMIT 6
    ");
    $stmt->execute();
    $featured_services = $stmt->fetchAll();

    // Buscar categorias
    $stmt = $conn->prepare("SELECT * FROM categories WHERE status = 'ativo' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();

} catch (Exception $e) {
    $total_users = 0;
    $total_services = 0;
    $total_contracts = 0;
    $featured_services = [];
    $categories = [];
}

// Verificar se o usuário está logado
$user_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? '';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EconomiaShare - Conectando Pessoas e Oportunidades</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-light">
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="bg-gradient-to-r from-blue-600 to-purple-600 text-white py-5">
        <div class="container">
            <div class="row align-items-center min-vh-50">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">
                        Conecte-se à <span class="text-warning">Economia Compartilhada</span>
                    </h1>
                    <p class="lead mb-4">
                        Descubra oportunidades, ofereça seus serviços e faça parte de uma comunidade 
                        que promove colaboração e sustentabilidade.
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <?php if (!$user_logged_in): ?>
                            <a href="index.php?page=register" class="btn btn-warning btn-lg px-4">
                                <i class="bi bi-person-plus me-2"></i>Começar Agora
                            </a>
                            <a href="index.php?page=login" class="btn btn-outline-light btn-lg px-4">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Fazer Login
                            </a>
                        <?php else: ?>
                            <a href="index.php?page=explore_orders" class="btn btn-warning btn-lg px-4">
                                <i class="bi bi-search me-2"></i>Explorar Serviços
                            </a>
                            <a href="index.php?page=create_order" class="btn btn-outline-light btn-lg px-4">
                                <i class="bi bi-plus-circle me-2"></i>Oferecer Serviço
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6 text-center">
                    <img src="https://images.unsplash.com/photo-1521737711867-e3b97375f902?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                         alt="Economia Compartilhada" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- Estatísticas -->
    <section class="py-5 bg-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="bi bi-people-fill text-primary mb-3" style="font-size: 3rem;"></i>
                            <h3 class="text-primary fw-bold"><?php echo number_format($total_users); ?></h3>
                            <p class="text-muted mb-0">Usuários Ativos</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="bi bi-briefcase-fill text-success mb-3" style="font-size: 3rem;"></i>
                            <h3 class="text-success fw-bold"><?php echo number_format($total_services); ?></h3>
                            <p class="text-muted mb-0">Serviços Disponíveis</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <i class="bi bi-handshake-fill text-warning mb-3" style="font-size: 3rem;"></i>
                            <h3 class="text-warning fw-bold"><?php echo number_format($total_contracts); ?></h3>
                            <p class="text-muted mb-0">Contratos Realizados</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Categorias -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Explore por Categoria</h2>
                <p class="text-muted">Encontre o serviço perfeito para suas necessidades</p>
            </div>
            <div class="row">
                <?php foreach ($categories as $category): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <a href="index.php?page=explore_orders&category=<?php echo $category['id']; ?>" 
                           class="text-decoration-none">
                            <div class="card border-0 shadow-sm h-100 category-card">
                                <div class="card-body text-center p-4">
                                    <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                         style="width: 60px; height: 60px; background-color: <?php echo $category['color']; ?>20;">
                                        <i class="<?php echo $category['icon']; ?> text-dark" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <h5 class="card-title text-dark"><?php echo htmlspecialchars($category['name']); ?></h5>
                                    <p class="card-text text-muted small"><?php echo htmlspecialchars($category['description']); ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Serviços em Destaque -->
    <?php if (!empty($featured_services)): ?>
    <section class="py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Serviços em Destaque</h2>
                <p class="text-muted">Descubra os serviços mais populares da nossa plataforma</p>
            </div>
            <div class="row">
                <?php foreach ($featured_services as $service): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card border-0 shadow-sm h-100">
                            <?php if ($service['image_url']): ?>
                                <img src="<?php echo htmlspecialchars($service['image_url']); ?>" 
                                     class="card-img-top" style="height: 200px; object-fit: cover;" 
                                     alt="<?php echo htmlspecialchars($service['title']); ?>">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                    <i class="<?php echo $service['icon']; ?> text-muted" style="font-size: 3rem;"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge rounded-pill" style="background-color: <?php echo $service['color']; ?>20; color: <?php echo $service['color']; ?>;">
                                        <i class="<?php echo $service['icon']; ?> me-1"></i>
                                        <?php echo htmlspecialchars($service['category_name']); ?>
                                    </span>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($service['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars(substr($service['description'], 0, 100)); ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-primary">R$ <?php echo number_format($service['price'], 2, ',', '.'); ?></strong>
                                        <span class="text-muted">/ <?php echo $service['price_unit']; ?></span>
                                    </div>
                                    <small class="text-muted">por <?php echo htmlspecialchars($service['provider_name']); ?></small>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div class="d-flex align-items-center">
                                        <?php if ($service['rating_average'] > 0): ?>
                                            <div class="text-warning me-1">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi bi-star<?php echo $i <= $service['rating_average'] ? '-fill' : ''; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted">(<?php echo $service['rating_count']; ?>)</small>
                                        <?php else: ?>
                                            <small class="text-muted">Sem avaliações</small>
                                        <?php endif; ?>
                                    </div>
                                    <a href="index.php?page=view_order&id=<?php echo $service['id']; ?>" 
                                       class="btn btn-outline-primary btn-sm">Ver Detalhes</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center mt-4">
                <a href="index.php?page=explore_orders" class="btn btn-primary btn-lg">
                    Ver Todos os Serviços <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Como Funciona -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold">Como Funciona</h2>
                <p class="text-muted">Simples, seguro e eficiente</p>
            </div>
            <div class="row">
                <div class="col-md-4 text-center mb-4">
                    <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <span class="fw-bold fs-3">1</span>
                    </div>
                    <h4>Cadastre-se</h4>
                    <p class="text-muted">Crie sua conta gratuitamente e complete seu perfil</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="rounded-circle bg-success text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <span class="fw-bold fs-3">2</span>
                    </div>
                    <h4>Explore ou Ofereça</h4>
                    <p class="text-muted">Encontre serviços que precisa ou ofereça suas habilidades</p>
                </div>
                <div class="col-md-4 text-center mb-4">
                    <div class="rounded-circle bg-warning text-white d-inline-flex align-items-center justify-content-center mb-3" 
                         style="width: 80px; height: 80px;">
                        <span class="fw-bold fs-3">3</span>
                    </div>
                    <h4>Conecte-se</h4>
                    <p class="text-muted">Converse, negocie e realize negócios com segurança</p>
                </div>
            </div>
        </div>
    </section>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <style>
    .category-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
    }
    .min-vh-50 {
        min-height: 50vh;
    }
    </style>
</body>
</html>