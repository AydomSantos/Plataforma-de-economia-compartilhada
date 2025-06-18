
<?php
// Verificar se estamos em uma subpasta
$base_url = (strpos($_SERVER['REQUEST_URI'], '/pages/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Economia Compartilhada</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: #2563eb;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?php echo $base_url; ?>index.php?page=home">
                <i class="bi bi-arrow-left-right me-2"></i>EconomiaShare
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo $base_url; ?>index.php?page=explore_services">
                                <i class="bi bi-search me-1"></i>Explorar Serviços
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo $base_url; ?>index.php?page=create_service">
                                <i class="bi bi-plus-circle me-1"></i>Criar Serviço
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo $base_url; ?>index.php?page=dashboard">
                                <i class="bi bi-speedometer2 me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo $base_url; ?>index.php?page=chat">
                                <i class="bi bi-chat-dots me-1"></i>Mensagens
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-white" href="#" role="button" data-bs-toggle="dropdown">
                                <span class="badge rounded-circle bg-warning text-dark me-1" style="font-size:0.8rem;">
                                    <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)); ?>
                                </span>
                                <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Usuário'); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>index.php?page=profile">
                                    <i class="bi bi-person me-2"></i>Meu Perfil
                                </a></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>index.php?page=dashboard">
                                    <i class="bi bi-speedometer2 me-2"></i>Dashboard
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo $base_url; ?>index.php?page=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i>Sair
                                </a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo $base_url; ?>index.php?page=login">
                                <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="<?php echo $base_url; ?>index.php?page=register">
                                <i class="bi bi-person-plus me-1"></i>Cadastrar
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
