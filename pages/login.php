<?php

require_once '../includes/db.php'; // Inclui o arquivo de conexão com o banco de dados

// Inicia a sessão (é importante que session_start() seja chamado no início de cada página que usa sessões)
session_start();

// Initialize error variable
$error = null;

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtém os dados do formulário
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    // Validação básica
    if (empty($email)) {
        $error = "O e-mail é obrigatório.";
    } elseif (empty($password)) {
        $error = "A senha é obrigatória.";
    }

    // Se não houver erros de validação
    if (!isset($error)) {
        // Busca o usuário no banco de dados pelo e-mail
        $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            // O e-mail foi encontrado, agora verifica a senha
            $stmt->bind_result($user_id, $user_name, $hashed_password);
            $stmt->fetch();

            // Verifica se a senha fornecida corresponde ao hash no banco de dados
            if (password_verify($password, $hashed_password)) {
                // Autenticação bem-sucedida
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $user_name;

                // Redireciona para a página inicial (ou outra página protegida)
                header("Location: home.php");
                exit();
            } else {
                // Senha incorreta
                $error = "Senha incorreta.";
            }
        } else {
            // Usuário não encontrado
            $error = "E-mail não encontrado.";
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
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger mb-3" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center mb-0 fs-4">Login</h2>
                    </div>
                    <div class="card-body p-4">
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Senha</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Lembrar-me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Entrar</button>
                        </form>
                        <div class="text-center mt-3">
                            <a href="forgot-password.php" class="text-decoration-none">Esqueceu a senha?</a>
                        </div>
                        <div class="text-center mt-3">
                            <p class="mb-0">Ainda não tem uma conta? <a href="register.php">Registre-se</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
