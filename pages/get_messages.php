<?php

require_once __DIR__ . '/../includes/db.php'; 


header('Content-Type: application/json');

// Inicializa o array de resposta.
$response = ['success' => false, 'messages' => [], 'message' => ''];

// Verificar se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    $response['message'] = 'Usuário não autenticado.';
    echo json_encode($response);
    exit; // Importante para parar a execução e enviar o JSON.
}

$current_user_id = $_SESSION['user_id'];

// Verificar se o ID do usuário para chat foi fornecido e é numérico
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $chat_user_id = $_GET['user'];
} else {
    $response['message'] = 'ID do usuário do chat não fornecido ou inválido.';
    echo json_encode($response);
    exit;
}

$last_message_timestamp = isset($_GET['since']) && is_string($_GET['since']) && !empty($_GET['since'])
                          ? $_GET['since']
                          : '1970-01-01 00:00:00'; 

try {
    // Consulta para buscar mensagens mais recentes que o timestamp fornecido
    $messages_query = "
        SELECT m.*, u.name as sender_name, u.profile_photo as sender_photo
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE ((m.sender_id = ? AND m.receiver_id = ?)
            OR (m.sender_id = ? AND m.receiver_id = ?))
            AND m.sent_at > ?
        ORDER BY m.sent_at ASC";

    $stmt = $conn->prepare($messages_query);

    if (!$stmt) {
        // Loga o erro para depuração em produção, não exibe ao usuário.
        error_log("Erro na preparação da consulta em get_messages.php: " . $conn->error);
        $response['message'] = 'Erro interno do servidor ao preparar a busca por mensagens.';
        echo json_encode($response);
        exit;
    }

    $stmt->bind_param("iiiis", $current_user_id, $chat_user_id, $chat_user_id, $current_user_id, $last_message_timestamp);
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
    }
    $stmt->close();

    $response['success'] = true;
    $response['messages'] = $messages;

} catch (Exception $e) {
    error_log("Erro na busca de mensagens em get_messages.php: " . $e->getMessage());
    $response['message'] = 'Erro ao buscar mensagens: ' . $e->getMessage(); 
}

// Envia a resposta JSON de volta ao cliente.
echo json_encode($response);
exit; 

