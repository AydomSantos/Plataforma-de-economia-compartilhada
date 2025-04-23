<?php
session_start();
require_once '../includes/db.php';

// Verifica se a variável de sessão user_id está definida. Se não estiver,
// significa que o usuário não está logado, então redireciona para a página de login.
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Se a execução chegar até aqui, significa que o usuário está logado.
// Podemos então exibir o conteúdo da página inicial.

// Obter informações do usuário da sessão
$user_id = $_SESSION['user_id'];
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';

// Obter informações adicionais do usuário do banco de dados
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

// Obter pedidos recentes
$recent_orders_query = "SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 3";
$recent_orders_result = $conn->query($recent_orders_query);
$recent_orders = [];
if ($recent_orders_result && $recent_orders_result->num_rows > 0) {
    while ($row = $recent_orders_result->fetch_assoc()) {
        $recent_orders[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página Inicial - Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">Economia Compartilhada</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php"><i class="bi bi-house-fill"></i> Início</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="explore_orders.php"><i class="bi bi-shop"></i> Explorar Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="create_order.php"><i class="bi bi-plus-circle"></i> Criar Pedido</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Meu Perfil</a>
                    </li>
                </ul>
                <div class="dropdown">
                    <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user_name ?: $user['name']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="edit_profile.php"><i class="bi bi-gear"></i> Editar Perfil</a></li>
                        <li><a class="dropdown-item" id="update-location-btn" href="#"><i class="bi bi-geo-alt"></i> Atualizar Localização</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-4">
        <!-- Welcome Card -->
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <h2 class="card-title">Bem-vindo, <?php echo htmlspecialchars($user_name ?: $user['name']); ?>!</h2>
                <p class="card-text">Explore nossa plataforma de economia compartilhada e descubra novas oportunidades para compartilhar, trocar e colaborar.</p>
                <?php if (!$user['latitude'] || !$user['longitude']): ?>
                <div class="alert alert-warning mt-3">
                    <i class="bi bi-exclamation-triangle-fill"></i> Sua localização não está configurada. 
                    <button id="update-location-alert-btn" class="btn btn-sm btn-warning ms-2">Atualizar Localização</button>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-search text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Explorar Pedidos</h5>
                        <p class="card-text">Encontre pedidos disponíveis na plataforma e ofereça ajuda à comunidade.</p>
                        <a href="explore_orders.php" class="btn btn-outline-primary">Ver Pedidos</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-plus-circle text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Criar Pedido</h5>
                        <p class="card-text">Crie um novo pedido para solicitar ajuda ou oferecer recursos para compartilhar.</p>
                        <a href="create_order.php" class="btn btn-outline-success">Criar Pedido</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-person text-info" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Meu Perfil</h5>
                        <p class="card-text">Gerencie seu perfil, veja seus pedidos e acompanhe suas atividades.</p>
                        <a href="profile.php" class="btn btn-outline-info">Ver Perfil</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Pedidos Recentes</h5>
            </div>
            <div class="list-group list-group-flush">
                <?php if (empty($recent_orders)): ?>
                    <div class="list-group-item">
                        <p class="mb-1">Nenhum pedido recente encontrado.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <a href="../view_order.php?id=<?php echo $order['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><?php echo htmlspecialchars($order['title']); ?></h6>
                                <small class="text-muted">
                                    <?php 
                                    $created_at = new DateTime($order['created_at']);
                                    $now = new DateTime();
                                    $interval = $created_at->diff($now);
                                    
                                    if ($interval->d > 0) {
                                        echo $interval->d . ' dia(s) atrás';
                                    } elseif ($interval->h > 0) {
                                        echo $interval->h . ' hora(s) atrás';
                                    } else {
                                        echo $interval->i . ' minuto(s) atrás';
                                    }
                                    ?>
                                </small>
                            </div>
                            <p class="mb-1"><?php echo htmlspecialchars(substr($order['description'], 0, 100)) . '...'; ?></p>
                            <small>Por: <?php echo htmlspecialchars($order['name']); ?></small>
                            <span class="badge bg-primary"><?php echo htmlspecialchars($order['category']); ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Economia Compartilhada</h5>
                    <p>Uma plataforma para conectar pessoas e promover o consumo consciente.</p>
                </div>
                <div class="col-md-3">
                    <h5>Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-white">Sobre nós</a></li>
                        <li><a href="terms.php" class="text-white">Termos de uso</a></li>
                        <li><a href="privacy.php" class="text-white">Política de privacidade</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Contato</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope"></i> contato@economiacompartilhada.com</li>
                        <li><i class="bi bi-telephone"></i> (11) 1234-5678</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; 2023 Economia Compartilhada. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Campos ocultos para armazenar a localização -->
    <input type="hidden" id="user-latitude" name="latitude" value="<?php echo $user['latitude']; ?>">
    <input type="hidden" id="user-longitude" name="longitude" value="<?php echo $user['longitude']; ?>">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para atualizar a localização do usuário
        document.getElementById('update-location-btn')?.addEventListener('click', function(e) {
            e.preventDefault();
            updateLocation(e);
        });
        
        document.getElementById('update-location-alert-btn')?.addEventListener('click', updateLocation);

        function updateLocation(e) {
            if (e) e.preventDefault();
            
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;
                    
                    // Enviar para o servidor via AJAX
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '../update_location.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (this.status === 200) {
                            alert('Localização atualizada com sucesso!');
                            location.reload();
                        } else {
                            alert('Erro ao atualizar localização.');
                        }
                    };
                    xhr.send(`latitude=${latitude}&longitude=${longitude}`);
                }, function() {
                    alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
                });
            } else {
                alert('Geolocalização não é suportada pelo seu navegador.');
            }
        }
    </script>
</body>
</html>