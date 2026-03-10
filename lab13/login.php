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
        // --- OCHRONA BRUTE-FORCE ---
        // Sprawdzenie 3 ostatnich prób logowania dla tego loginu
        $stmt_check = $conn->prepare("SELECT state FROM logowanie WHERE login_attempted = ? ORDER BY datetime DESC LIMIT 3");
        $stmt_check->execute([$login]);
        $attempts = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
        
        // Blokada, jeśli ostatnie 3 próby (jeśli są 3) były nieudane (state = 0)
        if (count($attempts) === 3 && array_sum($attempts) === 0) {
            $error = "Konto tymczasowo zablokowane (3 nieudane próby). Skontaktuj się z administratorem.";
        } else {
            // Próba pobrania pracownika
            $stmt = $conn->prepare("SELECT idp, login, password FROM pracownik WHERE login = ?");
            $stmt->execute([$login]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // SUKCES LOGOWANIA
                $_SESSION['lab13_user_id'] = $user['idp'];
                $_SESSION['lab13_login'] = $user['login'];
                
                // Rejestracja sukcesu w tabeli logowanie
                $stmt_log = $conn->prepare("INSERT INTO logowanie (idp, login_attempted, state) VALUES (?, ?, 1)");
                $stmt_log->execute([$user['idp'], $login]);
                
                header("Location: dashboard.php");
                exit();
            } else {
                // PORAŻKA LOGOWANIA
                $error = "Błędny login lub hasło.";
                $idp_log = $user ? $user['idp'] : null;
                
                // Rejestracja porażki w tabeli logowanie
                $stmt_log = $conn->prepare("INSERT INTO logowanie (idp, login_attempted, state) VALUES (?, ?, 0)");
                $stmt_log->execute([$idp_log, $login]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - Lab 13 Todo</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .auth-container { max-width: 400px; margin: 100px auto; padding: 20px; background: var(--card-bg); border-radius: 8px; border: 1px solid var(--border-color); }
        .error { color: #ff4444; background: rgba(255, 68, 68, 0.1); padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; }
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
        <h2 style="text-align: center; margin-bottom: 20px;">System Todo - Logowanie</h2>
        <?php if($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label>Login pracownika</label>
                <input type="text" name="login" required autocomplete="username">
            </div>
            <div class="form-group">
                <label>Hasło</label>
                <input type="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn">ZALOGUJ</button>
        </form>
        <div class="auth-link">Nowy pracownik? <a href="register.php">Zarejestruj się</a></div>
    </div>
</body>
</html>
