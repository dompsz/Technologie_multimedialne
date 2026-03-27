<?php
session_start();
if(isset($_SESSION['lab15_user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 15 - System CRM</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav-back"><a href="../index.php">← Menu główne</a></div>
    <div style="text-align: center; margin-top: 100px;">
        <h1>Zadanie 15</h1>
        <p>System zarządzania relacjami z klientami (CRM).</p>
        <div style="margin-top: 30px;">
            <a href="login.php" class="btn">Zaloguj się</a>
            <a href="register.php" class="btn" style="background: #28a745; color: white;">Zarejestruj się</a>
        </div>
    </div>
</body>
</html>
