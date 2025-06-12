<?php
require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "ID do pedido não informado.";
    exit;
}

$order_id = intval($_GET['id']);

// Verifica se o pedido pertence ao usuário logado
$stmt = $conn->prepare("SELECT id FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pedido não encontrado ou você não tem permissão para excluí-lo.";
    exit;
}
$stmt->close();

// Exclui o pedido
$stmt = $conn->prepare("DELETE FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$stmt->close();

header("Location: index.php?msg=Pedido excluído com sucesso");
exit;