<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Laboratorium 1 - Strona Główna</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .hero {
            text-align: center;
            padding: 50px 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            margin-top: 20px;
        }
        .hero h1 { color: #333; }
        .hero p { color: #666; font-size: 1.1rem; }
        .actions { margin-top: 30px; display: flex; justify-content: center; gap: 15px; }
        .btn-outline {
            padding: 10px 25px;
            border: 2px solid #007bff;
            border-radius: 4px;
            color: #007bff;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-outline:hover {
            background: #007bff;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="nav-back">
        <a href="../index.php">← Powrót do wyboru Laboratoriów</a>
    </div>
    <div class="hero">
        <h1>Witaj w Laboratorium 1</h1>
        <p>Temat: Podstawy technologii multimedialnych i struktura projektów webowych.</p>
        
        <?php if(isset($_SESSION['lab1_user_id'])): ?>
            <p>Jesteś zalogowany jako: <strong><?php echo htmlspecialchars($_SESSION['lab1_username']); ?></strong></p>
            <div class="actions">
                <a href="dashboard.php" class="btn">Przejdź do zadań</a>
                <a href="logout.php" class="btn-outline">Wyloguj się</a>
            </div>
        <?php else: ?>
            <p>Aby uzyskać dostęp do zadań, musisz się zalogować.</p>
            <div class="actions">
                <a href="login.php" class="btn">Logowanie</a>
                <a href="register.php" class="btn-outline">Rejestracja</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
