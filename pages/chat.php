
<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Buscar conversas do usuário
$conversations_query = "SELECT DISTINCT 
    u.id, u.name,
    (SELECT content FROM messages 
     WHERE (sender_id = ? AND receiver_id = u.id) 
     OR (sender_id = u.id AND receiver_id = ?) 
     ORDER BY sent_at DESC LIMIT 1) as last_message
FROM users u
WHERE u.id IN (
    SELECT sender_id FROM messages WHERE receiver_id = ?
    UNION
    SELECT receiver_id FROM messages WHERE sender_id = ?
)";

$stmt = $conn->prepare($conversations_query);
$stmt->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
$stmt->execute();
$conversations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .chat-container { height: 70vh; }
        .messages-container { height: calc(100% - 60px); overflow-y: auto; }
        .message { margin: 10px; padding: 10px; border-radius: 10px; max-width: 70%; }
        .message-sent { background-color: #dcf8c6; margin-left: auto; }
        .message-received { background-color: #f8f9fa; }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Conversas</h5>
                    </div>
                    <div class="list-group list-group-flush" id="conversations">
                        <?php foreach ($conversations as $conv): ?>
                        <a href="#" class="list-group-item list-group-item-action" 
                           onclick="loadChat(<?= $conv['id'] ?>)">
                            <h6 class="mb-1"><?= htmlspecialchars($conv['name']) ?></h6>
                            <small class="text-muted"><?= $conv['last_message'] ? htmlspecialchars(substr($conv['last_message'], 0, 30)) . '...' : 'Nenhuma mensagem' ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card chat-container">
                    <div class="card-header" id="chat-header">
                        <h5 class="mb-0">Selecione uma conversa</h5>
                    </div>
                    <div class="card-body messages-container" id="messages">
                        <!-- Messages will be loaded here -->
                    </div>
                    <div class="card-footer">
                        <form id="message-form" class="d-none">
                            <div class="input-group">
                                <input type="text" id="message-input" class="form-control" 
                                       placeholder="Digite sua mensagem">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentChat = null;
        let lastMessageId = 0;
        
        function loadChat(userId) {
            currentChat = userId;
            document.getElementById('message-form').classList.remove('d-none');
            
            fetch(`get_messages.php?user_id=${userId}`)
                .then(response => response.json())
                .then(data => {
                    const messagesDiv = document.getElementById('messages');
                    messagesDiv.innerHTML = '';
                    
                    data.messages.forEach(message => {
                        appendMessage(message);
                    });
                    
                    scrollToBottom();
                    document.getElementById('chat-header').innerHTML = 
                        `<h5 class="mb-0">${data.user_name}</h5>`;
                });
        }
        
        function appendMessage(message) {
            const messagesDiv = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${message.sender_id == <?= $user_id ?> ? 'message-sent' : 'message-received'}`;
            messageDiv.textContent = message.content;
            messagesDiv.appendChild(messageDiv);
            lastMessageId = Math.max(lastMessageId, message.id);
        }
        
        function scrollToBottom() {
            const messagesDiv = document.getElementById('messages');
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }
        
        document.getElementById('message-form').onsubmit = function(e) {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const content = input.value.trim();
            
            if (content && currentChat) {
                fetch('send_message.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `receiver_id=${currentChat}&content=${encodeURIComponent(content)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        appendMessage({
                            sender_id: <?= $user_id ?>,
                            content: content
                        });
                        scrollToBottom();
                        input.value = '';
                    }
                });
            }
        };
        
        // Check for new messages every 3 seconds
        setInterval(() => {
            if (currentChat) {
                fetch(`check_new_messages.php?user_id=${currentChat}&last_id=${lastMessageId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.messages.length > 0) {
                            data.messages.forEach(message => {
                                appendMessage(message);
                            });
                            scrollToBottom();
                        }
                    });
            }
        }, 3000);
    </script>
</body>
</html>
