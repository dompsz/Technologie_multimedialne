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
        $stmt = $conn->prepare("SELECT idu, login, haslo, poziom_uprawnien FROM uzytkownicy WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['haslo'])) {
            $_SESSION['lab17_user_id'] = $user['idu'];
            $_SESSION['lab17_login'] = $user['login'];
            $_SESSION['lab17_role'] = $user['poziom_uprawnien'];
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
    <title>Logowanie - Forum Lab 17</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="nav-back"><a href="index.php">← Powrót do Forum</a></div>
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
