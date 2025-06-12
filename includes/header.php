<?php
// Seu código PHP (se houver)
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
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background: #2563eb;">
        <div class="container">
            <a class="navbar-brand fw-bold" href="../index.php?page=home">Economia Compartilhada</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../index.php?page=explore_orders">Explorar Pedidos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../index.php?page=create_order">Criar Pedido</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../index.php?page=chat">Chat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="../index.php?page=profile">
                            <span class="badge rounded-circle bg-warning text-dark" style="font-size:1rem;">
                                <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)); ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Bootstrap JS (no final do body para melhor performance) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>