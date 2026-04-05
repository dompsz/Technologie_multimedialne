<?php
session_start();
require_once 'db_config.php';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password)) {
        $error = "Wszystkie pola są wymagane.";
    } elseif ($password !== $confirm_password) {
        $error = "Hasła nie są identyczne.";
    } else {
        $stmt = $conn->prepare("SELECT idu FROM uzytkownicy WHERE nazwa_uzytkownika = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = "Nazwa użytkownika jest już zajęta.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO uzytkownicy (nazwa_uzytkownika, haslo, rola) VALUES (?, ?, 'redaktor')");
            if ($stmt->execute([$username, $hashed_password])) {
                $success = "Konto zostało utworzone. Możesz się teraz zalogować.";
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
    <title>Rejestracja - System CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .auth-container { max-width: 450px; margin: 80px auto; padding: 30px; background: var(--card-bg); border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .form-control { background: #222; border: 1px solid #444; color: #fff; padding: 12px; }
        .btn-primary { background: var(--accent-color); border: none; color: #000; font-weight: bold; padding: 12px; }
        .error-msg { background: rgba(220, 53, 69, 0.1); color: #ff6b6b; border: 1px solid #dc3545; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
        .success-msg { background: rgba(40, 167, 69, 0.1); color: #75b798; border: 1px solid #28a745; padding: 10px; border-radius: 6px; margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="text-center mt-4">
            <a href="index.php" class="text-secondary text-decoration-none small">← Powrót</a>
        </div>
        
        <div class="auth-container">
            <h2 class="text-center mb-4">Rejestracja Redaktora</h2>
            
            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="success-msg"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nazwa użytkownika</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Hasło</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Powtórz hasło</label>
                    <input type="password" name="confirm_password" class="form-control" required>
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
