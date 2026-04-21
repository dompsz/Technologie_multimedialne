<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db_config.php';

try {
    // Pobierz wszystkie galerie
    $stmt = $conn->query("SELECT g.*, u.login as autor, (SELECT COUNT(*) FROM zdjecia z WHERE z.idg = g.idg) as foto_count FROM galerie g JOIN uzytkownicy u ON g.idu = u.idu");
    $galerie = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Błąd bazy danych: " . $e->getMessage() . "<br>Upewnij się, że tabele z pliku db_setup.sql zostały zaimportowane do bazy pszczolk_z18.");
}

$user_id = $_SESSION['lab18_user_id'] ?? null;
$user_role = $_SESSION['lab18_role'] ?? 'guest';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Internetowa Galeria Zdjęć - Lab 18</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .gallery-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; transition: transform 0.2s; height: 100%; }
        .gallery-card:hover { transform: translateY(-5px); border-color: var(--accent-color); }
        .text-accent { color: var(--accent-color) !important; }
        .commercial-badge { position: absolute; top: 10px; right: 10px; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold text-accent" href="index.php">🖼️ GALERIA LAB 18</a>
            <div class="d-flex align-items-center">
                <?php if ($user_id): ?>
                    <span class="text-secondary me-3">Witaj, <strong><?php echo htmlspecialchars($_SESSION['lab18_login']); ?></strong></span>
                    <?php if ($user_role === 'admin'): ?>
                        <a href="admin.php" class="btn btn-outline-warning btn-sm me-2">🛡️ Panel Admina</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success btn-sm me-2">Zaloguj się</a>
                    <a href="register.php" class="btn btn-accent btn-sm">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Galerie Zdjęć</h1>
            <?php if ($user_id): ?>
                <button class="btn btn-accent w-auto px-4" data-bs-toggle="modal" data-bs-target="#addGalleryModal">➕ Utwórz Galerię</button>
            <?php endif; ?>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($galerie as $g): ?>
                <div class="col">
                    <div class="gallery-card p-4 position-relative">
                        <?php if ($g['czy_komercyjna']): ?>
                            <span class="badge bg-warning text-dark commercial-badge">KOMERCYJNA</span>
                        <?php endif; ?>
                        <h3 class="h4 mb-2"><a href="gallery.php?id=<?php echo $g['idg']; ?>" class="text-accent text-decoration-none"><?php echo htmlspecialchars($g['nazwa_galerii']); ?></a></h3>
                        <p class="text-secondary small mb-3">Autor: <?php echo htmlspecialchars($g['autor']); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-secondary"><?php echo $g['foto_count']; ?> zdjęć</span>
                            <a href="gallery.php?id=<?php echo $g['idg']; ?>" class="btn btn-sm btn-outline-light">Otwórz</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            <?php if (empty($galerie)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Brak galerii w systemie.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal dodawania galerii -->
    <?php if ($user_id): ?>
    <div class="modal fade" id="addGalleryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light border-secondary">
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="add_gallery">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Nowa Galeria</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Nazwa galerii</label>
                            <input type="text" name="nazwa_galerii" class="form-control bg-black text-white border-secondary" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="czy_komercyjna" id="comCheck">
                            <label class="form-check-label" for="comCheck">
                                Galeria komercyjna (dodaje znaki wodne)
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="submit" class="btn btn-accent">Utwórz</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <footer class="text-center py-4 mt-5 border-top border-secondary text-secondary">
        <div class="container">
            <small>&copy; 2026 Laboratorium 18 - System Galerii Zdjęć</small>
            <br><a href="../index.php" class="text-secondary">Powrót do Strony Głównej</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
