<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab13_user_id']) || $_SESSION['lab13_login'] !== 'admin') {
    die("Brak uprawnień administratora.");
}

// 1. Logi logowania
$stmt_logs = $conn->query("SELECT * FROM logowanie ORDER BY datetime DESC LIMIT 50");
$logs = $stmt_logs->fetchAll();

// 2. Wszystkie zadania w firmie
$stmt_all_tasks = $conn->query("
    SELECT z.*, pr.login as manager_login,
           (SELECT AVG(stan) FROM podzadanie WHERE idz = z.idz) as srednia_postepu
    FROM zadanie z
    JOIN pracownik pr ON z.idp = pr.idp
    ORDER BY z.idz DESC
");
$all_tasks = $stmt_all_tasks->fetchAll();

function getProgressColor($percent) {
    if ($percent == 0) return '#ff4444';
    if ($percent == 100) return '#00ff00';
    return '#ffffff';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora - Lab 13</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .log-fail { color: #ff4444; }
        .log-success { color: #00ff00; }
    </style>
</head>
<body class="bg-dark text-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-4">
        <h2>Panel Administratora</h2>
        <a href="dashboard.php" class="btn btn-secondary">Powrót do Dashboardu</a>
    </div>

    <div class="row">
        <!-- RAPORT POSTĘPÓW -->
        <div class="col-md-7">
            <div class="admin-card">
                <h4>Wszystkie zadania w firmie</h4>
                <div class="table-responsive">
                    <table class="table table-dark table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Projekt</th>
                                <th>Manager</th>
                                <th>Postęp</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($all_tasks as $t): 
                                $avg = $t['srednia_postepu'] !== null ? round($t['srednia_postepu']) : 0;
                            ?>
                                <tr>
                                    <td style="color: <?php echo getProgressColor($avg); ?>"><?php echo htmlspecialchars($t['nazwa_zadania']); ?></td>
                                    <td><?php echo htmlspecialchars($t['manager_login']); ?></td>
                                    <td><strong><?php echo $avg; ?>%</strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- LOGI -->
        <div class="col-md-5">
            <div class="admin-card">
                <h4>Ostatnie logowania</h4>
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-dark table-sm" style="font-size: 0.85rem;">
                        <thead>
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
                                    <td><?php echo $l['datetime']; ?></td>
                                    <td class="<?php echo $l['state'] ? 'log-success' : 'log-fail'; ?>">
                                        <?php echo $l['state'] ? 'OK' : 'FAIL'; ?>
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
</body>
</html>
