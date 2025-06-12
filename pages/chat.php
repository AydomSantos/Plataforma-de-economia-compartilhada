<?php
// pages/chat.php
// session_start() is assumed to be called in index.php (front controller)

require_once __DIR__ . '/../includes/db.php';

// Verificar se o usuário está logado
// This check is good here, but also consider having a similar check in index.php's central authentication logic
// to prevent any direct access to pages/chat.php without being logged in.
if (!isset($_SESSION['user_id'])) {
    // Correct redirect to the login page via the front controller
    header('Location: index.php?page=login');
    exit;
}

$current_user_id = $_SESSION['user_id'];

// Verificar se o ID do usuário para chat foi fornecido
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $chat_user_id = $_GET['user'];

    // Verificar se o usuário existe
    $user_stmt = $conn->prepare("SELECT id, name, profile_photo FROM users WHERE id = ?"); // Added profile_photo
    $user_stmt->bind_param("i", $chat_user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();

    if ($user_result->num_rows === 0) {
        $chat_user_id = null; // User not found
    } else {
        $chat_user = $user_result->fetch_assoc();
    }
    $user_stmt->close();
} else {
    $chat_user_id = null;
}

// Obter conversas do usuário atual
// This query might need to select profile_photo for conversation list if you want to display it
$conversations_query = "
    SELECT DISTINCT
        CASE
            WHEN m.sender_id = ? THEN m.receiver_id
            ELSE m.sender_id
        END as user_id,
        u.name,
        u.profile_photo, -- Added profile_photo here
        (SELECT MAX(sent_at) FROM messages
         WHERE (sender_id = ? AND receiver_id = user_id)
            OR (sender_id = user_id AND receiver_id = ?)) as last_message_time
    FROM messages m
    JOIN users u ON u.id = CASE
                            WHEN m.sender_id = ? THEN m.receiver_id
                            ELSE m.sender_id
                        END
    WHERE m.sender_id = ? OR m.receiver_id = ?
    ORDER BY last_message_time DESC";

$conversations_stmt = $conn->prepare($conversations_query);
$conversations_stmt->bind_param("iiiiii", $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id, $current_user_id);
$conversations_stmt->execute();
$conversations_result = $conversations_stmt->get_result();
$conversations = $conversations_result->fetch_all(MYSQLI_ASSOC);
$conversations_stmt->close();

// Obter mensagens entre os dois usuários se um chat estiver selecionado
$messages = [];
if ($chat_user_id) {
    $messages_query = "
        SELECT m.*, u.name as sender_name, u.profile_photo as sender_photo
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = ? AND m.receiver_id = ?)
            OR (m.sender_id = ? AND m.receiver_id = ?)
        ORDER BY m.sent_at ASC";

    $messages_stmt = $conn->prepare($messages_query);
    $messages_stmt->bind_param("iiii", $current_user_id, $chat_user_id, $chat_user_id, $current_user_id);
    $messages_stmt->execute();
    $messages_result = $messages_stmt->get_result();
    $messages = $messages_result->fetch_all(MYSQLI_ASSOC);
    $messages_stmt->close();
}

