<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$idz = (int)($_GET['id'] ?? 0);

// Pobierz informacje o zdjęciu
$stmt_z = $conn->prepare("
    SELECT z.*, g.nazwa_galerii, g.czy_komercyjna, u.login as autor,
    (SELECT AVG(ocena) FROM oceny o WHERE o.idz = z.idz) as avg_ocena,
    (SELECT COUNT(*) FROM oceny o WHERE o.idz = z.idz) as count_ocena
    FROM zdjecia z 
    JOIN galerie g ON z.idg = g.idg 
    JOIN uzytkownicy u ON z.idu = u.idu 
    WHERE z.idz = ?
");
$stmt_z->execute([$idz]);
$photo = $stmt_z->fetch();

if (!$photo) {
    die("Zdjęcie nie istnieje.");
}

// Pobierz komentarze
$stmt_k = $conn->prepare("SELECT k.*, u.login FROM komentarze k JOIN uzytkownicy u ON k.idu = u.idu WHERE k.idz = ? ORDER BY k.datagodzina ASC");
$stmt_k->execute([$idz]);
$komentarze = $stmt_k->fetchAll();

// Pobierz ID poprzedniego i następnego zdjęcia w tej samej galerii
$stmt_prev = $conn->prepare("SELECT idz FROM zdjecia WHERE idg = ? AND idz < ? ORDER BY idz DESC LIMIT 1");
$stmt_prev->execute([$photo['idg'], $idz]);
$prev_id = $stmt_prev->fetchColumn();

$stmt_next = $conn->prepare("SELECT idz FROM zdjecia WHERE idg = ? AND idz > ? ORDER BY idz ASC LIMIT 1");
$stmt_next->execute([$photo['idg'], $idz]);
$next_id = $stmt_next->fetchColumn();

$user_id = $_SESSION['lab18_user_id'] ?? null;
$is_author = ($user_id && ($photo['idu'] == $user_id || $_SESSION['lab18_login'] === 'admin'));

// Czy użytkownik już ocenił?
$user_rating = 0;
if ($user_id) {
    $stmt_ur = $conn->prepare("SELECT ocena FROM oceny WHERE idz = ? AND idu = ?");
    $stmt_ur->execute([$idz, $user_id]);
    $user_rating = $stmt_ur->fetchColumn() ?: 0;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($photo['tytul']); ?> - Galeria Lab 18</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .photo-full { max-width: 100%; border-radius: 12px; border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.5); transition: filter 0.5s; }
        .photo-container { position: relative; display: inline-block; width: 100%; text-align: center; }
        .watermark-overlay { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-30deg); font-size: 5rem; color: rgba(255,255,255,0.1); font-weight: bold; pointer-events: none; white-space: nowrap; text-transform: uppercase; z-index: 5; }
        .comment-box { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 15px; }
        .rating-star { font-size: 1.5rem; cursor: pointer; color: #444; transition: color 0.2s; }
        .rating-star.active { color: #ffc107; }
        .text-accent { color: var(--accent-color) !important; }
        
        /* Klasy filtrów */
        .filter-none { filter: none; }
        .filter-mono { filter: grayscale(100%); }
        .filter-sepia { filter: sepia(100%); }
        
        #editFilterPreview { max-width: 100%; height: 120px; object-fit: cover; border: 1px solid #444; border-radius: 4px; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold text-accent" href="index.php">🖼️ GALERIA LAB 18</a>
            <div class="d-flex align-items-center">
                <a href="gallery.php?id=<?php echo $photo['idg']; ?>" class="btn btn-outline-light btn-sm me-2">Powrót do galerii</a>
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
        <div class="row">
            <!-- Kolumna ze zdjęciem -->
            <div class="col-lg-8">
                <div class="photo-container mb-4">
                    <img src="uploads/<?php echo $photo['plik']; ?>" class="photo-full filter-<?php echo $photo['filtr'] ?? 'none'; ?>" alt="<?php echo htmlspecialchars($photo['tytul']); ?>">
                    <?php if ($photo['czy_komercyjna']): ?>
                        <div class="watermark-overlay">LAB 18 SAMPLE</div>
                    <?php endif; ?>
                    
                    <!-- Nawigacja -->
                    <div class="d-flex justify-content-between mt-3">
                        <?php if ($prev_id): ?>
                            <a href="photo.php?id=<?php echo $prev_id; ?>" class="btn btn-outline-light btn-sm w-auto px-4">« Poprzednie</a>
                        <?php else: ?>
                            <span></span>
                        <?php endif; ?>
                        
                        <?php if ($next_id): ?>
                            <a href="photo.php?id=<?php echo $next_id; ?>" class="btn btn-outline-light btn-sm w-auto px-4">Następne »</a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h2 class="mb-1"><?php echo htmlspecialchars($photo['tytul']); ?></h2>
                        <p class="text-secondary"><?php echo nl2br(htmlspecialchars($photo['opis'])); ?></p>
                    </div>
                    <div class="text-end">
                        <div class="h3 mb-0 text-warning">⭐ <?php echo $photo['avg_ocena'] ? round($photo['avg_ocena'], 1) : '0.0'; ?></div>
                        <small class="text-secondary"><?php echo $photo['count_ocena']; ?> ocen</small>
                    </div>
                </div>
            </div>

            <!-- Kolumna z interakcjami -->
            <div class="col-lg-4">
                <div class="card bg-dark text-light border-secondary p-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Informacje</h5>
                        <?php if ($is_author): ?>
                            <button class="btn btn-xxs btn-outline-info" data-bs-toggle="modal" data-bs-target="#editPhotoModal">Edytuj</button>
                        <?php endif; ?>
                    </div>
                    <hr class="border-secondary">
                    <p class="small mb-1">Autor: <span class="text-accent"><?php echo htmlspecialchars($photo['autor']); ?></span></p>
                    <p class="small mb-1">Galeria: <span class="text-accent"><?php echo htmlspecialchars($photo['nazwa_galerii']); ?></span></p>
                    <p class="small mb-1">Filtr: <span class="text-info"><?php echo $photo['filtr'] ?? 'none'; ?></span></p>
                    <p class="small mb-3">Data: <?php echo $photo['datagodzina']; ?></p>

                    <?php if ($user_id): ?>
                        <hr class="border-secondary">
                        <h6>Twoja ocena</h6>
                        <div class="d-flex gap-1 mb-2">
                            <?php for($i=1; $i<=5; $i++): ?>
                                <form action="actions.php" method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="rate">
                                    <input type="hidden" name="idz" value="<?php echo $idz; ?>">
                                    <input type="hidden" name="ocena" value="<?php echo $i; ?>">
                                    <button type="submit" class="btn p-0 border-0 bg-transparent">
                                        <span class="rating-star <?php echo $i <= $user_rating ? 'active' : ''; ?>">★</span>
                                    </button>
                                </form>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <h5>Komentarze (<?php echo count($komentarze); ?>)</h5>
                    <hr class="border-secondary">
                    
                    <div style="max-height: 400px; overflow-y: auto;" class="pe-2">
                        <?php foreach ($komentarze as $k): ?>
                            <div class="comment-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong class="text-accent small"><?php echo htmlspecialchars($k['login']); ?></strong>
                                    <small class="text-secondary" style="font-size: 0.7rem;"><?php echo $k['datagodzina']; ?></small>
                                </div>
                                <p class="small mb-0 text-light"><?php echo nl2br(htmlspecialchars($k['tresc'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($komentarze)): ?>
                            <p class="text-muted small">Brak komentarzy. Bądź pierwszy!</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($user_id): ?>
                        <form action="actions.php" method="POST" class="mt-3">
                            <input type="hidden" name="action" value="add_comment">
                            <input type="hidden" name="idz" value="<?php echo $idz; ?>">
                            <div class="mb-2">
                                <textarea name="tresc" class="form-control bg-black text-white border-secondary small" rows="3" placeholder="Napisz komentarz..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-sm btn-accent w-100">Dodaj Komentarz</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal edycji zdjęcia -->
    <?php if ($is_author): ?>
    <div class="modal fade" id="editPhotoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light border-secondary">
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="edit_photo">
                    <input type="hidden" name="idz" value="<?php echo $idz; ?>">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Edytuj zdjęcie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tytuł</label>
                            <input type="text" name="tytul" class="form-control bg-black text-white border-secondary" value="<?php echo htmlspecialchars($photo['tytul']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea name="opis" class="form-control bg-black text-white border-secondary" rows="3"><?php echo htmlspecialchars($photo['opis']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Zmień filtr</label>
                            <select name="filtr" id="editFilterSelect" class="form-select bg-black text-white border-secondary mb-2">
                                <option value="none" <?php echo $photo['filtr']=='none'?'selected':''; ?>>Brak filtra</option>
                                <option value="mono" <?php echo $photo['filtr']=='mono'?'selected':''; ?>>Monochromatyczny</option>
                                <option value="sepia" <?php echo $photo['filtr']=='sepia'?'selected':''; ?>>Sepia</option>
                            </select>
                            <img id="editFilterPreview" src="uploads/<?php echo $photo['plik']; ?>" class="filter-<?php echo $photo['filtr']; ?>" alt="Podgląd">
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="submit" class="btn btn-accent">Zapisz zmiany</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('editFilterSelect').addEventListener('change', function() {
            const preview = document.getElementById('editFilterPreview');
            preview.className = '';
            if (this.value !== 'none') preview.classList.add('filter-' + this.value);
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
