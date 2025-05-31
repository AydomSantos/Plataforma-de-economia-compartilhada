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

// Obter pedidos recentes (últimos 5)
$recent_orders_query = "SELECT r.*, u.name FROM requests r 
                       JOIN users u ON r.user_id = u.id 
                       ORDER BY r.created_at DESC 
                       LIMIT 5";
$recent_orders_result = $conn->query($recent_orders_query);
$recent_orders = [];

if ($recent_orders_result) {
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
    <title>Dashboard | Economia Compartilhada</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }
        
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .notification-bell {
            position: relative;
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background-color: #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: bold;
        }
        
        .order-card {
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }
        
        .order-card:hover {
            transform: translateX(5px);
            border-left-color: #4f46e5;
        }
        
        .category-badge {
            transition: all 0.2s ease;
        }
        
        .category-badge:hover {
            transform: scale(1.05);
        }
        
        .avatar-ring {
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .avatar-ring:hover {
            border-color: #4f46e5;
        }
        
        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation Bar -->
    <nav class="gradient-bg shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <a href="home.php" class="flex items-center text-white font-bold text-xl">
                        <i class="fas fa-handshake mr-2"></i>
                        <span class="hidden sm:inline">Economia Compartilhada</span>
                        <span class="sm:hidden">EC</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <div class="hidden md:flex space-x-6">
                        <a href="explore_orders.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                            <i class="fas fa-search mr-2"></i> Explorar
                        </a>
                        <a href="create_order.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                            <i class="fas fa-plus-circle mr-2"></i> Criar
                        </a>
                        <a href="chat.php" class="text-white hover:text-gray-200 px-3 py-2 rounded-md text-sm font-medium flex items-center">
                            <i class="fas fa-comments mr-2"></i> Chat
                        </a>
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <button class="notification-bell text-white p-2 rounded-full hover:bg-indigo-700 smooth-transition">
                            <i class="fas fa-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        
                        <div class="relative">
                            <a href="profile.php" class="flex items-center">
                                <div class="avatar-ring rounded-full overflow-hidden h-8 w-8">
                                    <img class="h-full w-full object-cover"
                                         src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_name ?: $user['name']); ?>&background=4f46e5&color=fff"
                                         alt="Avatar">
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Mobile Navigation -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 z-10">
        <div class="flex justify-around py-3">
            <a href="explore_orders.php" class="text-gray-600 hover:text-indigo-600 flex flex-col items-center">
                <i class="fas fa-search text-lg"></i>
                <span class="text-xs mt-1">Explorar</span>
            </a>
            <a href="create_order.php" class="text-gray-600 hover:text-indigo-600 flex flex-col items-center">
                <i class="fas fa-plus-circle text-lg"></i>
                <span class="text-xs mt-1">Criar</span>
            </a>
            <a href="chat.php" class="text-gray-600 hover:text-indigo-600 flex flex-col items-center">
                <i class="fas fa-comments text-lg"></i>
                <span class="text-xs mt-1">Chat</span>
            </a>
            <a href="profile.php" class="text-gray-600 hover:text-indigo-600 flex flex-col items-center">
                <i class="fas fa-user text-lg"></i>
                <span class="text-xs mt-1">Perfil</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 flex-grow pb-16 md:pb-0">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8 animate-fadeIn" style="animation-delay: 0.1s;">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">
                        Olá, <span class="text-indigo-600"><?php echo htmlspecialchars($user_name ?: $user['name']); ?></span>!
                    </h2>
                    <p class="mt-2 text-gray-600">
                        O que você gostaria de fazer hoje?
                    </p>
                </div>
                
                <?php if (empty($user['latitude']) || empty($user['longitude'])): ?>
                <div class="mt-4 md:mt-0 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-yellow-500"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                Sua localização não está configurada. Isso pode afetar sua experiência.
                            </p>
                            <div class="mt-2">
                                <button id="update-location-alert-btn" class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Atualizar Localização
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Explore Card -->
            <div class="bg-white overflow-hidden shadow rounded-xl card-hover animate-fadeIn" style="animation-delay: 0.2s;">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                            <i class="fas fa-search text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-5 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Explorar Pedidos</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Encontre oportunidades perto de você
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <a href="explore_orders.php" class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800">
                        Ver todos <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Create Card -->
            <div class="bg-white overflow-hidden shadow rounded-xl card-hover animate-fadeIn" style="animation-delay: 0.3s;">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                            <i class="fas fa-plus-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-5 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Criar Pedido</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Ofereça algo ou solicite ajuda
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <a href="create_order.php" class="inline-flex items-center text-sm font-medium text-green-600 hover:text-green-800">
                        Criar agora <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="bg-white overflow-hidden shadow rounded-xl card-hover animate-fadeIn" style="animation-delay: 0.4s;">
                <div class="p-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                            <i class="fas fa-user-circle text-purple-600 text-xl"></i>
                        </div>
                        <div class="ml-5 flex-1">
                            <h3 class="text-lg font-medium text-gray-900">Meu Perfil</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Gerencie suas informações
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4">
                    <a href="profile.php" class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-800">
                        Ver perfil <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h5 class="mb-4 text-xl font-bold text-gray-900">Pedidos Recentes</h5>
            <div class="space-y-3">
                <?php if (empty($recent_orders)): ?>
                    <div class="p-4 bg-gray-50 rounded-lg text-center">
                        <p class="text-gray-500">Nenhum pedido recente encontrado.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recent_orders as $order): ?>
                        <a href="view_order.php?id=<?php echo $order['id']; ?>" class="block p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            <div class="flex justify-between items-start">
                                <h6 class="font-semibold text-gray-900"><?php echo htmlspecialchars($order['title']); ?></h6>
                                <small class="text-gray-500 ml-2">
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
                            <p class="mt-2 text-gray-600"><?php echo htmlspecialchars(substr($order['description'], 0, 100)) . '...'; ?></p>
                            <div class="mt-2 flex items-center justify-between">
                                <small class="text-gray-500">Por: <?php echo htmlspecialchars($order['name']); ?></small>
                                <span class="inline-block bg-blue-500 text-white rounded-full px-2.5 py-0.5 text-xs font-semibold"><?php echo htmlspecialchars($order['category']); ?></span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h5 class="text-lg font-bold mb-4">Economia Compartilhada</h5>
                    <p class="text-gray-300">Uma plataforma para conectar pessoas e promover o consumo consciente.</p>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Links</h5>
                    <ul class="space-y-2">
                        <li><a href="about.php" class="text-gray-300 hover:text-white transition-colors">Sobre nós</a></li>
                        <li><a href="terms.php" class="text-gray-300 hover:text-white transition-colors">Termos de uso</a></li>
                        <li><a href="privacy.php" class="text-gray-300 hover:text-white transition-colors">Política de privacidade</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-lg font-bold mb-4">Contato</h5>
                    <ul class="space-y-2">
                        <li class="flex items-center"><i class="bi bi-envelope mr-2"></i> contato@economiacompartilhada.com</li>
                        <li class="flex items-center"><i class="bi bi-telephone mr-2"></i> (11) 1234-5678</li>
                    </ul>
                </div>
            </div>
            <hr class="my-6 border-gray-700">
            <div class="text-center text-gray-400">
                <p>&copy; 2023 Economia Compartilhada. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    
    <script>
        // Script para atualizar a localização do usuário
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