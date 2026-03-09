<?php
session_start();
// Przekierowanie bezpośrednio do dashboardu jeśli zalogowany
if(isset($_SESSION['lab12b_user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Laboratorium 12b - Arduino i IoT</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .hero {
            text-align: center;
            padding: 60px 20px;
            border-radius: 12px;
            margin-top: 40px;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        .actions { margin-top: 40px; display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; }
    </style>
</head>
<body>
    <div class="nav-back">
        <a href="../index.php">← Powrót do menu głównego</a>
    </div>
    <div class="hero">
        <h1>Laboratorium 12b</h1>
        <p>Systemy wizualizacji SCADA i bazy danych MySQL.</p>
        
        <p>Zaloguj się, aby uzyskać dostęp do panelu sterowania.</p>
        <div class="actions">
            <a href="login.php" class="btn" style="max-width: 200px;">Logowanie</a>
            <a href="register.php" class="btn" style="background:#28a745; color: white; max-width: 200px;">Rejestracja</a>
        </div>
    </div>
</body>
</html>
