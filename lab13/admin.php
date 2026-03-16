<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab13_user_id']) || $_SESSION['lab13_login'] !== 'admin') {
    die("Brak uprawnień administratora.");
}

// 0. Obsługa odblokowania (usuwanie logów dla konkretnego loginu)
if (isset($_POST['unblock_login'])) {
    $login_to_unblock = $_POST['unblock_login'];
    $stmt_unblock = $conn->prepare("DELETE FROM logowanie WHERE login_attempted = ?");
    $stmt_unblock->execute([$login_to_unblock]);
    $msg = "Odblokowano użytkownika: " . htmlspecialchars($login_to_unblock);
}

// 1. Logi logowania (Historia)
$stmt_logs = $conn->query("SELECT * FROM logowanie ORDER BY datetime DESC LIMIT 100");
$logs = $stmt_logs->fetchAll();

// 2. Potencjalne blokady (Brute-Force)
// Szukamy loginów, których 3 ostatnie próby to porażki
$stmt_all_logins = $conn->query("SELECT DISTINCT login_attempted FROM logowanie");
$all_attempted_logins = $stmt_all_logins->fetchAll(PDO::FETCH_COLUMN);

$blocked_users = [];
foreach ($all_attempted_logins as $l_name) {
    $stmt_check = $conn->prepare("SELECT state FROM logowanie WHERE login_attempted = ? ORDER BY datetime DESC LIMIT 3");
    $stmt_check->execute([$l_name]);
    $attempts = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($attempts) === 3 && array_sum($attempts) === 0) {
        $blocked_users[] = $l_name;
    }
}

// 3. Wszystkie zadania w firmie
$stmt_all_tasks = $conn->query("
    SELECT z.*, pr.login as manager_login,
           (SELECT AVG(stan) FROM podzadanie WHERE idz = z.idz) as srednia_postepu
    FROM zadanie z
    JOIN pracownik pr ON z.idp = pr.idp
    ORDER BY z.idz DESC
");
$all_tasks = $stmt_all_tasks->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora - Lab 13</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.3); }
        .log-fail { color: #ff6666; font-weight: bold; }
        .log-success { color: #66ff66; font-weight: bold; }
        .blocked-badge { background: #dc3545; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.8rem; }
        .table-scroll { max-height: 400px; overflow-y: auto; }
        .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .bg-success-dot { background-color: #28a745; }
        .bg-danger-dot { background-color: #dc3545; }
        
        /* Poprawki widoczności tekstu */
        .text-secondary, .text-muted { color: #bbb !important; }
        .table thead th { color: #fff !important; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; }
        .table tbody td { color: #eee !important; }
        h2, h4 { color: #fff !important; }
        .btn-outline-secondary { color: #fff; border-color: #555; }
        .btn-outline-secondary:hover { background-color: #444; color: #fff; }
    </style>
</head>
<body class="bg-dark text-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">🛡️ Panel Administratora</h2>
            <small class="text-secondary">Zarządzanie systemem i bezpieczeństwem</small>
        </div>
        <a href="dashboard.php" class="btn btn-outline-secondary">Powrót do Dashboardu</a>
    </div>

    <?php if(isset($msg)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $msg; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- SEKCJA BEZPIECZEŃSTWA / BRUTE FORCE -->
        <div class="col-md-12">
            <div class="admin-card border-danger">
                <h4 class="text-danger mb-3">⚠️ Wykryte blokady Brute-Force</h4>
                <?php if (empty($blocked_users)): ?>
                    <p class="text-muted">Brak obecnie zablokowanych kont.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Zablokowany Login</th>
                                    <th>Status</th>
                                    <th>Akcja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($blocked_users as $bu): ?>
                                    <tr>
                                        <td class="fw-bold text-danger"><?php echo htmlspecialchars($bu); ?></td>
                                        <td><span class="blocked-badge">ZABLOKOWANY (3+ nieudane próby)</span></td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Czy na pewno chcesz odblokować tego użytkownika?');">
                                                <input type="hidden" name="unblock_login" value="<?php echo htmlspecialchars($bu); ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Odblokuj konto</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- HISTORIA LOGOWAŃ -->
        <div class="col-md-5">
            <div class="admin-card">
                <h4>📜 Historia logowań</h4>
                <div class="table-responsive table-scroll">
                    <table class="table table-dark table-sm table-hover" style="font-size: 0.85rem;">
                        <thead class="sticky-top bg-dark">
                            <tr>
                                <th>Użytkownik</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $l): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($l['login_attempted']); ?></td>
                                    <td class="text-secondary"><?php echo $l['datetime']; ?></td>
                                    <td>
                                        <span class="status-dot <?php echo $l['state'] ? 'bg-success-dot' : 'bg-danger-dot'; ?>"></span>
                                        <span class="<?php echo $l['state'] ? 'log-success' : 'log-fail'; ?>">
                                            <?php echo $l['state'] ? 'SUKCES' : 'PORAŻKA'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RAPORT POSTĘPÓW -->
        <div class="col-md-7">
            <div class="admin-card">
                <h4>📊 Wszystkie zadania w firmie</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Projekt</th>
                                <th>Manager</th>
                                <th>Postęp [%]</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_tasks as $t): 
                                $avg = $t['srednia_postepu'] !== null ? round($t['srednia_postepu']) : 0;
                                $r = floor(255 * (1 - $avg / 100));
                                $g = floor(255 * ($avg / 100));
                                $color = "rgb($r, $g, 0)";
                            ?>
                                <tr>
                                    <td class="fw-bold" style="color: <?php echo $color; ?>;"><?php echo htmlspecialchars($t['nazwa_zadania']); ?></td>
                                    <td><?php echo htmlspecialchars($t['manager_login']); ?></td>
                                    <td>
                                        <div class="progress bg-secondary" style="height: 20px;">
                                            <div class="progress-bar" role="progressbar" 
                                                 style="width: <?php echo $avg; ?>%; background-color: <?php echo $color; ?>; color: #000; font-weight: bold;" 
                                                 aria-valuenow="<?php echo $avg; ?>" aria-valuemin="0" aria-valuemax="100">
                                                <?php echo $avg; ?>%
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
