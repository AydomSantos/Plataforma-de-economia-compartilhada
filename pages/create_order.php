<?php

require_once __DIR__ . '/../includes/db.php'; // Inclui a conexão com o banco de dados

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

// Categorias disponíveis
$categories = [
    'ferramentas' => 'Ferramentas',
    'eletronicos' => 'Eletrônicos',
    'livros' => 'Livros',
    'roupas' => 'Roupas',
    'moveis' => 'Móveis',
    'outros' => 'Outros'
];

$message = '';
$title = $description = $category = '';
$latitude = $longitude = null;

// Processar o formulário quando enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = $_POST['category'] ?? '';
    $latitude = $_POST['latitude'] ?? null;
    $longitude = $_POST['longitude'] ?? null;
    $user_id = $_SESSION['user_id'];

    // Validação básica
    if (empty($title) || empty($description) || empty($category)) {
        $message = '<div class="alert alert-danger">Todos os campos são obrigatórios.</div>';
    } else {
        // Inserir o pedido no banco de dados (incluindo latitude/longitude se fornecidos)
        $stmt = $conn->prepare(
            "INSERT INTO orders (user_id, title, description, category, latitude, longitude, product_image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $status = 'publicado';
        $stmt->bind_param(
            "isssddss",
            $user_id,
            $title,
            $description,
            $category,
            $latitude,
            $longitude,
            $product_image,
            $status
        );

        if ($stmt->execute()) {
            $message = '<div class="alert alert-success">Pedido criado com sucesso!</div>';
            // Limpar os campos do formulário
            $title = $description = $category = '';
            $latitude = $longitude = null;
        } else {
            $message = '<div class="alert alert-danger">Erro ao criar pedido: ' . htmlspecialchars($stmt->error) . '</div>';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Pedido - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">Criar Novo Pedido</h2>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>

                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-group mb-3">
                                <label for="title" class="form-label">Título</label>
                                <input type="text" class="form-control" id="title" name="title"
                                    value="<?php echo htmlspecialchars($title); ?>" required>
                            </div>

                            <div class="form-group mb-3">
                                <label for="description" class="form-label">Descrição</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>
                            </div>

                            <div class="form-group mb-3">
                                <label for="category" class="form-label">Categoria</label>
                                <select class="form-select" id="category" name="category" required>
                                    <option value="">Selecione uma categoria</option>
                                    <?php foreach ($categories as $key => $value): ?>
                                        <option value="<?php echo $key; ?>" <?php echo ($category === $key) ? 'selected' : ''; ?>>
                                            <?php echo $value; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Campos ocultos para armazenar a localização -->
                            <input type="hidden" id="user-latitude" name="latitude" value="<?php echo htmlspecialchars($latitude ?? ''); ?>">
                            <input type="hidden" id="user-longitude" name="longitude" value="<?php echo htmlspecialchars($longitude ?? ''); ?>">

                            <div class="form-group mb-3">
                                <button type="button" id="update-location-btn" class="btn btn-info">
                                    <i class="bi bi-geo-alt"></i> Atualizar Localização
                                </button>
                            </div>

                            <div class="form-group mb-3">
                                <label for="product_image" class="form-label">Imagem do Produto</label>
                                <input type="file" class="form-control" id="product_image" name="product_image" accept="image/*">
                                <div class="form-text">Formatos permitidos: jpg, jpeg, png, gif.</div>
                            </div>

                            <div class="form-group d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Criar Pedido
                                </button>
                                <a href="http://localhost/pages/home.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script para atualizar a localização do usuário
        document.getElementById('update-location-btn')?.addEventListener('click', function() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    const latitude = position.coords.latitude;
                    const longitude = position.coords.longitude;

                    // Atualizar os campos ocultos
                    document.getElementById('user-latitude').value = latitude;
                    document.getElementById('user-longitude').value = longitude;

                    alert('Localização atualizada com sucesso!');
                }, function() {
                    alert('Não foi possível obter sua localização. Verifique as permissões do navegador.');
                });
            } else {
                alert('Geolocalização não é suportada pelo seu navegador.');
            }
        });

        // Esconde o alerta de sucesso após 3 segundos
        setTimeout(function() {
            const alert = document.querySelector('.alert-success');
            if(alert) alert.style.display = 'none';
        }, 3000);
    </script>
</body>
</html>