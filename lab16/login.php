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
        $ip = $_SERVER['REMOTE_ADDR'];
        $ua = $_SERVER['HTTP_USER_AGENT'];
        
        $os = "Nieznany";
        if (preg_match('/Windows/i', $ua)) $os = "Windows";
        elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) $os = "macOS";
        elseif (preg_match('/Linux/i', $ua)) $os = "Linux";
        elseif (preg_match('/Android/i', $ua)) $os = "Android";
        elseif (preg_match('/iPhone|iPad/i', $ua)) $os = "iOS";

        $browser = "Nieznana";
        if (preg_match('/Chrome/i', $ua)) $browser = "Chrome";
        elseif (preg_match('/Firefox/i', $ua)) $browser = "Firefox";
        elseif (preg_match('/Safari/i', $ua)) $browser = "Safari";
        elseif (preg_match('/Edge/i', $ua)) $browser = "Edge";

        // --- OCHRONA BRUTE-FORCE ---
        $stmt_bf = $conn->prepare("SELECT stan FROM logi_logowania WHERE login_attempted = ? ORDER BY datagodzina DESC LIMIT 3");
        $stmt_bf->execute([$username]);
        $attempts = $stmt_bf->fetchAll(PDO::FETCH_COLUMN);
        
        if (count($attempts) === 3 && array_sum($attempts) === 0) {
            $error = "Konto zablokowane (3 nieudane próby). Skontaktuj się z administratorem.";
        } else {
            $stmt = $conn->prepare("SELECT idu, nazwa_uzytkownika, haslo, rola FROM uzytkownicy WHERE nazwa_uzytkownika = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['haslo'])) {
                $_SESSION['lab16_user_id'] = $user['idu'];
                $_SESSION['lab16_username'] = $user['nazwa_uzytkownika'];
                $_SESSION['lab16_role'] = $user['rola'];

                $stmt_log = $conn->prepare("INSERT INTO logi_logowania (idu, login_attempted, ip_address, przegladarka, system, stan) VALUES (?, ?, ?, ?, ?, 1)");
                $stmt_log->execute([$user['idu'], $username, $ip, $browser, $os]);

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Błędna nazwa użytkownika lub hasło.";
                $idu = $user ? $user['idu'] : null;
                $stmt_log = $conn->prepare("INSERT INTO logi_logowania (idu, login_attempted, ip_address, przegladarka, system, stan) VALUES (?, ?, ?, ?, ?, 0)");
                $stmt_log->execute([$idu, $username, $ip, $browser, $os]);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie - System CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .auth-container { max-width: 450px; margin: 80px auto; padding: 30px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-control { background: #222; border: 1px solid #444; color: #fff; padding: 12px; }
        .form-control:focus { background: #2a2a2a; border-color: var(--accent-color); color: #fff; box-shadow: none; }
        .btn-primary { background: var(--accent-color); border: none; color: #000; font-weight: bold; padding: 12px; transition: 0.3s; }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-2px); }
        .error-msg { background: rgba(220, 53, 69, 0.1); color: #ff6b6b; border: 1px solid #dc3545; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mt-4">
            <a href="index.php" class="text-secondary text-decoration-none small">← Powrót</a>
        </div>
        
        <div class="auth-container">
            <h2 class="text-center mb-4">Logowanie CMS</h2>
            
            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nazwa użytkownika</label>
                    <input type="text" name="username" class="form-control" placeholder="Wpisz nazwę" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Hasło</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">ZALOGUJ SIĘ</button>
                
                <div class="text-center small text-secondary mt-3">
                    Nie masz konta? <a href="register.php" class="text-info">Zarejestruj się</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
