<?php
session_start();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Laboratorium 12a - SCADA i MySQL</title>
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
        .actions { margin-top: 30px; display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; }
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
        <h1>Laboratorium 12a</h1>
        <p>Temat: Systemy wizualizacji SCADA i bazy danych MySQL.</p>
        
        <?php if(isset($_SESSION['lab12a_user_id'])): ?>
            <p>Zalogowano jako: <strong><?php echo htmlspecialchars($_SESSION['lab12a_username']); ?></strong></p>
            <div class="actions">
                <a href="formularz.php" class="btn">Formularz Pomiarów</a>
                <a href="tabela.php" class="btn-outline">Tabela Wyników</a>
                <a href="wykres.php" class="btn-outline">Wykresy Chart.js</a>
                <a href="scada.php" class="btn">Wizualizacja SCADA</a>
                <a href="logout.php" class="btn" style="background:#6c757d">Wyloguj się</a>
            </div>
        <?php else: ?>
            <p>Zaloguj się, aby uzyskać dostęp do zadań.</p>
            <div class="actions">
                <a href="login.php" class="btn">Logowanie</a>
                <a href="register.php" class="btn" style="background:#28a745">Rejestracja</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