// Obter informações do usuário atual (for displaying current user's name/photo in messages)
$user_query = "SELECT name, profile_photo FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$current_user_data = $user_result->fetch_assoc(); 
$user_stmt->close();

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - Economia Compartilhada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Your existing CSS styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .chat-container {
            height: calc(100vh - 140px);
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 20px;
        }

        .conversations-sidebar {
            background-color: #fff;
            border-right: 1px solid #e9ecef;
            height: 100%;
            overflow-y: auto;
        }

        .chat-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px;
        }

        .conversation-item {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            cursor: pointer;
            transition: all 0.2s;
        }

        .conversation-item:hover {
            background-color: #f1f3f5;
        }

        .conversation-item.active {
            background-color: #e9ecef;
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            margin-right: 10px;
            overflow: hidden; /* Ensure image fits */
        }
        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Cover the avatar space */
        }


        .chat-messages {
            height: calc(100% - 120px);
            overflow-y: auto;
            padding: 15px;
            background-color: #f8f9fa;
        }

        .message {
            margin-bottom: 15px;
            max-width: 75%;
            position: relative;
        }

        .message-sent {
            margin-left: auto;
            background-color: #dcf8c6;
            border-radius: 15px 0 15px 15px;
            padding: 10px 15px;
        }

        .message-received {
            margin-right: auto;
            background-color: white;
            border-radius: 0 15px 15px 15px;
            padding: 10px 15px;
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
        }

        .message-time {
            font-size: 0.7rem;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }

        .chat-input {
            padding: 15px;
            background-color: white;
            border-top: 1px solid #e9ecef;
        }

        .chat-input form {
            display: flex;
            align-items: center;
        }

        .chat-input input {
            flex-grow: 1;
            border: 1px solid #ced4da;
            border-radius: 20px;
            padding: 8px 15px;
            margin-right: 10px;
        }

        .chat-input button {
            background-color: #4169E1;
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .chat-input button:hover {
            background-color: #3158d2;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #6c757d;
            text-align: center;
            padding: 20px;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            color: #adb5bd;
        }

        .new-conversation-btn {
            background-color: #4169E1;
            color: white;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .new-conversation-btn:hover {
            background-color: #3158d2;
        }

        /* Modal specific styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.5); /* Black w/ opacity */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto; /* This will center the modal */
            padding: 20px;
            border: 1px solid #888;
            border-radius: 8px;
            width: 90%; /* Adjust as needed */
            max-width: 500px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: relative; /* Needed for absolute positioning of close button */
        }

        .close {
            color: #aaa;
            float: right; /* Position to the top right */
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            position: absolute;
            right: 15px;
            top: 10px;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
        }

        .search-results {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #e9ecef;
            border-radius: 5px;
            margin-top: 10px;
        }

        .search-item {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            cursor: pointer;
            display: flex;
            align-items: center;
            background-color: #fff;
            transition: background-color 0.2s;
        }

        .search-item:last-child {
            border-bottom: none;
        }

        .search-item:hover {
            background-color: #f8f9fa;
        }

        .search-item .avatar {
            width: 35px;
            height: 35px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="chat-container row">
            <div class="conversations-sidebar col-md-4 col-lg-3 p-0">
                <div class="chat-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Conversas</h5>
                    <button class="new-conversation-btn" id="new-conversation-btn">
                        <i class="bi bi-plus-lg me-1"></i> Nova
                    </button>
                </div>

                <div class="conversations-list">
                    <?php if (empty($conversations)): ?>
                        <div class="p-3 text-center text-muted">
                            <p>Nenhuma conversa encontrada.</p>
                            <p>Inicie uma nova conversa!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($conversations as $conversation): ?>
                            <a href="index.php?page=chat&user=<?php echo $conversation['user_id']; ?>" class="text-decoration-none">
                                <div class="conversation-item d-flex align-items-center <?php echo ($chat_user_id == $conversation['user_id']) ? 'active' : ''; ?>">
                                    <div class="avatar" style="background-color: <?php echo sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>">
                                        <?php if (!empty($conversation['profile_photo'])): ?>
                                            <img src="<?php echo htmlspecialchars($conversation['profile_photo']); ?>" alt="Foto de Perfil">
                                        <?php else: ?>
                                            <?php echo strtoupper(substr($conversation['name'], 0, 2)); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($conversation['name']); ?></div>
                                        <div class="text-muted small">
                                            <?php
                                            if ($conversation['last_message_time']) {
                                                $last_time = new DateTime($conversation['last_message_time']);
                                                $now = new DateTime();
                                                $interval = $last_time->diff($now);

                                                if ($interval->y > 0) {
                                                    echo $interval->y . ' ano(s) atrás';
                                                } elseif ($interval->m > 0) {
                                                    echo $interval->m . ' mês(es) atrás';
                                                } elseif ($interval->d > 0) {
                                                    echo $interval->d . ' dia(s) atrás';
                                                } elseif ($interval->h > 0) {
                                                    echo $interval->h . ' hora(s) atrás';
                                                } elseif ($interval->i > 0) {
                                                    echo $interval->i . ' minuto(s) atrás';
                                                } else {
                                                    echo 'agora mesmo';
                                                }
                                            } else {
                                                echo 'Nenhuma mensagem';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-md-8 col-lg-9 p-0 d-flex flex-column">
                <?php if ($chat_user_id && isset($chat_user)): ?>
                    <div class="chat-header d-flex align-items-center">
                        <div class="avatar" style="background-color: <?php echo sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>">
                            <?php if (!empty($chat_user['profile_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($chat_user['profile_photo']); ?>" alt="Foto de Perfil">
                            <?php else: ?>
                                <?php echo strtoupper(substr($chat_user['name'], 0, 2)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="fw-bold"><?php echo htmlspecialchars($chat_user['name']); ?></div>
                    </div>

                    <div class="chat-messages" id="chat-messages">
                        <?php if (empty($messages)): ?>
                            <div class="empty-state">
                                <i class="bi bi-chat-dots"></i>
                                <p>Envie uma mensagem para iniciar a conversa.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?php echo ($message['sender_id'] == $current_user_id) ? 'message-sent' : 'message-received'; ?>">
                                    <div class="message-content">
                                        <?php echo nl2br(htmlspecialchars($message['content'])); ?>
                                    </div>
                                    <div class="message-time">
                                        <?php echo date('H:i', strtotime($message['sent_at'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="chat-input">
                        <form id="message-form">
                            <input type="hidden" id="receiver-id" value="<?php echo $chat_user_id; ?>">
                            <input type="text" id="message-content" placeholder="Digite sua mensagem..." autocomplete="off">
                            <button type="submit">
                                <i class="bi bi-send-fill"></i>
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="bi bi-chat-square-text"></i>
                        <h5>Bem-vindo ao Chat</h5>
                        <p>Selecione uma conversa ou inicie uma nova para começar a conversar.</p>
                        <button class="new-conversation-btn mt-3" id="empty-new-conversation-btn">
                            <i class="bi bi-plus-lg me-1"></i> Nova Conversa
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="new-conversation-modal" class="modal">
        <div class="modal-content">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Nova Conversa</h5>
                <span class="close">&times;</span>
            </div>
            <div>
                <input type="text" id="user-search" class="form-control" placeholder="Buscar usuário...">
                <div id="search-results" class="search-results mt-3"></div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Scroll to bottom of chat
        function scrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        }

        // Scroll to bottom on page load
        window.onload = scrollToBottom;

        // Send message
        document.getElementById('message-form')?.addEventListener('submit', function(e) {
            e.preventDefault();

            const receiverId = document.getElementById('receiver-id').value;
            const messageInput = document.getElementById('message-content');
            const content = messageInput.value.trim();

            if (content === '') return;

            // Send message to server via front controller
            fetch('index.php?page=send_message', { // Corrected URL
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `receiver_id=${receiverId}&content=${encodeURIComponent(content)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear message input
                    messageInput.value = '';

                    // Add message to chat
                    const chatMessages = document.getElementById('chat-messages');

                    // Remove empty state if it exists
                    const emptyState = chatMessages.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message message-sent';

                    const now = new Date();
                    const hours = now.getHours().toString().padStart(2, '0');
                    const minutes = now.getMinutes().toString().padStart(2, '0');

                    messageDiv.innerHTML = `
                        <div class="message-content">${content.replace(/\n/g, '<br>')}</div>
                        <div class="message-time">${hours}:${minutes}</div>
                    `;
                    chatMessages.appendChild(messageDiv);

                    // Scroll to bottom
                    scrollToBottom();
                } else {
                    alert('Erro ao enviar mensagem: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao enviar mensagem. Tente novamente.');
            });
        });

        // Check for new messages every 3 seconds
        <?php if ($chat_user_id): ?>
        let lastMessageTime = '<?php echo !empty($messages) ? end($messages)['sent_at'] : ''; ?>'; // Use end() for last element

        function checkNewMessages() {
            const receiverId = document.getElementById('receiver-id').value;

            fetch(`index.php?page=get_messages&user=${receiverId}&since=${encodeURIComponent(lastMessageTime)}`) // Corrected URL
            .then(response => response.json())
            .then(data => {
                if (data.success && data.messages.length > 0) {
                    const chatMessages = document.getElementById('chat-messages');
                    const wasAtBottom = chatMessages.scrollHeight - chatMessages.clientHeight <= chatMessages.scrollTop + 50;

                    // Remove empty state if it exists
                    const emptyState = chatMessages.querySelector('.empty-state');
                    if (emptyState) {
                        emptyState.remove();
                    }

                    // Add new messages
                    data.messages.forEach(message => {
                        // Ensure we only add messages sent by the other user (not ourselves)
                        if (message.sender_id != <?php echo $current_user_id; ?>) {
                            const messageDiv = document.createElement('div');
                            messageDiv.className = 'message message-received';

                            const messageTime = new Date(message.sent_at);
                            const hours = messageTime.getHours().toString().padStart(2, '0');
                            const minutes = messageTime.getMinutes().toString().padStart(2, '0');

                            messageDiv.innerHTML = `
                                <div class="message-content">${message.content.replace(/\n/g, '<br>')}</div>
                                <div class="message-time">${hours}:${minutes}</div>
                            `;
                            chatMessages.appendChild(messageDiv);

                            // Update last message time only if a new message was added
                            lastMessageTime = message.sent_at;
                        }
                    });

                    // Scroll to bottom if user was at bottom before
                    if (wasAtBottom) {
                        scrollToBottom();
                    }
                }
            })
            .catch(error => {
                console.error('Erro ao verificar novas mensagens:', error);
            });
        }

        // Check for new messages every 3 seconds
        setInterval(checkNewMessages, 3000);
        <?php endif; ?>

        // Modal functionality
        const modal = document.getElementById('new-conversation-modal');
        const newConversationBtn = document.getElementById('new-conversation-btn');
        const emptyNewConversationBtn = document.getElementById('empty-new-conversation-btn'); // For the empty state button
        const closeBtn = document.querySelector('.close');
        const userSearchInput = document.getElementById('user-search');
        const searchResults = document.getElementById('search-results');

        // Open modal
        newConversationBtn?.addEventListener('click', function() {
            modal.style.display = 'flex'; // Use flex to center with justify/align-items
            userSearchInput.focus();
            searchResults.innerHTML = ''; // Clear previous search results
            userSearchInput.value = ''; // Clear search input
        });

        emptyNewConversationBtn?.addEventListener('click', function() {
            modal.style.display = 'flex'; // Use flex to center with justify/align-items
            userSearchInput.focus();
            searchResults.innerHTML = ''; // Clear previous search results
            userSearchInput.value = ''; // Clear search input
        });

        // Close modal
        closeBtn.addEventListener('click', function() {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        // Search users
        let searchTimeout;
        userSearchInput?.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const query = this.value.trim();

            if (query.length < 2) {
                searchResults.innerHTML = '';
                return;
            }

            searchTimeout = setTimeout(() => {
                // Fetch users via front controller
                fetch(`http://localhost/index.php?page=find_users&search=${encodeURIComponent(query)}`) // Corrected URL
                .then(response => response.json())
                .then(data => {
                    searchResults.innerHTML = '';

                    if (data.length === 0) {
                        searchResults.innerHTML = '<div class="p-3 text-center text-muted">Nenhum usuário encontrado.</div>';
                        return;
                    }

                    data.forEach(user => {
                        // Exclude the current user from search results
                        if (user.id == <?php echo $current_user_id; ?>) {
                            return; // Skip current user
                        }

                        const userDiv = document.createElement('div');
                        userDiv.className = 'search-item d-flex align-items-center';

                        const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16);

                        userDiv.innerHTML = `
                            <div class="avatar me-2" style="background-color: ${randomColor}">
                                ${user.profile_photo ? `<img src="${user.profile_photo}" alt="Foto de Perfil">` : `${user.name.substring(0, 2).toUpperCase()}`}
                            </div>
                            <div>${user.name}</div>
                        `;

                        userDiv.addEventListener('click', function() {
                            // Redirect to chat with the selected user via front controller
                            window.location.href = `index.php?page=chat&user=${user.id}`; // Corrected URL
                        });

                        searchResults.appendChild(userDiv);
                    });
                })
                .catch(error => {
                    console.error('Erro na busca:', error);
                    searchResults.innerHTML = '<div class="p-3 text-center text-muted">Erro ao buscar usuários.</div>';
                });
            }, 300);
        });

        // Focus input when chat is loaded
        const messageInput = document.getElementById('message-content');
        if (messageInput) {
            messageInput.focus();
        }
    </script>
</body>
</html>