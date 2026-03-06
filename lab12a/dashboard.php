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
    <title>Lab 12a - Unified SCADA Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container { padding: 20px; }
        .card { margin-bottom: 20px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .scada-viz { position: relative; background: #000; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; }
        .sensor-tag {
            position: absolute;
            background: rgba(0, 0, 0, 0.85);
            color: #0f0;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: bold;
            font-size: 13px;
            border: 1px solid #0f03;
        }
        #scada-svg { width: 100%; height: auto; display: block; }
        .table-scroll { max-height: 400px; overflow-y: auto; }
        .form-control { background-color: #2c2c2c; border-color: #444; color: #fff; }
        .form-control:focus { background-color: #333; color: #fff; border-color: var(--accent-color); box-shadow: none; }
    </style>
</head>
<body class="bg-dark text-light">

<nav class="navbar navbar-dark bg-black mb-4 px-4 border-bottom border-secondary">
    <a class="navbar-brand" href="index.php">Lab 12a Dashboard</a>
    <div>
        <span class="text-secondary me-3">Zalogowano: <?php echo $_SESSION['lab12a_username']; ?></span>
        <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
    </div>
</nav>

<div class="container-fluid dashboard-container">
    <div class="row">
        <!-- Lewa kolumna: Formularz i SCADA -->
        <div class="col-lg-5">
            <!-- Formularz dodawania -->
            <div class="card p-3">
                <h5 class="card-title mb-3">Nowy Pomiar</h5>
                <form id="measure-form" class="row g-2">
                    <div class="col-4"><input type="number" step="0.1" name="x1" class="form-control" placeholder="x1" required></div>
                    <div class="col-4"><input type="number" step="0.1" name="x2" class="form-control" placeholder="x2" required></div>
                    <div class="col-4"><input type="number" step="0.1" name="x3" class="form-control" placeholder="x3" required></div>
                    <div class="col-4"><input type="number" step="0.1" name="x4" class="form-control" placeholder="x4" required></div>
                    <div class="col-4"><input type="number" step="0.1" name="x5" class="form-control" placeholder="x5" required></div>
                    <div class="col-4"><button type="submit" class="btn btn-primary w-100">Dodaj</button></div>
                </form>
                <div id="form-msg" class="mt-2 small" style="display:none"></div>
            </div>

            <!-- Wizualizacja SCADA -->
            <div class="scada-viz mb-3">
                <svg id="scada-svg" viewBox="0 0 800 400" xmlns="http://www.w3.org/2000/svg">
                    <rect x="50" y="50" width="700" height="300" fill="none" stroke="#444" stroke-width="2" />
                    <line x1="300" y1="50" x2="300" y2="350" stroke="#444" stroke-width="1.5" />
                    <line x1="550" y1="50" x2="550" y2="350" stroke="#444" stroke-width="1.5" />
                    <line x1="300" y1="200" x2="550" y2="200" stroke="#444" stroke-width="1.5" />
                    <text x="175" y="80" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="bold">Hala A</text>
                    <text x="425" y="80" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="bold">Magazyn</text>
                    <text x="425" y="230" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="bold">Biuro</text>
                    <text x="675" y="80" text-anchor="middle" fill="#FFFFFF" font-size="14" font-weight="bold">Hala B</text>
                </svg>
                <div id="s-x1" class="sensor-tag" style="top: 150px; left: 150px;">x1: --</div>
                <div id="s-x2" class="sensor-tag" style="top: 150px; left: 400px;">x2: --</div>
                <div id="s-x3" class="sensor-tag" style="top: 280px; left: 400px;">x3: --</div>
                <div id="s-x4" class="sensor-tag" style="top: 150px; left: 650px;">x4: --</div>
                <div id="s-x5" class="sensor-tag" style="top: 320px; left: 150px;">x5: --</div>
            </div>
            <div class="text-center small text-secondary">Status: <span id="status-live" class="badge bg-success">Live</span> | Aktualizacja: <span id="last-time">--</span></div>
        </div>

        <!-- Prawa kolumna: Wykres i Tabela -->
        <div class="col-lg-7">
            <!-- Wykres -->
            <div class="card p-3">
                <div style="height: 250px;"><canvas id="chart-live"></canvas></div>
            </div>

            <!-- Tabela -->
            <div class="card p-3">
                <h5 class="card-title">Historia Pomiarów (Ostatnie 20)</h5>
                <div class="table-scroll">
                    <table class="table table-dark table-sm table-hover mb-0">
                        <thead class="sticky-top bg-black">
                            <tr>
                                <th>Czas</th><th>x1</th><th>x2</th><th>x3</th><th>x4</th><th>x5</th>
                            </tr>
                        </thead>
                        <tbody id="table-body">
                            <!-- Dane ładowane przez JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Konfiguracja Chart.js
Chart.defaults.color = '#888';
Chart.defaults.borderColor = '#333';
const ctx = document.getElementById('chart-live').getContext('2d');
const liveChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            { label: 'x1', data: [], borderColor: '#ff6384', tension: 0.1, radius: 2 },
            { label: 'x2', data: [], borderColor: '#36a2eb', tension: 0.1, radius: 2 },
            { label: 'x3', data: [], borderColor: '#4bc0c2', tension: 0.1, radius: 2 },
            { label: 'x4', data: [], borderColor: '#9966ff', tension: 0.1, radius: 2 },
            { label: 'x5', data: [], borderColor: '#ff9f40', tension: 0.1, radius: 2 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 10 } } } },
        scales: { y: { beginAtZero: true, grid: { color: '#222' } }, x: { grid: { display: false } } }
    }
});

function refreshData() {
    fetch('get_history.php')
        .then(res => res.json())
        .then(data => {
            if (!data.length) return;
            
            // Aktualizacja wizualizacji SCADA (ostatni rekord)
            const latest = data[data.length - 1];
            ['x1','x2','x3','x4','x5'].forEach(x => {
                document.getElementById('s-'+x).innerText = x + ': ' + parseFloat(latest[x]).toFixed(2) + ' V';
            });
            document.getElementById('last-time').innerText = latest.datetime.split(' ')[1];

            // Aktualizacja Wykresu
            liveChart.data.labels = data.map(d => d.datetime.split(' ')[1]);
            ['x1','x2','x3','x4','x5'].forEach((x, i) => {
                liveChart.data.datasets[i].data = data.map(d => d[x]);
            });
            liveChart.update('none');

            // Aktualizacja Tabeli
            const rows = data.slice().reverse().map(d => `
                <tr>
                    <td>${d.datetime.split(' ')[1]}</td>
                    <td>${parseFloat(d.x1).toFixed(2)}</td>
                    <td>${parseFloat(d.x2).toFixed(2)}</td>
                    <td>${parseFloat(d.x3).toFixed(2)}</td>
                    <td>${parseFloat(d.x4).toFixed(2)}</td>
                    <td>${parseFloat(d.x5).toFixed(2)}</td>
                </tr>
            `).join('');
            document.getElementById('table-body').innerHTML = rows;
        });
}

// Obsługa formularza AJAX
document.getElementById('measure-form').onsubmit = function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('add.php', { method: 'POST', body: formData })
        .then(() => {
            this.reset();
            const msg = document.getElementById('form-msg');
            msg.innerText = "Dodano pomiar!";
            msg.className = "mt-2 small text-success";
            msg.style.display = "block";
            setTimeout(() => msg.style.display = "none", 2000);
            refreshData(); // Natychmiastowe odświeżenie
        });
};

setInterval(refreshData, 3000);
refreshData();
</script>
</body>
</html>
