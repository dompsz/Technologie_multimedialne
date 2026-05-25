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

// DEBUG AGRESYWNY
echo "<div style='background:white; color:black; padding:10px; border:5px solid red; position:fixed; top:0; left:0; z-index:9999;'>";
echo "DEBUG: Kat ID: $idko | Count: " . count($ogloszenia);
if (count($ogloszenia) > 0) {
    echo " | Pierwszy tytul: " . $ogloszenia[0]['tytul'];
}
echo "</div>";
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($kategoria['nazwa_kategorii']); ?> - Portal GIS Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-primary">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">📍 PORTAL GIS LAB 20</a>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <h1><?php echo htmlspecialchars($kategoria['nazwa_kategorii']); ?></h1>
        <p>Liczba: <?php echo count($ogloszenia); ?></p>

        <div class="list-group mt-4">
            <?php foreach ($ogloszenia as $o): ?>
                <a href="ad.php?id=<?php echo $o['ido']; ?>" class="list-group-item list-group-item-action bg-dark text-white border-secondary mb-2">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1 text-primary"><?php echo htmlspecialchars($o['tytul']); ?></h5>
                        <small><?php echo $o['cena']; ?> PLN</small>
                    </div>
                    <p class="mb-1"><?php echo htmlspecialchars(mb_substr($o['tresc'], 0, 100)); ?>...</p>
                    <small>Lokalizacja: <?php echo htmlspecialchars($o['lokalizacja_tekst']); ?></small>
                </a>
            <?php endforeach; ?>
        </div>
        
        <?php if (empty($ogloszenia)): ?>
            <div class="alert alert-info">Brak ogłoszeń.</div>
        <?php endif; ?>
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
<?php exit(); // Zatrzymujemy wszystko inne aby sprawdzic czysty HTML ?>

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
