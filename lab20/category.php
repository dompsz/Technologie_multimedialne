<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$idko = (int)($_GET['id'] ?? 0);

// Pobierz informacje o kategorii
$stmt_k = $conn->prepare("SELECT * FROM kategorie_ogloszen WHERE idko = ?");
$stmt_k->execute([$idko]);
$kategoria = $stmt_k->fetch();

if (!$kategoria) {
    die("Kategoria nie istnieje.");
}

// Pobierz ogłoszenia w tej kategorii
$stmt_o = $conn->prepare("SELECT o.*, u.login as autor FROM ogloszenia o LEFT JOIN uzytkownicy u ON o.idu = u.idu WHERE o.idko = ? ORDER BY o.datagodzina DESC");
$stmt_o->execute([$idko]);
$ogloszenia = $stmt_o->fetchAll();

$user_id = $_SESSION['lab20_user_id'] ?? null;

// DEBUG - Usuń po sprawdzeniu
if (isset($_GET['debug'])) {
    echo "<pre style='color: white; background: black; padding: 20px;'>";
    echo "ID Kategorii: " . $idko . "\n";
    echo "Liczba znalezionych: " . count($ogloszenia) . "\n";
    print_r($ogloszenia);
    echo "</pre>";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($kategoria['nazwa_kategorii']); ?> - Portal GIS Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .ad-card { background: #2a2a2a; border: 1px solid #444; border-radius: 8px; overflow: hidden; transition: transform 0.2s; color: white; }
        .ad-card:hover { transform: scale(1.01); border-color: #0d6efd; }
        .price-tag { color: #28a745; font-weight: bold; font-size: 1.2rem; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-primary">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">📍 PORTAL GIS LAB 20</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-light btn-sm me-2">Wróć do kategorii</a>
                <?php if ($user_id): ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h1 class="mb-1"><?php echo htmlspecialchars($kategoria['nazwa_kategorii']); ?></h1>
                <p class="text-secondary mb-0">Liczba ogłoszeń: <strong><?php echo count($ogloszenia); ?></strong></p>
            </div>
            <?php if ($user_id): ?>
                <button class="btn btn-primary w-auto px-4" data-bs-toggle="modal" data-bs-target="#addAdModal">➕ Dodaj Ogłoszenie</button>
            <?php endif; ?>
        </div>

        <div class="row row-cols-1 g-4">
            <?php foreach ($ogloszenia as $o): ?>
                <div class="col">
                    <a href="ad.php?id=<?php echo $o['ido']; ?>" class="text-decoration-none">
                        <div class="ad-card p-4">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-1 text-white"><?php echo htmlspecialchars($o['tytul'] ?? 'Bez tytułu'); ?></h4>
                                    <p class="text-info small mb-2">
                                        📍 <?php echo htmlspecialchars($o['lokalizacja_tekst'] ?? 'Brak lokalizacji'); ?> 
                                        • 🕒 <?php echo isset($o['datagodzina']) ? date('d.m.Y', strtotime($o['datagodzina'])) : 'Brak daty'; ?>
                                    </p>
                                    <div class="text-light">
                                        <?php 
                                            $opis = $o['tresc'] ?? '';
                                            echo htmlspecialchars(mb_strlen($opis) > 150 ? mb_substr($opis, 0, 150) . '...' : $opis); 
                                        ?>
                                    </div>
                                </div>
                                <div class="col-md-4 text-md-end">
                                    <div class="price-tag mb-2"><?php echo number_format($o['cena'] ?? 0, 2, ',', ' '); ?> PLN</div>
                                    <div class="text-secondary small">Autor: <?php echo htmlspecialchars($o['autor'] ?? 'Anonim'); ?></div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
            <?php if (empty($ogloszenia)): ?>
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Brak ogłoszeń w tej kategorii.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal dodawania ogłoszenia -->
    <?php if ($user_id): ?>
    <div class="modal fade" id="addAdModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light border-secondary">
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="add_ad">
                    <input type="hidden" name="idko" value="<?php echo $idko; ?>">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Dodaj Ogłoszenie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tytuł</label>
                                    <input type="text" name="tytul" class="form-control bg-black text-white border-secondary" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Cena (PLN)</label>
                                    <input type="number" step="0.01" name="cena" class="form-control bg-black text-white border-secondary" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Opis</label>
                                    <textarea name="tresc" class="form-control bg-black text-white border-secondary" rows="5" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Lokalizacja (tekstowo)</label>
                                    <input type="text" name="lokalizacja_tekst" class="form-control bg-black text-white border-secondary" placeholder="np. Warszawa, Centrum" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Szerokość (Lat)</label>
                                            <input type="number" step="0.0001" name="lat" id="latInput" class="form-control bg-black text-white border-secondary" required>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label">Długość (Lng)</label>
                                            <input type="number" step="0.0001" name="lng" id="lngInput" class="form-control bg-black text-white border-secondary" required>
                                        </div>
                                    </div>
                                </div>
                                <p class="small text-secondary mt-2">Współrzędne można pobrać np. z Google Maps (klikając prawym na mapie).</p>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Anuluj</button>
                        <button type="submit" class="btn btn-primary">Opublikuj Ogłoszenie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
