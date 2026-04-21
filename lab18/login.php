<?php
session_start();
require_once 'db_config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $error = "Wypełnij wszystkie pola.";
    } else {
        $stmt = $conn->prepare("SELECT idu, login, haslo, rola FROM uzytkownicy WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['haslo'])) {
            $_SESSION['lab18_user_id'] = $user['idu'];
            $_SESSION['lab18_login'] = $user['login'];
            $_SESSION['lab18_role'] = $user['rola'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Błędny login lub hasło.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Galeria Lab 18</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <div class="nav-back"><a href="index.php">← Powrót do Galerii</a></div>
    <div class="auth-container">
        <h2>Logowanie</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Login</label>
                <input type="text" name="login" required>
            </div>
            <div class="form-group">
                <label>Hasło</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">ZALOGUJ SIĘ</button>
        </form>
        <div class="auth-link">Nie masz konta? <a href="register.php">Zarejestruj się</a></div>
    </div>
</body>
</html>
