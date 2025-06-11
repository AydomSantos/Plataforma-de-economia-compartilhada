<?php

require_once __DIR__ . '/../includes/db.php'; 

// Initialize error variable
$error = null;

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados do formulário
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Novo: processa upload da foto
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array(strtolower($ext), $allowed)) {
            $upload_dir = __DIR__ . '/../uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $filename = uniqid('profile_', true) . '.' . $ext;
            $dest = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)) {
                $profile_photo = 'uploads/' . $filename;
            } else {
                $error = "Erro ao fazer upload da foto.";
            }
        } else {
            $error = "Formato de imagem não suportado. Use jpg, jpeg, png ou gif.";
        }
    }

    // Validação básica dos dados
    if (empty($name)) {
        $error = "O nome é obrigatório.";
    } elseif (empty($email)) {
        $error = "O e-mail é obrigatório.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Formato de e-mail inválido.";
    } elseif (empty($password)) {
        $error = "A senha é obrigatória.";
    } elseif (strlen($password) < 6) {
        $error = "A senha deve ter pelo menos 6 caracteres.";
    } elseif ($password !== $confirm_password) {
        $error = "As senhas não coincidem.";
    }

    // Se não houver erros de validação
    if (!isset($error)) {
        // Verifica se o e-mail já existe no banco de dados
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Este e-mail já está cadastrado.";
        } else {
            // Hash da senha antes de salvar no banco de dados
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepara a query para inserir o novo usuário (agora inclui profile_photo)
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, profile_photo) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $profile_photo);

            // Executa a query
            if ($stmt->execute()) {
                // Registro bem-sucedido, redireciona para a página de login
                header("Location: login.php?registration_success=1");
                exit();
            } else {
                $error = "Erro ao registrar o usuário. Por favor, tente novamente.";
            }
        }

        $stmt->close();
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-3" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="mb-0 fs-4">Registro</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="register.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome:</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail:</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha:</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Senha:</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="profile_photo" class="form-label">Foto de Perfil:</label>
                                <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Registrar</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <p class="mb-0">Já tem uma conta? <a href="./login.php">Faça login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>