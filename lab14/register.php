<?php
session_start();
require_once 'db_config.php';
$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error = "Wypełnij wszystkie pola.";
    } elseif ($password !== $confirm_password) {
        $error = "Hasła nie są identyczne.";
    } else {
        $role = $_POST['role'] ?? 'user';
        if (!in_array($role, ['user', 'coach', 'admin'])) $role = 'user';

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Użytkownik o takiej nazwie już istnieje.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $hashed_password, $role])) {
                $success = "Rejestracja pomyślna. Możesz się <a href='login.php'>zalogować</a>.";
            } else {
                $error = "Błąd rejestracji.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja - Lab 14 E-learning</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background: #1a1a1a;
            color: #fff;
            border: 1px solid #444;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="nav-back"><a href="index.php">← Powrót</a></div>
    <div class="auth-container">
        <h2>Rejestracja - System E-learningowy</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nazwa użytkownika</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Rola</label>
                <select name="role">
                    <option value="user">Uczeń (User)</option>
                    <option value="coach">Szkoleniowiec (Coach)</option>
                    <option value="admin">Administrator (Admin)</option>
                </select>
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
