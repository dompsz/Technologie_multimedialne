<?php
session_start();
require_once 'db_config.php';

// Dostęp dla każdego pracownika (pracownik lub admin)
if (!isset($_SESSION['lab15_user_id']) || $_SESSION['lab15_role'] === 'client') {
    die("Brak uprawnień. Ten panel jest dostępny tylko dla pracowników.");
}

$user_id = $_SESSION['lab15_user_id'];
$user_role = $_SESSION['lab15_role'];

// --- OBSŁUGA AKCJI ADMINISTRACYJNYCH ---

// 1. Odblokowywanie kont (brute-force)
if (isset($_POST['unblock_client'])) {
    $login = $_POST['unblock_client'];
    $stmt = $conn->prepare("DELETE FROM logi_klientow WHERE login_attempted = ? AND stan = 0");
    $stmt->execute([$login]);
}
if (isset($_POST['unblock_employee'])) {
    $login = $_POST['unblock_employee'];
    $stmt = $conn->prepare("DELETE FROM logi_pracownikow WHERE login_attempted = ? AND stan = 0");
    $stmt->execute([$login]);
}

// 2. Zarządzanie kategoriami (Zagadnienia)
if (isset($_POST['add_category'])) {
    $nazwa = trim($_POST['cat_name']);
    if (!empty($nazwa)) {
        $stmt = $conn->prepare("INSERT INTO zagadnienia (nazwa) VALUES (?)");
        $stmt->execute([$nazwa]);
    }
}
if (isset($_POST['delete_category'])) {
    $idz = (int)$_POST['delete_category'];
    $stmt = $conn->prepare("DELETE FROM zagadnienia WHERE idz = ?");
    $stmt->execute([$idz]);
}

// --- POBIERANIE DANYCH ---

