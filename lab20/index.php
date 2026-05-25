<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Pobierz wszystkie kategorie
$stmt = $conn->query("SELECT k.*, (SELECT COUNT(*) FROM ogloszenia o WHERE o.idko = k.idko) as ogl_count FROM kategorie_ogloszen k");
$kategorie = $stmt->fetchAll();

$user_id = $_SESSION['lab20_user_id'] ?? null;
$user_role = $_SESSION['lab20_role'] ?? 'guest';
$user_login = $_SESSION['lab20_login'] ?? '';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Portal Ogłoszeniowy GIS - Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .category-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; transition: transform 0.2s; height: 100%; }
        .category-card:hover { transform: translateY(-5px); border-color: var(--accent-color); }
        .text-accent { color: var(--accent-color) !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-primary">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">📍 PORTAL GIS LAB 20</a>
            <div class="d-flex align-items-center">
                <?php if ($user_id): ?>
                    <span class="text-secondary me-3">Witaj, <strong><?php echo htmlspecialchars($user_login); ?></strong> <?php echo getRoleLabel($user_role); ?></span>
                    <?php if ($user_role === 'admin' || $user_role === 'moderator'): ?>
                        <a href="admin.php" class="btn btn-outline-warning btn-sm me-2">🛡️ Panel Admina</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success btn-sm me-2">Zaloguj się</a>
                    <a href="register.php" class="btn btn-primary btn-sm">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Znajdź to, czego szukasz</h1>
            <p class="lead text-secondary">Portal ogłoszeniowy zintegrowany z mapami GIS</p>
        </div>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php foreach ($kategorie as $k): ?>
                <div class="col">
                    <div class="category-card p-4 text-center">
                        <h3 class="h4 mb-3"><a href="category.php?id=<?php echo $k['idko']; ?>" class="text-primary text-decoration-none"><?php echo htmlspecialchars($k['nazwa_kategorii']); ?></a></h3>
                        <p class="text-secondary mb-3"><?php echo $k['ogl_count']; ?> ogłoszeń</p>
                        <a href="category.php?id=<?php echo $k['idko']; ?>" class="btn btn-sm btn-outline-primary px-4">Przeglądaj</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="text-center py-4 mt-5 border-top border-secondary text-secondary">
        <div class="container">
            <small>&copy; 2026 Laboratorium 20 - System Ogłoszeń GIS</small>
            <br><a href="../index.php" class="text-secondary">Powrót do Strony Głównej</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
