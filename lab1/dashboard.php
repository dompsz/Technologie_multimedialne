<?php
session_start();
if (!isset($_SESSION['lab1_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lab 1</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container {
            max-width: 800px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        pre {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="nav-back">
        <a href="index.php">← Powrót do Strony Głównej Lab 1</a>
    </div>
    <div class="dashboard-container">
        <h2>Panel Laboratorium 1</h2>
        <p>Witaj, <strong><?php echo htmlspecialchars($_SESSION['lab1_username']); ?></strong>! Uzyskałeś dostęp do zadań laboratoryjnych.</p>
        
        <div class="tasks">
            <h3>Zadania do wykonania:</h3>
            <ul>
                <li>Przygotowanie struktury bazy danych.</li>
                <li>Implementacja logowania i rejestracji.</li>
                <li>Utworzenie panelu użytkownika.</li>
            </ul>
        </div>

        <div class="sql-info">
            <h3>Konfiguracja Bazy Danych</h3>
            <p>Uruchom poniższy skrypt SQL w swojej bazie danych (Lab 1):</p>
            <pre>
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
            </pre>
        </div>
        
        <a href="logout.php" class="btn" style="background-color: #dc3545; display: inline-block; width: auto; padding: 10px 20px;">Wyloguj się</a>
    </div>
</body>
</html>
