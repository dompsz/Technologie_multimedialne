<?php
session_start();
require_once 'db_config.php';

$idg = (int)($_GET['id'] ?? 0);

// Pobierz informacje o galerii
$stmt_g = $conn->prepare("SELECT g.*, u.login as autor FROM galerie g JOIN uzytkownicy u ON g.idu = u.idu WHERE g.idg = ?");
$stmt_g->execute([$idg]);
$galeria = $stmt_g->fetch();

if (!$galeria) {
    die("Galeria nie istnieje.");
}

// Pobierz zdjęcia w tej galerii
$stmt_f = $conn->prepare("SELECT z.*, (SELECT AVG(ocena) FROM oceny o WHERE o.idz = z.idz) as avg_ocena FROM zdjecia z WHERE z.idg = ? ORDER BY z.datagodzina DESC");
$stmt_f->execute([$idg]);
$zdjecia = $stmt_f->fetchAll();

$user_id = $_SESSION['lab18_user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($galeria['nazwa_galerii']); ?> - Galeria Lab 18</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .photo-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; transition: transform 0.2s; }
        .photo-card:hover { transform: scale(1.02); border-color: var(--accent-color); }
        .thumb-container { height: 200px; overflow: hidden; background: #000; position: relative; }
        .thumb-container img { width: 100%; height: 100%; object-fit: cover; }
        .watermark { position: absolute; bottom: 5px; right: 5px; background: rgba(0,0,0,0.5); color: #fff; font-size: 0.6rem; padding: 2px 5px; pointer-events: none; border-radius: 3px; }
        .text-accent { color: var(--accent-color) !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold text-accent" href="index.php">🖼️ GALERIA LAB 18</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Wróć do list galerii</a>
                <?php if ((isset($_SESSION['lab18_role']) && $_SESSION['lab18_role'] === 'admin') || (isset($_SESSION['lab18_login']) && $_SESSION['lab18_login'] === 'admin')): ?>
                    <a href="admin.php" class="btn btn-outline-warning btn-sm me-2">🛡️ Panel Admina</a>
                <?php endif; ?>
                <?php if ($user_id): ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'photo_added'): ?>
            <div class="alert alert-success">✓ Zdjęcie zostało dodane pomyślnie.</div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h1 class="mb-1"><?php echo htmlspecialchars($galeria['nazwa_galerii']); ?></h1>
                <p class="text-secondary mb-0">Autor: <strong><?php echo htmlspecialchars($galeria['autor']); ?></strong> 
                <?php if ($galeria['czy_komercyjna']): ?> <span class="badge bg-warning text-dark ms-2">KOMERCYJNA</span><?php endif; ?></p>
            </div>
            <?php if ($user_id): ?>
                <button class="btn btn-accent w-auto px-4" data-bs-toggle="modal" data-bs-target="#addPhotoModal">➕ Dodaj Zdjęcie</button>
            <?php endif; ?>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php foreach ($zdjecia as $z): ?>
                <div class="col">
                    <a href="photo.php?id=<?php echo $z['idz']; ?>" class="text-decoration-none">
                        <div class="photo-card">
                            <div class="thumb-container">
                                <img src="uploads/<?php echo $z['plik']; ?>" alt="<?php echo htmlspecialchars($z['tytul']); ?>">
                                <?php if ($galeria['czy_komercyjna']): ?>
                                    <div class="watermark">LAB 18 SAMPLE</div>
                                <?php endif; ?>
                            </div>
                            <div class="p-3">
                                <h5 class="text-light text-truncate mb-1"><?php echo htmlspecialchars($z['tytul']); ?></h5>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-warning small">⭐ <?php echo $z['avg_ocena'] ? round($z['avg_ocena'], 1) : 'brak'; ?></span>
                                    <small class="text-secondary"><?php echo date('d.m.Y', strtotime($z['datagodzina'])); ?></small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($zdjecia)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Ta galeria jest jeszcze pusta.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal dodawania zdjęcia -->
    <?php if ($user_id): ?>
    <div class="modal fade" id="addPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light border-secondary">
                <form action="actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_photo">
                    <input type="hidden" name="idg" value="<?php echo $idg; ?>">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Dodaj Zdjęcie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tytuł</label>
                            <input type="text" name="tytul" class="form-control bg-black text-white border-secondary" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea name="opis" class="form-control bg-black text-white border-secondary" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Plik obrazu</label>
                            <input type="file" name="plik" class="form-control bg-black text-white border-secondary" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="submit" class="btn btn-accent">Wyślij</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
