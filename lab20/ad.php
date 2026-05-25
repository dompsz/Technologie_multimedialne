<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$ido = (int)($_GET['id'] ?? 0);

// Pobierz informacje o ogłoszeniu
$stmt_o = $conn->prepare("
    SELECT o.*, k.nazwa_kategorii, u.login as autor
    FROM ogloszenia o 
    JOIN kategorie_ogloszen k ON o.idko = k.idko 
    JOIN uzytkownicy u ON o.idu = u.idu 
    WHERE o.ido = ?
");
$stmt_o->execute([$ido]);
$ad = $stmt_o->fetch();

if (!$ad) {
    die("Ogłoszenie nie istnieje.");
}

$user_id = $_SESSION['lab20_user_id'] ?? null;
$user_role = $_SESSION['lab20_role'] ?? 'guest';
$is_owner = ($user_id && ($ad['idu'] == $user_id || $user_role === 'admin' || $user_role === 'moderator'));

// Logowanie wyświetlenia (opcjonalnie, zgodnie z logi_ogloszen)
if ($user_id) {
    logAction($conn, $ido, $user_id, 'wyswietlenie');
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($ad['tytul']); ?> - Portal GIS Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <style>
        #map { height: 400px; border-radius: 12px; border: 1px solid var(--border-color); }
        .price-big { font-size: 2.5rem; color: #28a745; font-weight: bold; }
        .ad-meta { background: var(--card-bg); border-radius: 12px; padding: 20px; border: 1px solid var(--border-color); }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-primary">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">📍 PORTAL GIS LAB 20</a>
            <div class="d-flex align-items-center">
                <a href="category.php?id=<?php echo $ad['idko']; ?>" class="btn btn-outline-light btn-sm me-2">Powrót do kategorii</a>
                <?php if ($user_id): ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-3"><?php echo htmlspecialchars($ad['tytul']); ?></h1>
                <div class="mb-4">
                    <span class="badge bg-primary px-3 py-2"><?php echo htmlspecialchars($ad['nazwa_kategorii']); ?></span>
                    <span class="text-secondary ms-3">📍 <?php echo htmlspecialchars($ad['lokalizacja_tekst']); ?></span>
                    <span class="text-secondary ms-3">🕒 <?php echo date('d.m.Y H:i', strtotime($ad['datagodzina'])); ?></span>
                </div>

                <div class="price-big mb-4"><?php echo number_format($ad['cena'], 2, ',', ' '); ?> PLN</div>

                <div class="mb-5">
                    <h4 class="text-primary border-bottom border-secondary pb-2 mb-3">Opis ogłoszenia</h4>
                    <p class="lead" style="white-space: pre-line;"><?php echo htmlspecialchars($ad['tresc']); ?></p>
                </div>

                <div class="mb-5">
                    <h4 class="text-primary border-bottom border-secondary pb-2 mb-3">Lokalizacja na mapie (GIS)</h4>
                    <div id="map"></div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="ad-meta mb-4">
                    <h5>Informacje o autorze</h5>
                    <hr class="border-secondary">
                    <p class="mb-1">Użytkownik: <span class="text-primary fw-bold"><?php echo htmlspecialchars($ad['autor']); ?></span></p>
                    <p class="small text-secondary">Z nami od: <?php // Tu można by pobrać datę rejestracji ?></p>
                    
                    <?php if ($is_owner): ?>
                        <div class="mt-4 pt-4 border-top border-secondary">
                            <button class="btn btn-outline-info w-100 mb-2" data-bs-toggle="modal" data-bs-target="#editAdModal">Edytuj ogłoszenie</button>
                            <form action="actions.php" method="POST" onsubmit="return confirm('Czy na pewno chcesz usunąć to ogłoszenie?')">
                                <input type="hidden" name="action" value="delete_ad">
                                <input type="hidden" name="ido" value="<?php echo $ido; ?>">
                                <button type="submit" class="btn btn-outline-danger w-100">Usuń ogłoszenie</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="card bg-dark text-light border-secondary p-4">
                    <h6>Bezpieczeństwo</h6>
                    <small class="text-secondary">Pamiętaj, aby zawsze sprawdzać przedmiot osobiście przed dokonaniem płatności.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal edycji ogłoszenia -->
    <?php if ($is_owner): ?>
    <div class="modal fade" id="editAdModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content bg-dark text-light border-secondary">
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="edit_ad">
                    <input type="hidden" name="ido" value="<?php echo $ido; ?>">
                    <div class="modal-header border-secondary">
                        <h5 class="modal-title">Edytuj Ogłoszenie</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tytuł</label>
                            <input type="text" name="tytul" class="form-control bg-black text-white border-secondary" value="<?php echo htmlspecialchars($ad['tytul']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Cena (PLN)</label>
                            <input type="number" step="0.01" name="cena" class="form-control bg-black text-white border-secondary" value="<?php echo $ad['cena']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea name="tresc" class="form-control bg-black text-white border-secondary" rows="5" required><?php echo htmlspecialchars($ad['tresc']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lokalizacja (tekstowo)</label>
                            <input type="text" name="lokalizacja_tekst" class="form-control bg-black text-white border-secondary" value="<?php echo htmlspecialchars($ad['lokalizacja_tekst']); ?>" required>
                        </div>
                    </div>
                    <div class="modal-footer border-secondary">
                        <button type="button" class="btn btn-secondary" data-bs-modal="modal">Anuluj</button>
                        <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        var lat = <?php echo $ad['lat'] ?? 52.2297; ?>;
        var lng = <?php echo $ad['lng'] ?? 21.0122; ?>;
        
        var map = L.map('map').setView([lat, lng], 13);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup("<b><?php echo htmlspecialchars($ad['tytul']); ?></b><br><?php echo htmlspecialchars($ad['lokalizacja_tekst']); ?>").openPopup();
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
