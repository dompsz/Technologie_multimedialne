<?php
session_start();
require_once 'db_config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($login) || empty($password)) {
        $error = "Wszystkie pola są wymagane.";
    } elseif ($password !== $confirm_password) {
        $error = "Hasła nie są identyczne.";
    } elseif (strlen($password) < 4) {
        $error = "Hasło musi mieć co najmniej 4 znaki.";
    } else {
        // Sprawdzenie czy login zajęty
        $stmt = $conn->prepare("SELECT idp FROM pracownik WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = "Ten login jest już zajęty.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO pracownik (login, password) VALUES (?, ?)");
            if ($stmt->execute([$login, $hashed_password])) {
                $success = "Konto zostało utworzone! Możesz się teraz zalogować.";
            } else {
                $error = "Wystąpił błąd podczas rejestracji.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja - Lab 13 Todo</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .auth-container { max-width: 400px; margin: 80px auto; padding: 20px; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--border-color); }
        .error { color: #ff4444; background: rgba(255, 68, 68, 0.1); padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .success { color: #00ff00; background: rgba(0, 255, 0, 0.1); padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; background: #222; border: 1px solid var(--border-color); color: white; border-radius: 4px; box-sizing: border-box; }
        .btn { width: 100%; padding: 10px; background: var(--accent-color); color: black; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn:hover { background: var(--accent-hover); }
        .auth-link { margin-top: 15px; text-align: center; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="nav-back"><a href="index.php">← Powrót</a></div>
    <div class="auth-container">
        <h2 style="text-align: center; margin-bottom: 20px;">Rejestracja Pracownika</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Login (identyfikator)</label>
                <input type="text" name="login" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Hasło</label>
                <input type="password" name="password" required autocomplete="new-password">
            </div>
            <div class="form-group">
                <label>Potwierdź hasło</label>
                <input type="password" name="confirm_password" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn">ZAREJESTRUJ SIĘ</button>
        </form>
        <div class="auth-link">Masz już konto? <a href="login.php">Zaloguj się</a></div>
    </div>
</body>
</html>
