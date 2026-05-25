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

// Pobierz ogłoszenia
$stmt_o = $conn->prepare("SELECT o.*, u.login as autor FROM ogloszenia o LEFT JOIN uzytkownicy u ON o.idu = u.idu WHERE o.idko = ? ORDER BY o.datagodzina DESC");
$stmt_o->execute([$idko]);
$ogloszenia = $stmt_o->fetchAll();

$user_id = $_SESSION['lab20_user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Portal GIS Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #111 !important; color: white !important; }
        .test-card { background: #222; border: 1px solid #555; padding: 20px; margin-bottom: 10px; border-radius: 5px; }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <a href="index.php" class="btn btn-secondary mb-4">Wróć</a>
        <h1>Kategoria: <?php echo htmlspecialchars($kategoria['nazwa_kategorii']); ?></h1>
        <hr>
        
        <?php if ($user_id): ?>
            <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#addAdModal">DODAJ OGŁOSZENIE</button>
        <?php endif; ?>

        <div class="mt-4">
            <?php if (empty($ogloszenia)): ?>
                <div class="alert alert-warning">Brak ogłoszeń w bazie dla tej kategorii.</div>
            <?php else: ?>
                <p>Znaleziono: <?php echo count($ogloszenia); ?></p>
                <?php foreach ($ogloszenia as $o): ?>
                    <div class="test-card">
                        <h2 style="color: #0d6efd;"><?php echo htmlspecialchars($o['tytul']); ?></h2>
                        <p><strong>Cena:</strong> <?php echo $o['cena']; ?> PLN</p>
                        <p><strong>Lokalizacja:</strong> <?php echo htmlspecialchars($o['lokalizacja_tekst']); ?></p>
                        <p><?php echo nl2br(htmlspecialchars($o['tresc'])); ?></p>
                        <a href="ad.php?id=<?php echo $o['ido']; ?>" class="btn btn-sm btn-info">Zobacz szczegóły</a>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal dodawania ogłoszenia -->
    <div class="modal fade" id="addAdModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light">
                <form action="actions.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_ad">
                    <input type="hidden" name="idko" value="<?php echo $idko; ?>">
                    <div class="modal-header">
                        <h5 class="modal-title">Dodaj Ogłoszenie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Tytuł</label>
                                    <input type="text" name="tytul" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Cena</label>
                                    <input type="number" step="0.01" name="cena" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Opis</label>
                                    <textarea name="tresc" class="form-control" rows="5" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Zdjęcia (możesz wybrać kilka)</label>
                                    <input type="file" name="zdjecia[]" class="form-control bg-dark text-white border-secondary" accept="image/*" multiple>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Lokalizacja (tekst)</label>
                                    <input type="text" name="lokalizacja_tekst" class="form-control" required>
                                </div>
                                <div class="row">
                                    <div class="col-6">
                                        <label>Lat</label>
                                        <input type="number" step="0.0001" name="lat" class="form-control" required>
                                    </div>
                                    <div class="col-6">
                                        <label>Lng</label>
                                        <input type="number" step="0.0001" name="lng" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Dodaj</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
