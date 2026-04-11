<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab16_user_id'];
$username = $_SESSION['lab16_username'];
$role = $_SESSION['lab16_role'];

// Pobranie podstron
$stmt = $conn->prepare("SELECT p.*, k.nazwa as kategoria, u.nazwa_uzytkownika as autor FROM podstrony p LEFT JOIN kategorie k ON p.idk = k.idk JOIN uzytkownicy u ON p.idu = u.idu ORDER BY p.data_aktualizacji DESC");
$stmt->execute();
$pages = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel CMS - Zadanie 16</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .dashboard-container { padding: 30px; }
        .table { color: #eee; background: var(--card-bg); border-radius: 12px; overflow: hidden; }
        .table thead { background: rgba(255,255,255,0.05); }
        .badge-status { font-size: 0.8rem; }
        .btn-action { padding: 5px 10px; font-size: 0.9rem; }
        
        /* Chatbot Widget Styles */
        .chat-widget { position: fixed; bottom: 0; right: 30px; width: 350px; background: #1e1e1e; border: 1px solid #444; border-top-left-radius: 12px; border-top-right-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.5); z-index: 1000; transition: transform 0.3s ease; }
        .chat-header { background: #007bff; color: white; padding: 12px 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; user-select: none; }
        .chat-body { height: 350px; padding: 15px; overflow-y: auto; background: #222; display: none; border-left: 1px solid #444; border-right: 1px solid #444; flex-direction: column; }
        .chat-footer { padding: 10px; border-top: 1px solid #333; display: none; background: #1e1e1e; border-left: 1px solid #444; border-right: 1px solid #444; }
        .msg { margin-bottom: 10px; padding: 8px 12px; border-radius: 15px; max-width: 85%; font-size: 0.85rem; }
        .msg-bot { background: #333; color: white; align-self: flex-start; }
        .msg-user { background: #007bff; color: white; align-self: flex-end; margin-left: auto; }
        .avatar { width: 35px; height: 35px; margin-right: 10px; transition: transform 0.3s; }
        .typing .avatar { animation: bounce 0.5s infinite alternate; }
        @keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-5px); } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">CMS Lab 16</a>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3">Użytkownik: <strong><?php echo htmlspecialchars($username); ?></strong> | Uprawnienia: <span class="badge bg-info text-dark"><?php echo htmlspecialchars($role); ?></span></span>
                <?php if ($role === 'admin' || $username === 'admin'): ?>
                    <a href="admin.php" class="btn btn-warning btn-sm me-2 fw-bold text-dark">🛡️ PANEL ADMINA</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid dashboard-container">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2>Zarządzanie treścią</h2>
            </div>
            <div class="col text-end">
                <a href="add_page.php" class="btn btn-success">+ Dodaj nową stronę</a>
                <?php if($role === 'admin'): ?>
                    <a href="admin.php" class="btn btn-primary">Panel Admina</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tytuł</th>
                        <th>Kategoria</th>
                        <th>Autor</th>
                        <th>Status</th>
                        <th>Ostatnia zmiana</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pages as $page): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($page['tytul']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($page['kategoria'] ?? 'Brak'); ?></span></td>
                        <td><?php echo htmlspecialchars($page['autor']); ?></td>
                        <td>
                            <?php if($page['status'] === 'opublikowany'): ?>
                                <span class="badge bg-success badge-status">Opublikowany</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark badge-status">Szkic</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $page['data_aktualizacji']; ?></td>
                        <td>
                            <a href="edit_page.php?id=<?php echo $page['idp']; ?>" class="btn btn-sm btn-outline-info btn-action">Edytuj</a>
                            <?php if($role === 'admin' || $user_id == $page['idu']): ?>
                                <a href="delete_page.php?id=<?php echo $page['idp']; ?>" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('Czy na pewno chcesz usunąć tę stronę?')">Usuń</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($pages)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary">Brak stron w systemie.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Chatbot Widget -->
    <div class="chat-widget" id="chatWidget">
        <div class="chat-header" onclick="toggleChat()">
            <div class="d-flex align-items-center">
                <img src="../assets/default-avatar.svg" class="avatar" id="botAvatar">
                <span>Asystent CMS</span>
            </div>
            <span id="chatToggleIcon">+</span>
        </div>
        <div class="chat-body" id="chatBody">
            <div class="msg msg-bot">Cześć <?php echo htmlspecialchars($username); ?>! W czym mogę Ci dzisiaj pomóc?</div>
        </div>
        <div class="chat-footer" id="chatFooter">
            <div class="input-group">
                <input type="text" id="chatInput" class="form-control bg-dark text-white border-secondary" placeholder="Zadaj pytanie...">
                <button class="btn btn-primary" onclick="sendMessage()">Wyślij</button>
            </div>
        </div>
    </div>

    <script>
        function toggleChat() {
            const body = document.getElementById('chatBody');
            const footer = document.getElementById('chatFooter');
            const icon = document.getElementById('chatToggleIcon');
            
            if (body.style.display === 'none' || body.style.display === '') {
                body.style.display = 'flex';
                footer.style.display = 'block';
                icon.innerText = '−';
            } else {
                body.style.display = 'none';
                footer.style.display = 'none';
                icon.innerText = '+';
            }
        }

        function sendMessage() {
            const input = document.getElementById('chatInput');
            const body = document.getElementById('chatBody');
            const avatar = document.getElementById('botAvatar');
            const query = input.value.trim();

            if (query === '') return;

            appendMessage(query, 'user');
            input.value = '';

            avatar.parentElement.parentElement.parentElement.classList.add('typing');

            fetch('chat_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ query: query })
            })
            .then(res => res.json())
            .then(data => {
                avatar.parentElement.parentElement.parentElement.classList.remove('typing');
                appendMessage(data.reply, 'bot');
            })
            .catch(err => {
                avatar.parentElement.parentElement.parentElement.classList.remove('typing');
                appendMessage('Błąd połączenia z botem.', 'bot');
            });
        }

        function appendMessage(text, side) {
            const body = document.getElementById('chatBody');
            const div = document.createElement('div');
            div.className = `msg msg-${side}`;
            div.innerHTML = text;
            body.appendChild(div);
            body.scrollTop = body.scrollHeight;
        }

        document.getElementById('chatInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>
