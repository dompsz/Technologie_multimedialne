<?php
session_start();
require_once 'db_config.php';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($login) || empty($password)) {
        $error = "Wszystkie pola są wymagane.";
    } elseif ($password !== $confirm_password) {
        $error = "Hasła nie są identyczne.";
    } else {
        $stmt = $conn->prepare("SELECT idu FROM uzytkownicy WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = "Ten login jest już zajęty.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO uzytkownicy (login, haslo, rola) VALUES (?, ?, 'user')");
            if ($stmt->execute([$login, $hashed_password])) {
                $success = "Konto utworzone! <a href='login.php'>Zaloguj się</a>";
            } else {
                $error = "Wystąpił błąd przy tworzeniu konta.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja - Galeria Lab 18</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <div class="nav-back"><a href="index.php">← Powrót do Galerii</a></div>
    <div class="auth-container">
        <h2>Rejestracja</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Login</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Hasło</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Powtórz Hasło</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn">ZAREJESTRUJ SIĘ</button>
        </form>
        <div class="auth-link">Masz już konto? <a href="login.php">Zaloguj się</a></div>
    </div>
</body>
</html>
