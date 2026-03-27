<?php
session_start();
if(isset($_SESSION['lab13_user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Laboratorium 13 - System Todo</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav-back"><a href="../index.php">← Menu główne</a></div>
    <div style="text-align: center; margin-top: 100px;">
        <h1>Laboratorium 13</h1>
        <p>Dedykowany system zarządzania zadaniami Todo.</p>
        <div style="margin-top: 30px;">
            <a href="login.php" class="btn">Zaloguj się</a>
            <a href="register.php" class="btn" style="background: #28a745; color: white;">Zarejestruj pracownika</a>
        </div>
    </div>
</body>
</html>
