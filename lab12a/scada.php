<?php
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 12a - Wizualizacja SCADA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .scada-container {
            position: relative;
            max-width: 800px;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .sensor-tag {
            position: absolute;
            background: rgba(0, 0, 0, 0.7);
            color: #0f0;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 14px;
            pointer-events: none;
            transition: all 0.5s ease;
        }
        #scada-svg {
            width: 100%;
            height: auto;
            border: 1px solid #ddd;
            background: #f9f9f9;
        }
        .status-panel {
            margin-top: 20px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container text-center">
    <h1 class="mt-4 mb-4">System Wizualizacji SCADA - Lab 12a</h1>
    
    <div class="mb-3">
        <a href="formularz.php" class="btn btn-secondary">Dodaj Pomiary</a>
        <a href="tabela.php" class="btn btn-info">Historia Pomiarów</a>
        <a href="wykres.php" class="btn btn-primary">Wykresy</a>
    </div>

    <div class="scada-container">
        <!-- SVG Plan Piętra / Schemat Techniczny -->
        <svg id="scada-svg" viewBox="0 0 800 400" xmlns="http://www.w3.org/2000/svg">
            <!-- Ściany zewnętrzne -->
            <rect x="50" y="50" width="700" height="300" fill="none" stroke="#333" stroke-width="3" />
            <!-- Pokoje -->
            <line x1="300" y1="50" x2="300" y2="350" stroke="#333" stroke-width="2" />
            <line x1="550" y1="50" x2="550" y2="350" stroke="#333" stroke-width="2" />
            <line x1="300" y1="200" x2="550" y2="200" stroke="#333" stroke-width="2" />
            
            <!-- Etykiety Pomieszczeń -->
            <text x="175" y="80" text-anchor="middle" fill="#999">Hala Produkcyjna A</text>
            <text x="425" y="80" text-anchor="middle" fill="#999">Magazyn</text>
            <text x="425" y="230" text-anchor="middle" fill="#999">Biuro</text>
            <text x="675" y="80" text-anchor="middle" fill="#999">Hala B</text>
        </svg>

        <!-- Dynamiczne Tagi Sensorów (pozycjonowane absolutnie nad SVG) -->
        <div id="sensor-x1" class="sensor-tag" style="top: 150px; left: 150px;">x1: -- V</div>
        <div id="sensor-x2" class="sensor-tag" style="top: 150px; left: 400px;">x2: -- V</div>
        <div id="sensor-x3" class="sensor-tag" style="top: 280px; left: 400px;">x3: -- V</div>
        <div id="sensor-x4" class="sensor-tag" style="top: 150px; left: 650px;">x4: -- V</div>
        <div id="sensor-x5" class="sensor-tag" style="top: 320px; left: 150px;">x5: -- V</div>
    </div>

    <div class="status-panel card p-3 mx-auto" style="max-width: 400px;">
        <h6>Ostatnia aktualizacja: <span id="last-update">--:--:--</span></h6>
        <div id="status-indicator" class="badge bg-success">Połączono (Live)</div>
    </div>
</div>

<script>
function updateSCADA() {
    fetch('get_latest_data.php')
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                document.getElementById('status-indicator').className = 'badge bg-danger';
                document.getElementById('status-indicator').innerText = 'Błąd danych';
                return;
            }
            
            // Aktualizacja wartości
            document.getElementById('sensor-x1').innerText = 'x1: ' + parseFloat(data.x1).toFixed(2) + ' V';
            document.getElementById('sensor-x2').innerText = 'x2: ' + parseFloat(data.x2).toFixed(2) + ' V';
            document.getElementById('sensor-x3').innerText = 'x3: ' + parseFloat(data.x3).toFixed(2) + ' V';
            document.getElementById('sensor-x4').innerText = 'x4: ' + parseFloat(data.x4).toFixed(2) + ' V';
            document.getElementById('sensor-x5').innerText = 'x5: ' + parseFloat(data.x5).toFixed(2) + ' V';
            
            document.getElementById('last-update').innerText = data.datetime.split(' ')[1];
            document.getElementById('status-indicator').className = 'badge bg-success';
            document.getElementById('status-indicator').innerText = 'Połączono (Live)';

            // Prosta animacja "mrugania" przy zmianie danych
            const tags = document.querySelectorAll('.sensor-tag');
            tags.forEach(tag => {
                tag.style.boxShadow = '0 0 10px #0f0';
                setTimeout(() => tag.style.boxShadow = 'none', 300);
            });
        })
        .catch(err => {
            console.error('Fetch error:', err);
            document.getElementById('status-indicator').className = 'badge bg-warning text-dark';
            document.getElementById('status-indicator').innerText = 'Brak połączenia';
        });
}

// Odświeżanie co 3 sekundy
setInterval(updateSCADA, 3000);
updateSCADA(); // Pierwsze wywołanie od razu
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
