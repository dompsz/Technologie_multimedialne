<?php
session_start();
if(!isset($_SESSION['lab12b_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 12b - Dashboard SCADA PRO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../style.css">
    <style>
        .dashboard-container { padding: 5px 15px; }
        .card { margin-bottom: 8px; border: 1px solid var(--border-color); box-shadow: 0 4px 15px rgba(0,0,0,0.8); background: var(--card-bg); padding: 10px !important; }
        .scada-viz { position: relative; background: #000; border: 1px solid var(--border-color); border-radius: 8px; overflow: hidden; height: 340px; width: 100%; }
        .sensor-tag { position: absolute; background: rgba(0, 0, 0, 0.95); color: #0f0; padding: 2px 6px; border-radius: 4px; font-family: monospace; font-weight: bold; font-size: 12px; border: 1px solid #0f06; transform: translate(-50%, -50%); z-index: 10; }
        .alarm-icon { position: absolute; font-size: 22px; transform: translate(-50%, -50%); display: none; z-index: 15; pointer-events: none; }
        #fan-container { position: absolute; width: 45px; height: 45px; transform: translate(-50%, -50%); z-index: 12; }
        .fan-svg { width: 100%; height: 100%; fill: #4dabff; }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .spin-fast { animation: spin 0.3s linear infinite; }
        .spin-medium { animation: spin 0.8s linear infinite; }
        .spin-slow { animation: spin 2s linear infinite; }
        #scada-svg { width: 100%; height: 100%; display: block; }
        .table-scroll { max-height: 235px; overflow-y: auto; }
        .form-label-sm { font-size: 0.8rem; font-weight: bold; color: #fff; margin-bottom: 4px; }
        .form-check-input { width: 2.5em; height: 1.25em; border: 2px solid #666; background-color: #222; }
        .form-check-input:checked { background-color: #ff0000; border-color: #ff4444; }
        
        /* Przycisk zapisu dopasowany do wysokości inputów sm */
        .btn-save { 
            height: calc(1.5em + 0.5rem + 2px); 
            background-color: var(--accent-color) !important; 
            color: #000 !important; 
            font-weight: bold; 
            border: none;
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-save:hover { background-color: var(--accent-hover) !important; }
    </style>
</head>
<body class="bg-dark text-light">

<nav class="bg-black mb-1 px-4 border-bottom border-secondary d-flex align-items-center justify-content-between" style="padding: 8px 24px;">
    <div class="d-flex align-items-center">
        <a href="../index.php" class="btn btn-outline-secondary py-1 px-3" style="font-size: 0.9rem; line-height: 1; margin: 0; white-space: nowrap;">← POWRÓT</a>
        <span class="fw-bold text-white ms-3" style="font-size: 0.9rem; line-height: 1; margin: 0; white-space: nowrap; display: inline-block;">Lab 12b</span>
    </div>
    <div class="d-flex align-items-center">
        <a href="logout.php" class="btn btn-outline-danger py-1 px-3" style="font-size: 0.9rem; line-height: 1; margin: 0; white-space: nowrap;">Wyloguj</a>
    </div>
</nav>

<div class="container-fluid dashboard-container">
    <div class="row">
        <div class="col-lg-5">
            <div class="card">
                <form id="measure-form">
                    <div class="row g-1 mb-2 align-items-end">
                        <div class="col-2"><label class="form-label-sm">v0</label><input type="number" step="0.1" name="v0" class="form-control form-control-sm" required></div>
                        <div class="col-2"><label class="form-label-sm">v1</label><input type="number" step="0.1" name="v1" class="form-control form-control-sm" required></div>
                        <div class="col-2"><label class="form-label-sm">v2</label><input type="number" step="0.1" name="v2" class="form-control form-control-sm" required></div>
                        <div class="col-2"><label class="form-label-sm">v3</label><input type="number" step="0.1" name="v3" class="form-control form-control-sm" required></div>
                        <div class="col-2"><label class="form-label-sm">v4</label><input type="number" step="0.1" name="v4" class="form-control form-control-sm" required></div>
                        <div class="col-2"><label class="form-label-sm">v5</label><input type="number" step="0.1" name="v5" class="form-control form-control-sm" required></div>
                    </div>
                    <div class="row g-2 small mt-1">
                        <div class="col-3"><label class="form-label-sm d-block">Terrorysta</label><input class="form-check-input" type="checkbox" name="terrorysta"></div>
                        <div class="col-3"><label class="form-label-sm">Pożar</label><select name="pozar" class="form-select form-select-sm bg-dark text-white"><option value="brak">Brak</option><option value="Hala A">Hala A</option><option value="Magazyn">Magazyn</option><option value="Biuro">Biuro</option><option value="Hala B">Hala B</option><option value="Serwerownia">Serwerownia</option></select></div>
                        <div class="col-3"><label class="form-label-sm">Powódź</label><select name="powodz" class="form-select form-select-sm bg-dark text-white"><option value="brak">Brak</option><option value="Hala A">Hala A</option><option value="Magazyn">Magazyn</option><option value="Biuro">Biuro</option><option value="Hala B">Hala B</option><option value="Serwerownia">Serwerownia</option></select></div>
                        <div class="col-3"><label class="form-label-sm">Wiatrak</label><select name="wiatrak" class="form-select form-select-sm bg-dark text-white"><option value="wyłączony">OFF</option><option value="słabo">Słabo</option><option value="średnio">Średnio</option><option value="szybko">Szybko</option></select></div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100 btn-save">ZAPISZ POMIAR</button>
                    </div>
                </form>
                <div id="form-msg-container" class="text-center"><div id="form-msg" class="small fw-bold" style="display:none"></div></div>
            </div>

            <div class="scada-viz mb-1">
                <svg id="scada-svg" viewBox="0 0 800 400" xmlns="http://www.w3.org/2000/svg">
                    <rect x="50" y="50" width="700" height="300" fill="none" stroke="#888" stroke-width="3" />
                    <line x1="250" y1="50" x2="250" y2="350" stroke="#888" stroke-width="2" />
                    <line x1="500" y1="50" x2="500" y2="350" stroke="#888" stroke-width="2" />
                    <line x1="250" y1="200" x2="500" y2="200" stroke="#888" stroke-width="2" />
                    <line x1="500" y1="200" x2="750" y2="200" stroke="#888" stroke-width="2" />
                    <path d="M 330 50 A 30 30 0 0 0 300 20" fill="none" stroke="#4dabff" stroke-width="2" /><line x1="300" y1="50" x2="330" y2="50" stroke="#4dabff" stroke-width="2" /><line x1="300" y1="50" x2="300" y2="20" stroke="#4dabff" stroke-width="2" />
                    <path d="M 250 120 A 30 30 0 0 0 220 150" fill="none" stroke="#4dabff" stroke-width="2" /><line x1="250" y1="150" x2="250" y2="120" stroke="#4dabff" stroke-width="2" /><line x1="250" y1="150" x2="220" y2="150" stroke="#4dabff" stroke-width="2" />
                    <path d="M 500 120 A 30 30 0 0 1 530 150" fill="none" stroke="#4dabff" stroke-width="2" /><line x1="500" y1="150" x2="500" y2="120" stroke="#4dabff" stroke-width="2" /><line x1="500" y1="150" x2="530" y2="150" stroke="#4dabff" stroke-width="2" />
                    <path d="M 330 200 A 30 30 0 0 1 300 230" fill="none" stroke="#4dabff" stroke-width="2" /><line x1="300" y1="200" x2="330" y2="200" stroke="#4dabff" stroke-width="2" /><line x1="300" y1="200" x2="300" y2="230" stroke="#4dabff" stroke-width="2" />
                    <path d="M 500 240 A 30 30 0 0 0 530 210" fill="none" stroke="#4dabff" stroke-width="2" /><line x1="500" y1="210" x2="500" y2="240" stroke="#4dabff" stroke-width="2" /><line x1="500" y1="210" x2="530" y2="210" stroke="#4dabff" stroke-width="2" />
                    <text x="150" y="80" text-anchor="middle" fill="#FFF" font-size="12" font-weight="bold">Hala Produkcyjna A</text>
                    <text x="375" y="80" text-anchor="middle" fill="#FFF" font-size="12" font-weight="bold">Magazyn</text>
                    <text x="375" y="230" text-anchor="middle" fill="#FFF" font-size="12" font-weight="bold">Biuro</text>
                    <text x="625" y="80" text-anchor="middle" fill="#FFF" font-size="12" font-weight="bold">Hala Produkcyjna B</text>
                    <text x="625" y="230" text-anchor="middle" fill="#FFF" font-size="12" font-weight="bold">Serwerownia</text>
                </svg>
                <div id="s-v0" class="sensor-tag">v0: --</div><div id="s-v1" class="sensor-tag">v1: --</div><div id="s-v2" class="sensor-tag">v2: --</div><div id="s-v3" class="sensor-tag">v3: --</div><div id="s-v4" class="sensor-tag">v4: --</div><div id="s-v5" class="sensor-tag">v5: --</div>
                <div id="icon-terror" class="alarm-icon">💣</div><div id="icon-fire" class="alarm-icon">🔥</div><div id="icon-flood" class="alarm-icon">🌊</div>
                <div id="fan-container"><svg class="fan-svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="5" fill="#FFF"/><path d="M50 50 L50 10 A15 15 0 0 1 65 25 Z M50 50 L90 50 A15 15 0 0 1 75 65 Z M50 50 L50 90 A15 15 0 0 1 35 75 Z M50 50 L10 50 A15 15 0 0 1 25 35 Z" /></svg></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card"><div style="height: 230px;"><canvas id="chart-live"></canvas></div></div>
            <div class="card"><div class="table-scroll"><table class="table table-dark table-sm table-hover mb-0" style="font-size: 0.85rem;"><thead class="sticky-top bg-black"><tr><th>Czas</th><th>v0</th><th>v1</th><th>v2</th><th>v3</th><th>v4</th><th>v5</th><th>Status</th></tr></thead><tbody id="table-body"></tbody></table></div></div>
        </div>
    </div>
</div>

<script>
const roomPos = {
    'Hala A': {left: '18.7%', top: '50%'},
    'Magazyn': {left: '46.8%', top: '31.2%'},
    'Biuro': {left: '46.8%', top: 'calc(68.7% - 4px)'},
    'Hala B': {left: '78.1%', top: '31.2%'},
    'Serwerownia': {left: '78.1%', top: 'calc(68.7% - 4px)'},
    'Hala A_2': {left: '18.7%', top: '75%'} // Dodatkowy punkt dla v5
};

Chart.defaults.color = '#ccc';
const liveChart = new Chart(document.getElementById('chart-live').getContext('2d'), {
    type: 'line',
    data: {
        labels: [],
        datasets: ['v0','v1','v2','v3','v4','v5'].map((l, i) => ({
            label: l, data: [], borderColor: ['#ff6384','#36a2eb','#4bc0c2','#9966ff','#ff9f40','#2ecc71'][i],
            tension: 0.2, radius: 4, pointStyle: 'rect', backgroundColor: ['#ff6384','#36a2eb','#4bc0c2','#9966ff','#ff9f40','#2ecc71'][i], fill: false
        }))
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        animation: {
            duration: 500,
            easing: 'easeInOutQuad'
        },
        interaction: { mode: 'index', intersect: false },
        plugins: { legend: { labels: { usePointStyle: true, boxWidth: 10 } }, tooltip: { yAlign: 'bottom', usePointStyle: true } },
        scales: { y: { grid: { color: '#222' } }, x: { grid: { display: false } } }
    }
});

function refreshData() {
    fetch('get_history.php').then(res => res.json()).then(data => {
        if (!data.length) return;
        const latest = data[data.length - 1];
        ['v0','v1','v2','v3','v4','v5'].forEach((v, i) => {
            const el = document.getElementById('s-'+v);
            if(el) {
                el.innerText = v+': '+parseFloat(latest[v]).toFixed(2);
                const rooms = Object.keys(roomPos);
                const pos = roomPos[rooms[i]];
                el.style.left = pos.left; el.style.top = pos.top;
            }
        });
        const tIcon = document.getElementById('icon-terror');
        tIcon.style.display = latest.terrorysta == 1 ? 'block' : 'none';
        tIcon.style.left = '78.1%'; tIcon.style.top = '75%';
        const fIcon = document.getElementById('icon-fire');
        if(latest.pozar !== 'brak') {
            fIcon.style.display = 'block'; 
            fIcon.style.left = (parseFloat(roomPos[latest.pozar].left)-10)+'%'; 
            fIcon.style.top = roomPos[latest.pozar].top;
        } else fIcon.style.display = 'none';
        const wIcon = document.getElementById('icon-flood');
        if(latest.powodz !== 'brak') {
            wIcon.style.display = 'block'; 
            wIcon.style.left = (parseFloat(roomPos[latest.powodz].left)+10)+'%'; 
            wIcon.style.top = roomPos[latest.powodz].top;
        } else wIcon.style.display = 'none';
        const fc = document.getElementById('fan-container');
        fc.style.left = '46.8%'; fc.style.top = '75%';
        document.querySelector('.fan-svg').className.baseVal = 'fan-svg ' + (latest.wiatrak === 'szybko' ? 'spin-fast' : latest.wiatrak === 'średnio' ? 'spin-medium' : latest.wiatrak === 'słabo' ? 'spin-slow' : '');
        liveChart.data.labels = data.map(d => d.datetime.split(' ')[1]);
        ['v0','v1','v2','v3','v4','v5'].forEach((v, i) => liveChart.data.datasets[i].data = data.map(d => d[v]));
        liveChart.update();
        document.getElementById('table-body').innerHTML = data.slice().reverse().map(d => `
            <tr><td class="text-primary fw-bold">${d.datetime.split(' ')[1]}</td><td>${parseFloat(d.v0).toFixed(2)}</td><td>${parseFloat(d.v1).toFixed(2)}</td><td>${parseFloat(d.v2).toFixed(2)}</td><td>${parseFloat(d.v3).toFixed(2)}</td><td>${parseFloat(d.v4).toFixed(2)}</td><td>${parseFloat(d.v5).toFixed(2)}</td><td>${d.terrorysta==1?'💣':''}${d.pozar!='brak'?'🔥':''}${d.powodz!='brak'?'🌊':''}${d.wiatrak!='wyłączony'?'⚙️':''}</td></tr>
        `).join('');
    });
}

document.getElementById('measure-form').onsubmit = function(e) {
    e.preventDefault();
    fetch('add.php', { method: 'POST', body: new FormData(this) }).then(() => {
        document.getElementById('measure-form').reset();
        const msg = document.getElementById('form-msg');
        msg.innerText = "✓ ZAPISANO"; msg.className = "text-success"; msg.style.display = "block";
        setTimeout(() => msg.style.display = "none", 2000);
        refreshData();
    });
};
setInterval(refreshData, 3000);
refreshData();
</script>
</body>
</html>
