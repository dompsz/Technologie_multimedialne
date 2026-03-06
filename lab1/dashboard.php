<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab1_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab1_user_id'];
$success = '';
$error = '';

// Pobieranie aktualnych danych użytkownika
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['change_password'])) {
        $old_pass = $_POST['old_password'];
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if (password_verify($old_pass, $user['password'])) {
            if ($new_pass === $confirm_pass) {
                $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update->execute([$hashed, $user_id]);
                $success = "Hasło zostało zmienione.";
            } else {
                $error = "Nowe hasła nie są identyczne.";
            }
        } else {
            $error = "Błędne aktualne hasło.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Użytkownika - Lab 1</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; max-width: 1000px; margin: 20px auto; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .welcome-card { grid-column: 1 / -1; }
    </style>
</head>
<body>
    <div class="nav-back">
        <a href="index.php">← Powrót do Strony Głównej</a>
    </div>

    <div class="dashboard-grid">
        <div class="card welcome-card">
            <h2>Witaj, <?php echo htmlspecialchars($user['username']); ?>!</h2>
            <p>To jest Twój panel w Laboratorium 1.</p>
        </div>

        <div class="card">
            <h3>Informacje o koncie</h3>
            <p><strong>ID:</strong> <?php echo $user['id']; ?></p>
            <p><strong>Login:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Data rejestracji:</strong> <?php echo $user['created_at']; ?></p>
        </div>

        <div class="card">
            <h3>Zmień hasło</h3>
            <?php if($success) echo "<div class='success'>$success</div>"; ?>
            <?php if($error) echo "<div class='error'>$error</div>"; ?>
            <form method="POST">
                <div class="form-group">
                    <label>Stare hasło</label>
                    <input type="password" name="old_password" required>
                </div>
                <div class="form-group">
                    <label>Nowe hasło</label>
                    <input type="password" name="new_password" required>
                </div>
                <div class="form-group">
                    <label>Potwierdź nowe hasło</label>
                    <input type="password" name="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="btn">Zaktualizuj hasło</button>
            </form>
        </div>
    </div>
</body>
</html>
