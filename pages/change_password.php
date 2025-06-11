<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=login');
    exit;
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (empty($current) || empty($new) || empty($confirm)) {
        $message = '<div class="alert alert-danger">Preencha todos os campos.</div>';
    } elseif ($new !== $confirm) {
        $message = '<div class="alert alert-danger">As novas senhas não coincidem.</div>';
    } else {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($current, $hash)) {
            $message = '<div class="alert alert-danger">Senha atual incorreta.</div>';
        } else {
            $new_hash = password_hash($new, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $update->bind_param("si", $new_hash, $_SESSION['user_id']);
            if ($update->execute()) {
                $message = '<div class="alert alert-success">Senha alterada com sucesso!</div>';
            } else {
                $message = '<div class="alert alert-danger">Erro ao atualizar senha.</div>';
            }
            $update->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Alterar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header bg-secondary text-white">
                        <h3 class="mb-0"><i class="bi bi-key"></i> Alterar Senha</h3>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Senha Atual</label>
                                <input type="password" class="form-control" id="current_password" name="current_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="new_password" class="form-label">Nova Senha</label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                            </div>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Alterar Senha
                                </button>
                                <a href="../index.php?page=profile" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Voltar ao Perfil
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>