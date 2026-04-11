<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id']) || $_SESSION['lab16_role'] !== 'admin') {
    header("Location: dashboard.php?error=admin_only");
    exit();
}

// --- OBSŁUGA AKCJI ---

// 1. Odblokowywanie kont (Brute-Force)
if (isset($_POST['unblock_user'])) {
    $login = $_POST['unblock_user'];
    $stmt = $conn->prepare("DELETE FROM logi_logowania WHERE login_attempted = ? AND stan = 0");
    $stmt->execute([$login]);
}

// 2. Zarządzanie Kategoriami
if(isset($_POST['add_category'])) {
    $nazwa = trim($_POST['cat_name']);
    $slug = strtolower(str_replace(' ', '-', $nazwa));
    if(!empty($nazwa)) {
        $stmt = $conn->prepare("INSERT INTO kategorie (nazwa, slug) VALUES (?, ?)");
        $stmt->execute([$nazwa, $slug]);
    }
}

// 3. Zarządzanie Słownikiem Bota
if(isset($_POST['add_dict'])) {
    $klucz = trim($_POST['dict_key']);
    $odp = trim($_POST['dict_val']);
    if(!empty($klucz) && !empty($odp)) {
        $stmt = $conn->prepare("INSERT INTO slownik_bota (pytanie_klucz, odpowiedz) VALUES (?, ?)");
        $stmt->execute([$klucz, $odp]);
    }
}

// --- POBIERANIE DANYCH ---

// Pobranie użytkowników
$users = $conn->query("SELECT idu, nazwa_uzytkownika, rola, data_utworzenia FROM uzytkownicy ORDER BY data_utworzenia DESC")->fetchAll();

// Wykrywanie blokad Brute-Force
function getBlockedUsers($conn) {
    $stmt = $conn->query("SELECT DISTINCT login_attempted FROM logi_logowania");
    $logins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $blocked = [];
    foreach ($logins as $l) {
        $stmt_check = $conn->prepare("SELECT stan FROM logi_logowania WHERE login_attempted = ? ORDER BY datagodzina DESC LIMIT 3");
        $stmt_check->execute([$l]);
        $atts = $stmt_check->fetchAll(PDO::FETCH_COLUMN);
        if (count($atts) === 3 && array_sum($atts) === 0) $blocked[] = $l;
    }
    return $blocked;
}
$blocked_users = getBlockedUsers($conn);

// Kategorie, Słownik, Logi
$categories = $conn->query("SELECT * FROM kategorie ORDER BY nazwa ASC")->fetchAll();
$dictionary = $conn->query("SELECT * FROM slownik_bota ORDER BY ids DESC")->fetchAll();
$login_logs = $conn->query("SELECT l.*, u.nazwa_uzytkownika FROM logi_logowania l LEFT JOIN uzytkownicy u ON l.idu = u.idu ORDER BY l.datagodzina DESC LIMIT 20")->fetchAll();
$bot_logs = $conn->query("SELECT * FROM logi_bota ORDER BY data_godzina DESC LIMIT 20")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>🛡️ Admin Panel - CMS Lab 16</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #0a0a0a; color: #eee; }
        .admin-card { background: #161616; border: 1px solid #333; border-radius: 12px; margin-bottom: 25px; overflow: hidden; }
        .card-header { background: rgba(255,193,7, 0.1); border-bottom: 1px solid #444; color: #ffc107; font-weight: bold; padding: 12px 20px; }
        .table { color: #ccc; font-size: 0.9rem; }
        .status-success { color: #2ecc71; }
        .status-fail { color: #e74c3c; }
        .efficiency-badge { font-size: 0.75rem; padding: 4px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-black border-bottom border-warning mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-warning fw-bold" href="dashboard.php">🛡️ CMS SECURITY & ADMIN</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Powrót do treści</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <!-- 1. BLOKADY BRUTE-FORCE -->
            <?php if (!empty($blocked_users)): ?>
            <div class="col-12 mb-4">
                <div class="admin-card border-danger">
                    <div class="card-header text-danger">⚠️ Wykryte blokady kont (Brute-Force)</div>
                    <div class="card-body p-3">
                        <?php foreach($blocked_users as $bu): ?>
                            <form method="POST" class="d-inline-block me-2 mb-2">
                                <input type="hidden" name="unblock_user" value="<?php echo htmlspecialchars($bu); ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">Odblokuj: <?php echo htmlspecialchars($bu); ?></button>
                            </form>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- 2. UŻYTKOWNICY I LOGI -->
            <div class="col-lg-8">
                <div class="admin-card">
                    <div class="card-header">👥 Zarejestrowani Użytkownicy</div>
                    <div class="card-body p-0">
                        <table class="table table-dark table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nazwa</th>
                                    <th>Rola</th>
                                    <th>Data utworzenia</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><?php echo $u['idu']; ?></td>
                                    <td class="fw-bold"><?php echo htmlspecialchars($u['nazwa_uzytkownika']); ?></td>
                                    <td>
                                        <span class="badge <?php echo $u['rola'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>">
                                            <?php echo $u['rola']; ?>
                                        </span>
                                    </td>
                                    <td class="text-secondary"><?php echo $u['data_utworzenia']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header">🔑 Logi systemowe (Ostatnie logowania)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 300px;">
                            <table class="table table-sm table-dark mb-0">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Login</th>
                                        <th>Status</th>
                                        <th>IP / System</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($login_logs as $l): ?>
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

            <!-- 3. KATEGORIE I SŁOWNIK -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">🏷️ Kategorie</div>
                    <div class="card-body">
                        <form method="POST" class="d-flex gap-2 mb-3">
                            <input type="text" name="cat_name" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Nowa kategoria" required>
                            <button type="submit" name="add_category" class="btn btn-sm btn-success">Dodaj</button>
                        </form>
                        <ul class="list-group list-group-flush">
                            <?php foreach($categories as $cat): ?>
                                <li class="list-group-item bg-transparent text-white-50 border-secondary d-flex justify-content-between py-1 px-0">
                                    <small><?php echo htmlspecialchars($cat['nazwa']); ?></small>
                                    <small class="text-secondary">/<?php echo $cat['slug']; ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header">🤖 Słownik Bota</div>
                    <div class="card-body">
                        <form method="POST" class="mb-3">
                            <input type="text" name="dict_key" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" placeholder="Słowa kluczowe..." required>
                            <textarea name="dict_val" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" placeholder="Odpowiedź bota..." required></textarea>
                            <button type="submit" name="add_dict" class="btn btn-sm btn-primary w-100">Dodaj do bazy wiedzy</button>
                        </form>
                    </div>
                </div>

                <div class="admin-card">
                    <div class="card-header">🕵️ Ostatnie zapytania bota</div>
                    <div class="card-body p-0">
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-dark mb-0">
                                <thead>
                                    <tr>
                                        <th>Zapytanie</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($bot_logs as $bl): ?>
                                    <tr>
                                        <td style="font-size: 0.8rem;"><?php echo htmlspecialchars($bl['zapytanie_uzytkownika']); ?></td>
                                        <td>
                                            <?php if($bl['czy_znaleziono_odp']): ?>
                                                <span class="text-success small">Trafiony</span>
                                            <?php else: ?>
                                                <span class="text-warning small">Brak</span>
                                            <?php endif; ?>
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
    </div>
</body>
</html>
