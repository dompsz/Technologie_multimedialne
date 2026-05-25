<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Pobierz wszystkie ogłoszenia z danymi do mapy
$stmt = $conn->query("SELECT o.*, k.nazwa_kategorii FROM ogloszenia o JOIN kategorie_ogloszen k ON o.idko = k.idko");
$ads = $stmt->fetchAll();

$user_id = $_SESSION['lab20_user_id'] ?? null;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Mapa zbiorcza - Portal GIS Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #main-map { height: 600px; border-radius: 12px; border: 1px solid var(--border-color); }
        .text-primary { color: #0d6efd !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-primary">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="index.php">📍 PORTAL GIS LAB 20</a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-outline-light btn-sm">Wróć do strony głównej</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4 pb-5">
        <h2 class="mb-4">Wszystkie ogłoszenia na mapie</h2>
        <div id="main-map"></div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        var map = L.map('main-map').setView([52.2297, 21.0122], 6);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        var ads = <?php echo json_encode($ads); ?>;
        
        ads.forEach(function(ad) {
            if (ad.lat && ad.lng) {
                var marker = L.marker([ad.lat, ad.lng]).addTo(map);
                marker.bindPopup(
                    "<b>" + ad.tytul + "</b><br>" +
                    "Kategoria: " + ad.nazwa_kategorii + "<br>" +
                    "Cena: " + ad.cena + " PLN<br>" +
                    "<a href='ad.php?id=" + ad.ido + "' class='btn btn-sm btn-primary mt-2 text-white'>Zobacz ogłoszenie</a>"
                );
            }
        });
    </script>
</body>
</html>
