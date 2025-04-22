<?php
session_start();

// Verifica se a variável de sessão user_id está definida. Se não estiver,
// significa que o usuário não está logado, então redireciona para a página de login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Se a execução chegar até aqui, significa que o usuário está logado.
// Podemos então exibir o conteúdo da página inicial.

// Opcional: Obter informações do usuário da sessão
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
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
                        <a class="nav-link" href="marketplace.php"><i class="bi bi-shop"></i> Marketplace</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="services.php"><i class="bi bi-tools"></i> Serviços</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="community.php"><i class="bi bi-people-fill"></i> Comunidade</a>
                    </li>
                </ul>
                <div class="dropdown">
                    <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($user_name); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Meu Perfil</a></li>
                        <li><a class="dropdown-item" href="settings.php"><i class="bi bi-gear"></i> Configurações</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php"><i class="bi bi-box-arrow-right"></i> Sair</a></li>
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
                <h2 class="card-title">Bem-vindo, <?php echo htmlspecialchars($user_name); ?>!</h2>
                <p class="card-text">Explore nossa plataforma de economia compartilhada e descubra novas oportunidades para compartilhar, trocar e colaborar.</p>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-cart-plus text-primary" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Comprar e Vender</h5>
                        <p class="card-text">Encontre produtos usados em bom estado ou venda itens que você não usa mais.</p>
                        <a href="marketplace.php" class="btn btn-outline-primary">Explorar Marketplace</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-arrow-repeat text-success" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Trocar Serviços</h5>
                        <p class="card-text">Ofereça suas habilidades ou encontre alguém que possa ajudar com o que você precisa.</p>
                        <a href="services.php" class="btn btn-outline-success">Ver Serviços</a>
                    </div>
                </div>
            </div>
            <div class="col">
                <div class="card h-100 shadow-sm">
                    <div class="card-body text-center">
                        <i class="bi bi-people text-info" style="font-size: 2rem;"></i>
                        <h5 class="card-title mt-3">Comunidade</h5>
                        <p class="card-text">Conecte-se com pessoas que compartilham seus interesses e valores.</p>
                        <a href="community.php" class="btn btn-outline-info">Participar</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Atividades Recentes</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Novo item no marketplace</h6>
                        <small class="text-muted">3 horas atrás</small>
                    </div>
                    <p class="mb-1">Bicicleta em ótimo estado disponível para troca.</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Serviço adicionado</h6>
                        <small class="text-muted">1 dia atrás</small>
                    </div>
                    <p class="mb-1">Aulas de programação para iniciantes.</p>
                </a>
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">Evento comunitário</h6>
                        <small class="text-muted">2 dias atrás</small>
                    </div>
                    <p class="mb-1">Workshop de sustentabilidade no próximo sábado.</p>
                </a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>