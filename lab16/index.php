<?php
session_start();
require_once 'db_config.php';

// Pobranie opublikowanych podstron (menu boczne)
$stmt = $conn->query("SELECT tytul, slug FROM podstrony WHERE status = 'opublikowany' ORDER BY data_aktualizacji DESC");
$pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 16 - System CMS i Chatbot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .hero { padding: 80px 0; text-align: center; background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('https://source.unsplash.com/1600x900/?technology'); background-size: cover; }
        .sidebar { background: #1e1e1e; border-radius: 12px; padding: 20px; border: 1px solid #333; }
        .chat-widget { position: fixed; bottom: 30px; right: 30px; width: 350px; background: #1e1e1e; border: 1px solid #444; border-radius: 12px; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.5); z-index: 1000; }
        .chat-header { background: #007bff; color: white; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; }
        .chat-body { height: 300px; padding: 15px; overflow-y: auto; background: #222; display: none; }
        .chat-footer { padding: 10px; border-top: 1px solid #333; display: none; }
        .msg { margin-bottom: 10px; padding: 8px 12px; border-radius: 15px; max-width: 85%; font-size: 0.9rem; }
        .msg-bot { background: #333; color: white; align-self: flex-start; }
        .msg-user { background: #007bff; color: white; align-self: flex-end; margin-left: auto; }
        .avatar { width: 40px; height: 40px; margin-right: 10px; transition: transform 0.3s; }
        .typing .avatar { animation: bounce 0.5s infinite alternate; }
        @keyframes bounce { from { transform: translateY(0); } to { transform: translateY(-5px); } }
    </style>
</head>
<body>
    <div class="nav-back"><a href="../index.php">← Menu główne</a></div>

    <div class="hero mb-5">
        <div class="container">
            <h1 class="display-4">System CMS & Chatbot</h1>
            <p class="lead">Nowoczesne zarządzanie treścią z inteligentnym asystentem.</p>
            <div class="mt-4">
                <?php if(isset($_SESSION['lab16_user_id'])): ?>
                    <a href="dashboard.php" class="btn btn-primary btn-lg">Przejdź do Panelu</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-lg me-2">Zaloguj się</a>
                    <a href="register.php" class="btn btn-success btn-lg">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="sidebar">
                    <h4>Nasze sekcje</h4>
                    <hr class="border-secondary">
                    <ul class="nav flex-column">
                        <?php foreach($pages as $p): ?>
                            <li class="nav-item">
                                <a class="nav-link text-info" href="view_page.php?slug=<?php echo $p['slug']; ?>">
                                    → <?php echo htmlspecialchars($p['tytul']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <?php if(empty($pages)): ?>
                            <li class="text-secondary">Brak opublikowanych stron.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-8">
                <h3>O projekcie Lab 16</h3>
                <p>Ten system łączy w sobie prostotę panelu CMS z nowoczesnym chatbotem. Dzięki integracji bazy danych MySQL, chatbot potrafi przeszukiwać treści artykułów oraz sformułowane w słowniku odpowiedzi, aby pomagać użytkownikom w czasie rzeczywistym.</p>
                <img src="https://images.unsplash.com/photo-1531482615713-2afd69097998?auto=format&fit=crop&w=800&q=80" class="img-fluid rounded border border-secondary shadow mt-3" alt="Tech">
            </div>
        </div>
    </div>

    <!-- Chatbot Widget -->
    <div class="chat-widget" id="chatWidget">
        <div class="chat-header" onclick="toggleChat()">
            <div class="d-flex align-items-center">
                <img src="../assets/default-avatar.svg" class="avatar" id="botAvatar">
                <span>Chatbot Pomocnik</span>
            </div>
            <span id="chatToggleIcon">+</span>
        </div>
        <div class="chat-body d-flex flex-column" id="chatBody">
            <div class="msg msg-bot">Witaj! Jestem botem pomocnikiem. Zapytaj mnie o coś, np. o "kontakt" lub "oferta".</div>
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

            // Dodaj wiadomość użytkownika
            appendMessage(query, 'user');
            input.value = '';

            // Animacja bota (typing)
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
                appendMessage('Wystąpił błąd połączenia.', 'bot');
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

        // Obsługa klawisza Enter
        document.getElementById('chatInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>
