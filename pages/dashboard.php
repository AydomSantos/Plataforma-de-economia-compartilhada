
<?php
require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar informações do usuário
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Buscar estatísticas do usuário
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM services WHERE user_id = ? AND status = 'ativo') as active_services,
        (SELECT COUNT(*) FROM contracts WHERE provider_id = ? AND status = 'concluido') as completed_contracts,
        (SELECT COUNT(*) FROM contracts WHERE client_id = ? AND status = 'concluido') as hired_services,
        (SELECT AVG(rating) FROM ratings WHERE rated_id = ?) as avg_rating,
        (SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND is_read = 0) as unread_messages
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);
$stats = $stats_stmt->fetch();

// Buscar contratos recentes
$contracts_query = "
    SELECT c.*, s.title as service_title, u.name as other_party_name,
           CASE 
               WHEN c.provider_id = ? THEN 'prestador'
               ELSE 'cliente'
           END as user_role
    FROM contracts c
    JOIN services s ON c.service_id = s.id
    JOIN users u ON (CASE WHEN c.provider_id = ? THEN c.client_id ELSE c.provider_id END) = u.id
    WHERE c.provider_id = ? OR c.client_id = ?
    ORDER BY c.created_at DESC
    LIMIT 5
";
$contracts_stmt = $conn->prepare($contracts_query);
$contracts_stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$recent_contracts = $contracts_stmt->fetchAll();

// Buscar notificações recentes
$notifications_query = "
    SELECT * FROM notifications 
    WHERE user_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5
";
$notifications_stmt = $conn->prepare($notifications_query);
$notifications_stmt->execute([$user_id]);
$notifications = $notifications_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header do Dashboard -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">Dashboard</h1>
                        <p class="text-muted">Bem-vindo, <?php echo htmlspecialchars($user['name']); ?>!</p>
                    </div>
                    <div>
                        <a href="index.php?page=create_service" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Novo Serviço
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards de Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-primary">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['active_services'] ?: 0; ?></h4>
                                <p class="card-text">Serviços Ativos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-briefcase fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-success">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['completed_contracts'] ?: 0; ?></h4>
                                <p class="card-text">Trabalhos Concluídos</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-check-circle fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-info">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo number_format($stats['avg_rating'] ?: 0, 1); ?></h4>
                                <p class="card-text">Avaliação Média</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-star fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-3 col-sm-6 mb-3">
                <div class="card text-white bg-warning">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="card-title"><?php echo $stats['unread_messages'] ?: 0; ?></h4>
                                <p class="card-text">Mensagens Não Lidas</p>
                            </div>
                            <div class="align-self-center">
                                <i class="bi bi-envelope fs-1"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="row">
            <!-- Contratos Recentes -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-text"></i> Contratos Recentes
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_contracts)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-file-text text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2">Nenhum contrato encontrado</p>
                                <a href="index.php?page=explore_services" class="btn btn-outline-primary">
                                    Explorar Serviços
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Serviço</th>
                                            <th>Papel</th>
                                            <th>Outra Parte</th>
                                            <th>Status</th>
                                            <th>Data</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_contracts as $contract): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($contract['service_title']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $contract['user_role'] == 'prestador' ? 'primary' : 'secondary'; ?>">
                                                        <?php echo ucfirst($contract['user_role']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($contract['other_party_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($contract['status']) {
                                                            'pendente' => 'warning',
                                                            'aceito' => 'info',
                                                            'em_andamento' => 'primary',
                                                            'concluido' => 'success',
                                                            'cancelado', 'rejeitado' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $contract['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($contract['created_at'])); ?></td>
                                                <td>
                                                    <a href="index.php?page=contract_details&id=<?php echo $contract['id']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        Ver
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar com Notificações e Ações Rápidas -->
            <div class="col-lg-4">
                <!-- Notificações -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bell"></i> Notificações
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p class="text-muted">Nenhuma notificação</p>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="list-group-item border-0 px-0 <?php echo $notification['is_read'] ? '' : 'bg-light'; ?>">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                            <small><?php echo date('d/m', strtotime($notification['created_at'])); ?></small>
                                        </div>
                                        <p class="mb-1 small"><?php echo htmlspecialchars($notification['message']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Ações Rápidas -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning"></i> Ações Rápidas
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="index.php?page=create_service" class="btn btn-primary">
                                <i class="bi bi-plus-circle"></i> Criar Serviço
                            </a>
                            <a href="index.php?page=explore_services" class="btn btn-outline-primary">
                                <i class="bi bi-search"></i> Explorar Serviços
                            </a>
                            <a href="index.php?page=chat" class="btn btn-outline-primary">
                                <i class="bi bi-chat-dots"></i> Mensagens
                            </a>
                            <a href="index.php?page=financial" class="btn btn-outline-success">
                                <i class="bi bi-wallet2"></i> Financeiro
                            </a>
                            <a href="index.php?page=edit_profile" class="btn btn-outline-secondary">
                                <i class="bi bi-person-gear"></i> Configurações
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
