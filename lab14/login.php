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
            $_SESSION['lab14_user_id'] = $user['id'];
            $_SESSION['lab14_username'] = $user['username'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Błędne dane logowania.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Lab 14 E-learning</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav-back"><a href="../index.php">← Powrót do Strony Głównej</a></div>
    <div class="auth-container">
        <h2>Logowanie - System E-learningowy</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Nazwa użytkownika</label>
                <input type="text" name="username" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Hasło</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">ZALOGUJ SIĘ</button>
        </form>
        <div class="auth-link">Nie masz konta? <a href="register.php">Zarejestruj się</a></div>
    </div>
</body>
</html>
