
<?php
require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Buscar categorias
$categories_stmt = $conn->prepare("SELECT * FROM categories WHERE status = 'ativo' ORDER BY name");
$categories_stmt->execute();
$categories = $categories_stmt->fetchAll();

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $price_unit = $_POST['price_unit'];
    $service_type = $_POST['service_type'];
    $duration_estimate = trim($_POST['duration_estimate']);
    $requirements = trim($_POST['requirements']);
    $location = trim($_POST['location']);

    // Validação básica
    if (empty($title) || empty($description) || empty($category_id) || $price <= 0) {
        $message = '<div class="alert alert-danger">Todos os campos obrigatórios devem ser preenchidos.</div>';
    } else {
        // Upload de imagem se fornecida
        $image_url = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if (in_array($ext, $allowed)) {
                $upload_dir = __DIR__ . '/../uploads/services/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $filename = uniqid('service_', true) . '.' . $ext;
                $dest = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_url = 'uploads/services/' . $filename;
                }
            }
        }

        // Inserir serviço
        $insert_stmt = $conn->prepare("
            INSERT INTO services (user_id, category_id, title, description, price, price_unit, 
                                service_type, duration_estimate, requirements, location, image_url, 
                                status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', NOW())
        ");

        if ($insert_stmt->execute([
            $user_id, $category_id, $title, $description, $price, $price_unit,
            $service_type, $duration_estimate, $requirements, $location, $image_url
        ])) {
            $message = '<div class="alert alert-success">Serviço criado com sucesso!</div>';
            // Limpar campos
            $title = $description = $duration_estimate = $requirements = $location = '';
            $price = 0;
        } else {
            $message = '<div class="alert alert-danger">Erro ao criar serviço. Tente novamente.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Serviço - Plataforma de Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0">
                            <i class="bi bi-plus-circle"></i> Criar Novo Serviço
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="title" class="form-label">Título do Serviço *</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo htmlspecialchars($title ?? ''); ?>" required>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="category_id" class="form-label">Categoria *</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                    <?php echo (isset($category_id) && $category_id == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descrição *</label>
                                <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                                <div class="form-text">Descreva detalhadamente o serviço que você oferece.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label">Preço *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">R$</span>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               min="0" step="0.01" value="<?php echo $price ?? ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="price_unit" class="form-label">Unidade de Preço</label>
                                    <select class="form-select" id="price_unit" name="price_unit">
                                        <option value="hora" <?php echo (isset($price_unit) && $price_unit == 'hora') ? 'selected' : ''; ?>>Por Hora</option>
                                        <option value="dia" <?php echo (isset($price_unit) && $price_unit == 'dia') ? 'selected' : ''; ?>>Por Dia</option>
                                        <option value="projeto" <?php echo (isset($price_unit) && $price_unit == 'projeto') ? 'selected' : ''; ?>>Por Projeto</option>
                                        <option value="mes" <?php echo (isset($price_unit) && $price_unit == 'mes') ? 'selected' : ''; ?>>Por Mês</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="service_type" class="form-label">Tipo de Serviço</label>
                                    <select class="form-select" id="service_type" name="service_type">
                                        <option value="ambos" <?php echo (isset($service_type) && $service_type == 'ambos') ? 'selected' : ''; ?>>Presencial e Remoto</option>
                                        <option value="presencial" <?php echo (isset($service_type) && $service_type == 'presencial') ? 'selected' : ''; ?>>Apenas Presencial</option>
                                        <option value="remoto" <?php echo (isset($service_type) && $service_type == 'remoto') ? 'selected' : ''; ?>>Apenas Remoto</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="duration_estimate" class="form-label">Duração Estimada</label>
                                    <input type="text" class="form-control" id="duration_estimate" name="duration_estimate" 
                                           value="<?php echo htmlspecialchars($duration_estimate ?? ''); ?>" 
                                           placeholder="Ex: 2-3 horas, 1 semana">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="location" class="form-label">Localização</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo htmlspecialchars($location ?? ''); ?>" 
                                       placeholder="Cidade, Estado ou 'Remoto'">
                            </div>

                            <div class="mb-3">
                                <label for="requirements" class="form-label">Requisitos/Observações</label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="3"><?php echo htmlspecialchars($requirements ?? ''); ?></textarea>
                                <div class="form-text">Informações adicionais, requisitos específicos, etc.</div>
                            </div>

                            <div class="mb-4">
                                <label for="image" class="form-label">Imagem do Serviço</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                <div class="form-text">Formatos aceitos: JPG, JPEG, PNG, GIF</div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="index.php?page=dashboard" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="bi bi-save"></i> Criar Serviço
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
