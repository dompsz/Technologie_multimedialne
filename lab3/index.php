<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Laboratorium 3 - Efekty Wizualne</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .hero { text-align: center; padding: 50px 20px; border-radius: 8px; margin-top: 20px; }
        .actions { margin-top: 30px; display: flex; justify-content: center; gap: 15px; }
    </style>
</head>
<body>
    <div class="nav-back"><a href="../index.php">← Powrót do wyboru Laboratoriów</a></div>
    <div class="hero">
        <h1>Witaj w Laboratorium 3</h1>
        <p>Temat: Zaawansowane efekty wizualne i animacje w CSS/JS.</p>
        <?php if(isset($_SESSION['lab3_user_id'])): ?>
            <p>Zalogowano jako: <strong><?php echo htmlspecialchars($_SESSION['lab3_username']); ?></strong></p>
            <div class="actions">
                <a href="dashboard.php" class="btn">Zadania Lab 3</a>
                <a href="logout.php" class="btn" style="background:#6c757d">Wyloguj</a>
            </div>
        <?php else: ?>
            <p>Zaloguj się aby przejść do zadań.</p>
            <div class="actions">
                <a href="login.php" class="btn">Logowanie</a>
                <a href="register.php" class="btn" style="background:#28a745">Rejestracja</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
