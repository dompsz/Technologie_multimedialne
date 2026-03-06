<?php
session_start();
require_once 'db_config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Wypełnij wszystkie pola.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && !empty($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['lab1_user_id'] = $user['id'];
            $_SESSION['lab1_username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Błędna nazwa użytkownika lub hasło.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Lab 1</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav-back">
        <a href="index.php">← Powrót do Lab 1</a>
    </div>
    <div class="auth-container">
        <h2>Logowanie - Lab 1</h2>
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Nazwa użytkownika</label>
                <input type="text" name="username" id="username" required>
            </div>
            <div class="form-group">
                <label for="password">Hasło</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="btn">Zaloguj się</button>
        </form>
        <div class="auth-link">
            Nie masz konta? <a href="register.php">Zarejestruj się</a>
        </div>
    </div>
</body>
</html>
