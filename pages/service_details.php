
<?php
require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$service_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$service_id) {
    header('Location: index.php?page=explore_orders');
    exit;
}

// Buscar detalhes do serviço
$service_query = "
    SELECT s.*, u.name as provider_name, u.profile_picture as provider_photo, 
           u.email as provider_email, u.rating as provider_rating, 
           c.name as category_name, c.color as category_color
    FROM services s
    JOIN users u ON s.user_id = u.id
    LEFT JOIN categories c ON s.category_id = c.id
    WHERE s.id = ? AND s.status = 'ativo'
";
$service_stmt = $conn->prepare($service_query);
$service_stmt->execute([$service_id]);
$service = $service_stmt->fetch();

if (!$service) {
    header('Location: index.php?page=explore_orders');
    exit;
}

// Buscar avaliações do serviço
$ratings_query = "
    SELECT r.*, u.name as rater_name
    FROM ratings r
    JOIN users u ON r.rater_id = u.id
    WHERE r.service_id = ? AND r.is_visible = 1
    ORDER BY r.created_at DESC
    LIMIT 10
";
$ratings_stmt = $conn->prepare($ratings_query);
$ratings_stmt->execute([$service_id]);
$ratings = $ratings_stmt->fetchAll();

// Incrementar contador de visualizações
$views_update = $conn->prepare("UPDATE services SET views_count = views_count + 1 WHERE id = ?");
$views_update->execute([$service_id]);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($service['title']); ?> - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container py-4">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php?page=home">Início</a></li>
                <li class="breadcrumb-item"><a href="index.php?page=explore_orders">Explorar Serviços</a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($service['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <!-- Conteúdo Principal -->
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <!-- Imagem do Serviço -->
                    <?php if (!empty($service['image_url'])): ?>
                        <img src="../<?php echo htmlspecialchars($service['image_url']); ?>" 
                             class="card-img-top" style="height: 300px; object-fit: cover;" 
                             alt="<?php echo htmlspecialchars($service['title']); ?>">
                    <?php endif; ?>
                    
                    <div class="card-body">
                        <!-- Título e Categoria -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <h1 class="h3"><?php echo htmlspecialchars($service['title']); ?></h1>
                            <?php if (!empty($service['category_name'])): ?>
                                <span class="badge fs-6" style="background-color: <?php echo htmlspecialchars($service['category_color'] ?? '#6c757d'); ?>">
                                    <?php echo htmlspecialchars($service['category_name']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Preço -->
                        <div class="mb-3">
                            <span class="h4 text-success">
                                R$ <?php echo number_format($service['price'], 2, ',', '.'); ?>
                            </span>
                            <span class="text-muted">/ <?php echo htmlspecialchars($service['price_unit']); ?></span>
                        </div>

                        <!-- Informações do Serviço -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-geo-alt text-muted me-2"></i>
                                    <span><?php echo htmlspecialchars($service['location'] ?: 'Não informado'); ?></span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-clock text-muted me-2"></i>
                                    <span><?php echo htmlspecialchars($service['duration_estimate'] ?: 'A combinar'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-laptop text-muted me-2"></i>
                                    <span class="badge bg-<?php echo $service['service_type'] == 'remoto' ? 'info' : ($service['service_type'] == 'presencial' ? 'warning' : 'primary'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $service['service_type'])); ?>
                                    </span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-eye text-muted me-2"></i>
                                    <span><?php echo number_format($service['views_count']); ?> visualizações</span>
                                </div>
                            </div>
                        </div>

                        <!-- Descrição -->
                        <h5>Descrição</h5>
                        <p class="mb-4"><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>

                        <!-- Requisitos -->
                        <?php if (!empty($service['requirements'])): ?>
                            <h5>Requisitos/Observações</h5>
                            <p class="mb-4"><?php echo nl2br(htmlspecialchars($service['requirements'])); ?></p>
                        <?php endif; ?>

                        <!-- Avaliações -->
                        <?php if (!empty($ratings)): ?>
                            <h5>Avaliações</h5>
                            <div class="row">
                                <?php foreach ($ratings as $rating): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-light">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <strong><?php echo htmlspecialchars($rating['rater_name']); ?></strong>
                                                    <div class="text-warning">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="bi bi-star<?php echo $i <= $rating['rating'] ? '-fill' : ''; ?>"></i>
                                                        <?php endfor; ?>
                                                    </div>
                                                </div>
                                                <?php if (!empty($rating['comment'])): ?>
                                                    <p class="card-text small mb-0"><?php echo htmlspecialchars($rating['comment']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Card do Prestador -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Prestador de Serviço</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <?php if (!empty($service['provider_photo'])): ?>
                                <img src="../<?php echo htmlspecialchars($service['provider_photo']); ?>" 
                                     class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;" 
                                     alt="<?php echo htmlspecialchars($service['provider_name']); ?>">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" 
                                     style="width: 80px; height: 80px; font-size: 24px;">
                                    <?php echo strtoupper(substr($service['provider_name'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <h6><?php echo htmlspecialchars($service['provider_name']); ?></h6>
                        
                        <?php if ($service['provider_rating'] > 0): ?>
                            <div class="mb-3">
                                <div class="text-warning">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="bi bi-star<?php echo $i <= round($service['provider_rating']) ? '-fill' : ''; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                                <small class="text-muted"><?php echo number_format($service['provider_rating'], 1); ?> / 5.0</small>
                            </div>
                        <?php endif; ?>
                        
                        <p class="text-muted small">
                            Membro desde <?php echo date('M/Y', strtotime($service['created_at'])); ?>
                        </p>
                    </div>
                </div>

                <!-- Ações -->
                <?php if ($service['user_id'] != $user_id): ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg" onclick="createContract(<?php echo $service['id']; ?>)">
                                    <i class="bi bi-handshake"></i> Contratar Serviço
                                </button>
                                
                                <a href="index.php?page=chat&user_id=<?php echo $service['user_id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-chat-dots"></i> Enviar Mensagem
                                </a>
                                
                                <button class="btn btn-outline-warning" onclick="addToFavorites(<?php echo $service['id']; ?>)">
                                    <i class="bi bi-heart"></i> Adicionar aos Favoritos
                                </button>
                                
                                <button class="btn btn-outline-secondary" onclick="reportService(<?php echo $service['id']; ?>)">
                                    <i class="bi bi-flag"></i> Reportar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle"></i>
                                Este é o seu serviço
                            </div>
                            <div class="d-grid gap-2">
                                <a href="index.php?page=edit_service&id=<?php echo $service['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Editar Serviço
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function createContract(serviceId) {
            window.location.href = `index.php?page=create_contract&service_id=${serviceId}`;
        }

        function addToFavorites(serviceId) {
            // Implementar adição aos favoritos via AJAX
            fetch('api/add_favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ service_id: serviceId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Serviço adicionado aos favoritos!');
                } else {
                    alert('Erro ao adicionar aos favoritos.');
                }
            });
        }

        function reportService(serviceId) {
            if (confirm('Deseja reportar este serviço?')) {
                // Implementar sistema de reports
                alert('Obrigado pelo report. Analisaremos em breve.');
            }
        }
    </script>
</body>
</html>
