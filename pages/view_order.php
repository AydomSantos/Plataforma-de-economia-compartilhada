<?php

$order_id = $_GET['id'] ?? 0; 

if (!$order_id) {
    // Redireciona para uma página de exploração de pedidos ou home se não houver ID válido
    header("Location: index.php?page=explore_orders");
    exit();
}

// Obter os detalhes do pedido
$stmt = $conn->prepare("SELECT o.*, u.name, u.email, u.latitude, u.longitude 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: index.php?page=explore_orders'); // Ou index.php?page=error_404
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Obter a localização do usuário atual (se estiver logado)
$user_lat = $user_lng = null;
if (isset($_SESSION['user_id'])) {
    $user_query = "SELECT latitude, longitude FROM users WHERE id = ?";
    $user_stmt = $conn->prepare($user_query);
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    if ($user_row = $user_result->fetch_assoc()) {
        $user_lat = $user_row['latitude'];
        $user_lng = $user_row['longitude'];
    }
    $user_stmt->close();
}

// Função para calcular distância (certifique-se de que esta função está em um arquivo incluído globalmente, como db.php ou functions.php, ou defina-a aqui)
if (!function_exists('calculateDistance')) {
    function calculateDistance($lat1, $lon1, $lat2, $lon2, $unit = 'km') {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        }
        else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            $unit = strtolower($unit);
            if ($unit == "km") {
                return ($miles * 1.609344);
            } else if ($unit == "nmi") {
                return ($miles * 0.8684);
            } else {
                return $miles;
            }
        }
    }
}


// Calcular a distância se o usuário estiver logado e tiver localização
$distance = null;
if ($user_lat && $user_lng && $order['latitude'] && $order['longitude']) {
    $distance = calculateDistance($user_lat, $user_lng, $order['latitude'], $order['longitude']);
}

// Verificar se as coordenadas do pedido estão disponíveis para o mapa
$has_map = ($order['latitude'] && $order['longitude']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($order['title']); ?> - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css"> 
    <?php if ($has_map): ?>
    <style>
        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
    <?php endif; ?>
</head>
<body>
    <?php 
    // Incluir o header global, que deve estar no index.php, ou manter o caminho se for local
    // Se você não o está incluindo no index.php, então o caminho aqui está correto para pages/
    // include '../includes/header.php'; 
    ?>
    
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0"><?php echo htmlspecialchars($order['title']); ?></h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($order['product_image'])): ?>
                            <div class="mb-4 text-center">
                                <img src="uploads/<?php echo htmlspecialchars($order['product_image']); ?>" alt="Imagem do Produto" class="img-fluid" style="max-height: 300px;">
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <h5>Descrição:</h5>
                            <p><?php echo nl2br(htmlspecialchars($order['description'])); ?></p>
                        </div>
                        
                        <?php if ($has_map): ?>
                        <div class="mb-4">
                            <h5>Localização:</h5>
                            <div id="map"></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h5>Detalhes:</h5>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Categoria:</strong> <?php echo ucfirst(htmlspecialchars($order['category'])); ?></li>
                                    <li class="list-group-item"><strong>Status:</strong> <?php echo htmlspecialchars($order['status'] ?? 'Ativo'); ?></li>
                                    <li class="list-group-item"><strong>Data de Criação:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></li>
                                    <?php if ($distance !== null): ?>
                                        <li class="list-group-item"><strong>Distância:</strong> <?php echo number_format($distance, 1); ?> km</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Solicitante:</h5>
                                <ul class="list-group">
                                    <li class="list-group-item"><strong>Nome:</strong> <?php echo htmlspecialchars($order['name']); ?></li>
                                    <?php if (isset($_SESSION['user_id'])): ?>
                                        <li class="list-group-item"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $order['user_id']): ?>
                                <a href="index.php?page=edit_order&id=<?php echo $order_id; ?>" class="btn btn-warning me-2">Editar Pedido</a>
                                <a href="index.php?page=delete_order&id=<?php echo $order_id; ?>" class="btn btn-danger" onclick="return confirm('Tem certeza que deseja excluir este pedido?')">Excluir Pedido</a>
                            <?php elseif (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $order['user_id']): ?>
                                <a href="index.php?page=chat&user=<?php echo $order['user_id']; ?>" class="btn btn-success me-2">Entrar em Contato</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=explore_orders" class="btn btn-secondary">Voltar para a Lista</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php if ($has_map): ?>
    <script>
        function initMap() {
            // Coordenadas do pedido
            const orderLocation = {
                lat: <?php echo $order['latitude']; ?>,
                lng: <?php echo $order['longitude']; ?>
            };
            
            // Criar o mapa centralizado na localização do pedido
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 13,
                center: orderLocation,
            });
            
            // Marcador para a localização do pedido
            const orderMarker = new google.maps.Marker({
                position: orderLocation,
                map: map,
                title: "<?php echo htmlspecialchars($order['title']); ?>",
                icon: {
                    url: "http://maps.google.com/mapfiles/ms/icons/red-dot.png" // Ícone padrão do Google Maps
                }
            });
            
            // Adicionar janela de informação ao marcador do pedido
            const orderInfoWindow = new google.maps.InfoWindow({
                content: "<strong><?php echo htmlspecialchars($order['title']); ?></strong><br>Solicitado por: <?php echo htmlspecialchars($order['name']); ?>"
            });
            
            orderMarker.addListener("click", () => {
                orderInfoWindow.open(map, orderMarker);
            });
            
            <?php if ($user_lat && $user_lng && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $order['user_id']): ?>
            // Adicionar marcador para a localização do usuário atual
            const userLocation = {
                lat: <?php echo $user_lat; ?>,
                lng: <?php echo $user_lng; ?>
            };
            
            const userMarker = new google.maps.Marker({
                position: userLocation,
                map: map,
                title: "Sua localização",
                icon: {
                    url: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png" // Ícone padrão do Google Maps
                }
            });
            
            const userInfoWindow = new google.maps.InfoWindow({
                content: "<strong>Sua localização</strong>"
            });
            
            userMarker.addListener("click", () => {
                userInfoWindow.open(map, userMarker);
            });
            
            // Desenhar linha entre os dois pontos
            const path = new google.maps.Polyline({
                path: [orderLocation, userLocation],
                geodesic: true,
                strokeColor: "#FF0000",
                strokeOpacity: 1.0,
                strokeWeight: 2,
            });
            
            path.setMap(map);
            
            // Ajustar o zoom para mostrar ambos os marcadores
            const bounds = new google.maps.LatLngBounds();
            bounds.extend(orderLocation);
            bounds.extend(userLocation);
            map.fitBounds(bounds);
            <?php endif; ?>
        }
    </script>
    <script async defer
        src="https://maps.googleapis.com/maps/api/js?key=SUA_CHAVE_API&callback=initMap">
    </script>
    <?php endif; ?>
</body>
</html>