// 1. Statystyki wydajności (wszystkich dla admina, tylko własne dla pracownika - lub wszystkich jeśli "pracownik to admin")
// Skoro pracownik ma być jak admin, pokazujemy wszystko.
$stmt_stats = $conn->query("
    SELECT p.idp, p.nazwisko, p.role,
           (SELECT COUNT(*) FROM odpowiedzi o WHERE o.idp = p.idp) as liczba_odpowiedzi,
           (SELECT AVG(po.ocena_pracownika) FROM posty po 
            WHERE po.idpo IN (SELECT o2.idpo FROM odpowiedzi o2 WHERE o2.idp = p.idp)
            AND po.ocena_pracownika IS NOT NULL) as srednia_ocen
    FROM pracownicy p
    ORDER BY liczba_odpowiedzi DESC
");
$stats = $stmt_stats->fetchAll();

// Średnia zespołu
$total_replies = 0;
foreach ($stats as $s) $total_replies += $s['liczba_odpowiedzi'];
$team_avg = count($stats) > 0 ? $total_replies / count($stats) : 0;

function getEfficiencySymbol($replies, $avg) {
    if ($avg == 0) return '👤';
    $ratio = $replies / $avg;
    if ($ratio > 1.5) return '🐆 (Puma)';
    if ($ratio > 0.8) return '👤 (Człowiek)';
    if ($ratio > 0.4) return '🐢 (Żółw)';
    return '🐌 (Ślimak)';
}

// 2. Wykrywanie blokad (Brute-Force)
function getBlockedUsers($conn, $table) {
    $stmt = $conn->query("SELECT DISTINCT login_attempted FROM $table");
    $logins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $blocked = [];
    foreach ($logins as $l) {
        $stmt_check = $conn->prepare("SELECT stan FROM $table WHERE login_attempted = ? ORDER BY datagodzina DESC LIMIT 3");
        $stmt_check->execute([$l]);
        $atts = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
        if (count($atts) === 3 && array_sum($atts) === 0) $blocked[] = $l;
    }
    return $blocked;
}
$blocked_k = getBlockedUsers($conn, 'logi_klientow');
$blocked_p = getBlockedUsers($conn, 'logi_pracownikow');

// 3. Logi systemowe (ostatnie 50)
$stmt_log_k = $conn->query("SELECT * FROM logi_klientow ORDER BY datagodzina DESC LIMIT 25");
$logi_k = $stmt_log_k->fetchAll();

$stmt_log_p = $conn->query("SELECT * FROM logi_pracownikow ORDER BY datagodzina DESC LIMIT 25");
$logi_p = $stmt_log_p->fetchAll();

// 4. Kategorie
$stmt_cat = $conn->query("SELECT * FROM zagadnienia ORDER BY nazwa ASC");
$categories = $stmt_cat->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administracyjny CRM - Lab 15</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 20px; }
        .card-header { background: rgba(255,255,255,0.05); border-bottom: 1px solid var(--border-color); font-weight: bold; }
        .status-fail { color: #ff6b6b; }
        .status-success { color: #51cf66; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-warning mb-4">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="dashboard.php">🛡️ CRM ADMIN PANEL</a>
            <div class="d-flex gap-2">
                <span class="navbar-text me-2 small text-secondary">Zalogowany jako: <strong><?php echo $_SESSION['lab15_username']; ?></strong></span>
                <a href="dashboard.php" class="btn btn-outline-light btn-sm">Powrót do Dashboardu</a>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <!-- 1. BLOKADY BRUTE-FORCE -->
            <?php if (!empty($blocked_k) || !empty($blocked_p)): ?>
            <div class="col-12 mb-4">
                <div class="admin-card border-danger">
                    <div class="card-header text-danger">⚠️ Wykryte blokady kont (Brute-Force)</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Klienci:</h6>
                                <?php if(empty($blocked_k)): ?><p class="text-muted small">Brak</p><?php endif; ?>
                                <?php foreach($blocked_k as $bk): ?>
                                    <form method="POST" class="d-inline-block me-2 mb-2">
                                        <input type="hidden" name="unblock_client" value="<?php echo htmlspecialchars($bk); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Odblokuj: <?php echo htmlspecialchars($bk); ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-md-6">
                                <h6>Pracownicy:</h6>
                                <?php if(empty($blocked_p)): ?><p class="text-muted small">Brak</p><?php endif; ?>
                                <?php foreach($blocked_p as $bp): ?>
                                    <form method="POST" class="d-inline-block me-2 mb-2">
                                        <input type="hidden" name="unblock_employee" value="<?php echo htmlspecialchars($bp); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Odblokuj: <?php echo htmlspecialchars($bp); ?></button>
                                    </form>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. STATYSTYKI WYDAJNOŚCI -->
            <div class="col-lg-8 mb-4">
                <div class="admin-card h-100">
                    <div class="card-header">📊 Wydajność Zespołu</div>
                    <div class="card-body">
                        <table class="table table-dark table-striped table-hover align-middle small">
                            <thead>
                                <tr>
                                    <th>Pracownik</th>
                                    <th>Rola</th>
                                    <th>Odpowiedzi</th>
                                    <th>Średnia ⭐</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats as $s): ?>
                                    <tr <?php echo $s['idp'] == $user_id ? 'class="table-active"' : ''; ?>>
                                        <td class="fw-bold"><?php echo htmlspecialchars($s['nazwisko']); ?></td>
                                        <td><span class="badge <?php echo $s['role']=='admin'?'bg-warning text-dark':'bg-secondary'; ?>"><?php echo $s['role']; ?></span></td>
                                        <td><?php echo $s['liczba_odpowiedzi']; ?></td>
                                        <td><?php echo $s['srednia_ocen'] ? round($s['srednia_ocen'], 2) : '---'; ?></td>
                                        <td><?php echo getEfficiencySymbol($s['liczba_odpowiedzi'], $team_avg); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 3. ZARZĄDZANIE KATEGORIAMI -->
            <div class="col-lg-4 mb-4">
                <div class="admin-card h-100">
                    <div class="card-header">🏷️ Kategorie (Zagadnienia)</div>
                    <div class="card-body">
                        <form method="POST" class="mb-3 d-flex gap-2">
                            <input type="text" name="cat_name" class="form-control form-control-sm bg-dark text-light" placeholder="Nowa kategoria..." required>
                            <button type="submit" name="add_category" class="btn btn-sm btn-success">Dodaj</button>
                        </form>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <ul class="list-group list-group-flush bg-dark">
                                <?php foreach($categories as $cat): ?>
                                    <li class="list-group-item bg-dark text-light border-secondary d-flex justify-content-between align-items-center py-1">
                                        <small><?php echo htmlspecialchars($cat['nazwa']); ?></small>
                                        <form method="POST" onsubmit="return confirm('Usunąć tę kategorię?')">
                                            <input type="hidden" name="delete_category" value="<?php echo $cat['idz']; ?>">
                                            <button type="submit" class="btn btn-link btn-sm text-danger p-0">usuń</button>
                                        </form>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. LOGI SYSTEMOWE -->
            <div class="col-md-6 mb-4">
                <div class="admin-card">
                    <div class="card-header">🕵️ Logi Klientów</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Login</th>
                                        <th>Status</th>
                                        <th>IP / System</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($logi_k as $l): ?>
                                        <tr>
                                            <td class="text-secondary"><?php echo $l['datagodzina']; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($l['login_attempted']); ?></td>
                                            <td><span class="<?php echo $l['stan'] ? 'status-success' : 'status-fail'; ?>"><?php echo $l['stan'] ? 'OK' : 'FAIL'; ?></span></td>
                                            <td><?php echo $l['ip_address']; ?> (<?php echo $l['system']; ?>)</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="admin-card">
                    <div class="card-header">🔑 Logi Pracowników</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-dark table-sm mb-0" style="font-size: 0.75rem;">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Login</th>
                                        <th>Status</th>
                                        <th>IP / System</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($logi_p as $l): ?>
                                        <tr>
                                            <td class="text-secondary"><?php echo $l['datagodzina']; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($l['login_attempted']); ?></td>
                                            <td><span class="<?php echo $l['stan'] ? 'status-success' : 'status-fail'; ?>"><?php echo $l['stan'] ? 'OK' : 'FAIL'; ?></span></td>
                                            <td><?php echo $l['ip_address']; ?> (<?php echo $l['system']; ?>)</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</body>
</html>
