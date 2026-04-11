<?php
session_start();
require_once 'db_config.php';

// Pobranie opublikowanych podstron
$stmt = $conn->query("SELECT tytul, slug FROM podstrony WHERE status = 'opublikowany' ORDER BY data_aktualizacji DESC");
$pages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zadanie 16 - CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .container { max-width: 800px; margin-top: 50px; }
        .page-list { background: #1e1e1e; border-radius: 12px; padding: 20px; border: 1px solid #333; }
    </style>
</head>
<body>
    <div class="nav-back"><a href="../index.php">← Menu główne</a></div>

    <div class="container text-center">
        <h1 class="mb-4">System CMS - Lab 16</h1>
        
        <div class="mb-5">
            <?php if(isset($_SESSION['lab16_user_id'])): ?>
                <a href="dashboard.php" class="btn btn-primary">Przejdź do Panelu</a>
                <a href="logout.php" class="btn btn-outline-danger">Wyloguj</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-success me-2">Zaloguj się</a>
                <a href="register.php" class="btn btn-outline-light">Rejestracja</a>
            <?php endif; ?>
        </div>

        <div class="page-list text-start">
            <h4>Opublikowane podstrony:</h4>
            <hr class="border-secondary">
            <ul class="nav flex-column">
                <?php foreach($pages as $p): ?>
                    <li class="nav-item">
                        <a class="nav-link text-info" href="view_page.php?slug=<?php echo $p['slug']; ?>">
                            → <?php echo htmlspecialchars($p['tytul']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                <?php if(empty($pages)): ?>
                    <li class="text-secondary">Brak opublikowanych treści.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>
