<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab2_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab2_user_id'];
$success = '';
$error = '';

// Pobieranie danych
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        if ($file['size'] < 5 * 1024 * 1024) { // 5MB
            $new_name = "avatar_" . $user_id . "." . $ext;
            $destination = "uploads/" . $new_name;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $update = $conn->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
                $update->execute([$destination, $user_id]);
                $success = "Avatar został zaktualizowany.";
                // Odśwież dane
                $user['avatar_path'] = $destination;
            } else {
                $error = "Błąd podczas zapisywania pliku.";
            }
        } else {
            $error = "Plik jest za duży (max 5MB).";
        }
    } else {
        $error = "Niedozwolony format pliku.";
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Multimedia - Lab 2</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .avatar-preview { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #007bff; margin: 20px 0; }
        .multimedia-card { max-width: 600px; margin: 30px auto; background: white; padding: 30px; border-radius: 12px; text-align: center; }
    </style>
</head>
<body>
    <div class="nav-back"><a href="index.php">← Powrót do Lab 2</a></div>
    
    <div class="multimedia-card">
        <h2>Twój profil multimedialny</h2>
        
        <?php 
        $avatar = (!empty($user['avatar_path']) && file_exists($user['avatar_path'])) ? $user['avatar_path'] : '../assets/default-avatar.svg';
        ?>
        <img src="<?php echo $avatar; ?>" alt="Avatar" class="avatar-preview">
        
        <p>Witaj, <strong><?php echo htmlspecialchars($user['username']); ?></strong>!</p>
        
        <?php if($success) echo "<div class='success'>$success</div>"; ?>
        <?php if($error) echo "<div class='error'>$error</div>"; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Zmień zdjęcie profilowe (JPG, PNG, GIF)</label>
                <input type="file" name="avatar" required>
            </div>
            <button type="submit" class="btn">Wgraj zdjęcie</button>
        </form>
    </div>
</body>
</html>
