<?php
require_once __DIR__ . '/../includes/db.php'; // Verifique o caminho.

// Define o cabeçalho para indicar que a resposta é JSON.
header('Content-Type: application/json');

// Inicializa o array de resposta.
$response = ['success' => false, 'message' => ''];

// Verificar autenticação
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit;
}

$sender_id = $_SESSION['user_id'];
// Use o operador de coalescência nula (??) para evitar "undefined index"
$receiver_id = $_POST['receiver_id'] ?? null;
$content = $_POST['content'] ?? null;

// Validação dos dados de entrada
if (empty($receiver_id) || empty($content)) {
    $response['message'] = 'Dados incompletos (destinatário ou conteúdo da mensagem).';
    echo json_encode($response);
    exit;
}

// Validação básica do receiver_id como número
if (!is_numeric($receiver_id)) {
    $response['message'] = 'ID do destinatário inválido.';
    echo json_encode($response);
    exit;
}


try {
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");

    if (!$stmt) {
        error_log("Erro na preparação do INSERT em send_message.php: " . $conn->error);
        $response['message'] = 'Erro interno do servidor ao preparar o envio da mensagem.';
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("iis", $sender_id, $receiver_id, $content);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Mensagem enviada com sucesso.';
    } else {
        error_log("Erro na execução do INSERT em send_message.php: " . $stmt->error);
        $response['message'] = 'Falha ao enviar mensagem.';
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Exceção em send_message.php: " . $e->getMessage());
    $response['message'] = 'Ocorreu um erro inesperado ao enviar a mensagem: ' . $e->getMessage(); 
}

echo json_encode($response);
exit; 

