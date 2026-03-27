<?php
session_start();
require_once 'db_config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type = $_POST['login_type'] ?? 'client'; // 'client' lub 'employee'
    $nazwisko = trim($_POST['nazwisko']);
    $password = $_POST['password'];

    if (empty($nazwisko) || empty($password)) {
        $error = "Wypełnij wszystkie pola.";
    } else {
        if ($login_type === 'client') {
            // Logowanie Klienta
            $stmt = $conn->prepare("SELECT idk, nazwisko, haslo FROM klienci WHERE nazwisko = ?");
            $stmt->execute([$nazwisko]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['haslo'])) {
                $_SESSION['lab15_user_id'] = $user['idk'];
                $_SESSION['lab15_username'] = $user['nazwisko'];
                $_SESSION['lab15_role'] = 'client';

                // Logowanie szczegółowe dla klienta
                $ip = $_SERVER['REMOTE_ADDR'];
                $ua = $_SERVER['HTTP_USER_AGENT'];
                
                // Prosta ekstrakcja systemu/przeglądarki (można rozbudować)
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

                $stmt_log = $conn->prepare("INSERT INTO logi_klientow (idk, ip_address, przegladarka, system) VALUES (?, ?, ?, ?)");
                $stmt_log->execute([$user['idk'], $ip, $browser, $os]);

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Błędne nazwisko lub hasło klienta.";
            }
        } else {
            // Logowanie Pracownika / Admina
            $stmt = $conn->prepare("SELECT idp, nazwisko, haslo, role FROM pracownicy WHERE nazwisko = ?");
            $stmt->execute([$nazwisko]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['haslo'])) {
                $_SESSION['lab15_user_id'] = $user['idp'];
                $_SESSION['lab15_username'] = $user['nazwisko'];
                $_SESSION['lab15_role'] = $user['role'];

                // Logowanie dla pracownika
                $stmt_log = $conn->prepare("INSERT INTO logi_pracownikow (idp) VALUES (?)");
                $stmt_log->execute([$user['idp']]);

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Błędne nazwisko lub hasło pracownika.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie CRM - Lab 15</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .auth-container { max-width: 450px; margin: 80px auto; padding: 30px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .nav-tabs .nav-link { color: #aaa; border: none; font-weight: bold; padding: 12px 20px; }
        .nav-tabs .nav-link.active { background: transparent; color: var(--accent-color); border-bottom: 3px solid var(--accent-color); }
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
            <a href="index.php" class="text-secondary text-decoration-none small">← Powrót do strony głównej</a>
        </div>
        
        <div class="auth-container">
            <h2 class="text-center mb-4">System CRM 🏢</h2>
            
            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>

            <ul class="nav nav-tabs justify-content-center mb-4" id="loginTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" onclick="setLoginType('client', this)">Klient</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" onclick="setLoginType('employee', this)">Pracownik</button>
                </li>
            </ul>

            <form method="POST">
                <input type="hidden" name="login_type" id="login_type" value="client">
                
                <div class="mb-3">
                    <label class="form-label" id="label_nazwisko">Nazwisko Klienta</label>
                    <input type="text" name="nazwisko" class="form-control" placeholder="Wpisz nazwisko" required>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Hasło</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">ZALOGUJ SIĘ</button>
                
                <div class="text-center small text-secondary mt-3" id="register_link">
                    Nie masz konta? <a href="register.php" class="text-info">Zarejestruj się jako klient</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function setLoginType(type, btn) {
            document.getElementById('login_type').value = type;
            
            // UI updates
            const label = document.getElementById('label_nazwisko');
            const regLink = document.getElementById('register_link');
            
            // Reset active state
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            btn.classList.add('active');

            if (type === 'client') {
                label.innerText = 'Nazwisko Klienta';
                regLink.style.display = 'block';
            } else {
                label.innerText = 'Nazwisko Pracownika / Admina';
                regLink.style.display = 'none';
            }
        }
    </script>
</body>
</html>
