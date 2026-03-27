<?php
session_start();
require_once 'db_config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwisko = trim($_POST['nazwisko']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($nazwisko) || empty($password)) {
        $error = "Wszystkie pola są wymagane.";
    } elseif ($password !== $confirm_password) {
        $error = "Hasła nie są identyczne.";
    } else {
        // Sprawdzenie czy nazwisko zajęte
        $stmt = $conn->prepare("SELECT idk FROM klienci WHERE nazwisko = ?");
        $stmt->execute([$nazwisko]);
        if ($stmt->fetch()) {
            $error = "To nazwisko jest już zarejestrowane.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $conn->beginTransaction();
            try {
                $stmt = $conn->prepare("INSERT INTO klienci (nazwisko, haslo) VALUES (?, ?)");
                $stmt->execute([$nazwisko, $hashed_password]);
                $idk = $conn->lastInsertId();

                // Logowanie szczegółowe dla klienta przy rejestracji
                $ip = $_SERVER['REMOTE_ADDR'];
                $ua = $_SERVER['HTTP_USER_AGENT'];
                
                $os = "Nieznany";
                if (preg_match('/Windows/i', $ua)) $os = "Windows";
                elseif (preg_match('/Macintosh|Mac OS X/i', $ua)) $os = "macOS";
                elseif (preg_match('/Linux/i', $ua)) $os = "Linux";

                $browser = "Nieznana";
                if (preg_match('/Chrome/i', $ua)) $browser = "Chrome";
                elseif (preg_match('/Firefox/i', $ua)) $browser = "Firefox";

                $stmt_log = $conn->prepare("INSERT INTO logi_klientow (idk, ip_address, przegladarka, system) VALUES (?, ?, ?, ?)");
                $stmt_log->execute([$idk, $ip, $browser, $os]);

                $conn->commit();
                $success = "Konto klienta utworzone! Możesz się teraz zalogować.";
            } catch (Exception $e) {
                $conn->rollBack();
                $error = "Błąd rejestracji: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja Klienta - System CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .auth-container { max-width: 450px; margin: 80px auto; padding: 30px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); }
        .form-control { background: #222; border: 1px solid #444; color: #fff; padding: 12px; }
        .form-control:focus { background: #2a2a2a; border-color: var(--accent-color); color: #fff; box-shadow: none; }
        .btn-primary { background: var(--accent-color); border: none; color: #000; font-weight: bold; padding: 12px; }
        .btn-primary:hover { background: var(--accent-hover); transform: translateY(-2px); }
        .error-msg { background: rgba(220, 53, 69, 0.1); color: #ff6b6b; border: 1px solid #dc3545; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .success-msg { background: rgba(25, 135, 84, 0.1); color: #75b798; border: 1px solid #198754; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mt-4">
            <a href="login.php" class="text-secondary text-decoration-none small">← Powrót do logowania</a>
        </div>
        
        <div class="auth-container shadow-lg">
            <h2 class="text-center mb-4">Rejestracja Klienta</h2>
            
            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nazwisko (Login)</label>
                    <input type="text" name="nazwisko" class="form-control" placeholder="Wpisz swoje nazwisko" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Hasło</label>
                    <input type="password" name="password" class="form-control" placeholder="Wpisz hasło" required>
                </div>

                <div class="mb-4">
                    <label class="form-label">Potwierdź Hasło</label>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Powtórz hasło" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">ZAREJESTRUJ SIĘ</button>
                
                <div class="text-center small text-secondary mt-3">
                    Masz już konto? <a href="login.php" class="text-info">Zaloguj się</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
