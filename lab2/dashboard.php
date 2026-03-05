<?php
session_start();
if (!isset($_SESSION['lab2_user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lab 2</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container { max-width: 800px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 8px; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="nav-back"><a href="index.php">← Powrót do Lab 2</a></div>
    <div class="dashboard-container">
        <h2>Panel Laboratorium 2 (Multimedia)</h2>
        <p>Witaj, <strong><?php echo htmlspecialchars($_SESSION['lab2_username']); ?></strong>!</p>
        <h3>Zadania Lab 2:</h3>
        <ul>
            <li>Ładowanie i wyświetlanie obrazów.</li>
            <li>Obsługa plików wideo.</li>
            <li>Przetwarzanie multimediów po stronie serwera.</li>
        </ul>
        <h3>SQL dla bazy Lab 2:</h3>
        <pre>
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
        </pre>
        <a href="logout.php" class="btn" style="background:#dc3545; width: auto; padding: 10px 20px;">Wyloguj</a>
    </div>
</body>
</html>
