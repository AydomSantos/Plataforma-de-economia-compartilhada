<?php
require_once __DIR__ . '/../includes/db.php';

// Verifica se o usuário está logado e é o dono do pedido
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID do pedido não informado.";
    exit;
}

$order_id = intval($_GET['id']);

// Busca os dados do pedido
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Pedido não encontrado ou você não tem permissão para editá-lo.";
    exit;
}
$order = $result->fetch_assoc();
$stmt->close();

// Atualiza o pedido se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE orders SET title=?, description=?, category=?, status=? WHERE id=? AND user_id=?");
    $stmt->bind_param("ssssii", $title, $description, $category, $status, $order_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();

    echo "<div class='alert alert-success'>Pedido atualizado com sucesso!</div>";
    // Atualiza os dados exibidos no formulário
    $order['title'] = $title;
    $order['description'] = $description;
    $order['category'] = $category;
    $order['status'] = $status;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h2>Editar Pedido</h2>
    <form method="post">
        <div class="form-group">
            <label for="title">Título</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($order['title'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="description">Descrição</label>
            <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($order['description'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label for="category">Categoria</label>
            <input type="text" class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($order['category'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <input type="text" class="form-control" id="status" name="status" value="<?php echo htmlspecialchars($order['status'] ?? ''); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="http://localhost/index.php?page=view_order&id=<?php echo $order_id; ?>" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>