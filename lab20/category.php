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
$stmt_o = $conn->prepare("
    SELECT o.*, u.login as autor,
    (SELECT plik FROM ogloszenia_zdjecia z WHERE z.ido = o.ido LIMIT 1) as miniaturka 
    FROM ogloszenia o 
    LEFT JOIN uzytkownicy u ON o.idu = u.idu 
    WHERE o.idko = ? 
    ORDER BY o.datagodzina DESC
");
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
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { background: #111 !important; color: white !important; }
        .test-card { background: #222; border: 1px solid #555; padding: 20px; margin-bottom: 10px; border-radius: 5px; }
        .mini-photo { width: 120px; height: 120px; object-fit: cover; border-radius: 8px; border: 1px solid #444; }
        #add-map { height: 250px; border-radius: 8px; border: 1px solid #444; margin-top: 10px; }
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
                    <div class="test-card d-flex gap-3 align-items-center">
                        <?php if($o['miniaturka']): ?>
                            <img src="uploads/<?php echo $o['miniaturka']; ?>" class="mini-photo">
                        <?php else: ?>
                            <div class="mini-photo bg-dark d-flex align-items-center justify-content-center text-secondary small">Brak zdjęć</div>
                        <?php endif; ?>
                        
                        <div>
                            <h2 style="color: #0d6efd;" class="h4 mb-1"><?php echo htmlspecialchars($o['tytul']); ?></h2>
                            <p class="mb-1 text-success fw-bold"><?php echo number_format($o['cena'], 2, ',', ' '); ?> PLN</p>
                            <p class="small text-secondary mb-2">📍 <?php echo htmlspecialchars($o['lokalizacja_tekst']); ?></p>
                            <a href="ad.php?id=<?php echo $o['ido']; ?>" class="btn btn-sm btn-info">Zobacz szczegóły</a>
                        </div>
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
                                    <label class="form-label text-info">Zdjęcia (możesz wybrać wiele plików)</label>
                                    <div id="photo-inputs">
                                        <div class="input-group mb-2">
                                            <input type="file" name="zdjecia[]" class="form-control bg-black text-white border-secondary" accept="image/*" multiple>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-info w-100" onclick="addPhotoField()">+ Dodaj więcej pól na zdjęcia</button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row mb-2">
                                    <div class="col-4">
                                        <label>Kod pocztowy</label>
                                        <input type="text" id="add-kod" name="kod_pocztowy" class="form-control" placeholder="00-000">
                                    </div>
                                    <div class="col-8">
                                        <label>Lokalizacja (tekst)</label>
                                        <input type="text" name="lokalizacja_tekst" id="add-lok" class="form-control" required placeholder="np. Warszawa, Centrum">
                                    </div>
                                </div>
                                <div class="row mb-1">
                                    <div class="col-6">
                                        <label>Szerokość (Lat)</label>
                                        <input type="number" step="0.000001" name="lat" id="add-lat" class="form-control" required value="52.2297">
                                    </div>
                                    <div class="col-6">
                                        <label>Długość (Lng)</label>
                                        <input type="number" step="0.000001" name="lng" id="add-lng" class="form-control" required value="21.0122">
                                    </div>
                                </div>
                                <div class="small text-info mb-2">Kliknij na mapie, aby ustawić lokalizację:</div>
                                <div id="add-map"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary px-4">Opublikuj Ogłoszenie</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    var addMap, addMarker;

    document.getElementById('addAdModal').addEventListener('shown.bs.modal', function () {
        if (!addMap) {
            addMap = L.map('add-map').setView([52.2297, 21.0122], 10);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(addMap);

            addMarker = L.marker([52.2297, 21.0122], {draggable: true}).addTo(addMap);

            addMap.on('click', function(e) {
                var lat = e.latlng.lat.toFixed(6);
                var lng = e.latlng.lng.toFixed(6);
                addMarker.setLatLng(e.latlng);
                document.getElementById('add-lat').value = lat;
                document.getElementById('add-lng').value = lng;
            });

            addMarker.on('dragend', function(e) {
                var lat = addMarker.getLatLng().lat.toFixed(6);
                var lng = addMarker.getLatLng().lng.toFixed(6);
                document.getElementById('add-lat').value = lat;
                document.getElementById('add-lng').value = lng;
            });
        } else {
            addMap.invalidateSize();
        }
    });

    function addPhotoField() {
        const container = document.getElementById('photo-inputs');
        const div = document.createElement('div');
        div.className = 'input-group mb-2';
        div.innerHTML = `
            <input type="file" name="zdjecia[]" class="form-control bg-black text-white border-secondary" accept="image/*" multiple>
            <button class="btn btn-outline-danger" type="button" onclick="this.parentElement.remove()">X</button>
        `;
        container.appendChild(div);
    }

    document.getElementById('add-kod').addEventListener('blur', function() {
        const kod = this.value.trim();
        if (/^\d{2}-\d{3}$/.test(kod)) {
            fetch('get_city.php?kod=' + kod)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('add-lok').value = data.miasto;
                        if (data.lat && data.lng) {
                            document.getElementById('add-lat').value = data.lat;
                            document.getElementById('add-lng').value = data.lng;
                            if (addMap && addMarker) {
                                const latlng = new L.LatLng(data.lat, data.lng);
                                addMap.setView(latlng, 12);
                                addMarker.setLatLng(latlng);
                            }
                        }
                    }
                })
                .catch(err => console.error('Błąd pobierania miasta:', err));
        }
    });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
