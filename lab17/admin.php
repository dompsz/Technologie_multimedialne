<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Zabezpieczenie - tylko dla moderatorów i adminów (poziom >= 2)
if (!isset($_SESSION['lab17_user_id']) || $_SESSION['lab17_role'] < 2) {
    die("Brak uprawnień do panelu moderacji.");
}

$user_role = $_SESSION['lab17_role'];

// --- OBSŁUGA AKCJI ---

// 1. Zarządzanie tematami (tylko admin lub moderator)
if (isset($_POST['add_topic'])) {
    $nazwa = trim($_POST['nazwa_tematu']);
    $opis = trim($_POST['opis']);
    if (!empty($nazwa)) {
        $stmt = $conn->prepare("INSERT INTO tematy (nazwa_tematu, opis) VALUES (?, ?)");
        $stmt->execute([$nazwa, $opis]);
    }
}

// 2. Zarządzanie cenzurą
if (isset($_POST['add_censure'])) {
    $slowo = trim($_POST['slowo']);
    $zamiennik = trim($_POST['zamiennik']);
    if (!empty($slowo)) {
        $stmt = $conn->prepare("INSERT INTO cenzura (slowo_zakazane, zamiennik) VALUES (?, ?)");
        $stmt->execute([$slowo, $zamiennik ?: '***']);
    }
}

// 3. Usuwanie/Blokowanie postów
if (isset($_GET['block_post'])) {
    $idw = (int)$_GET['block_post'];
    $stmt = $conn->prepare("UPDATE watki SET stan = 0 WHERE idw = ?");
    $stmt->execute([$idw]);
}

// 4. Zmiana uprawnień (tylko Admin)
if (isset($_POST['change_role']) && $user_role == 3) {
    $idu = (int)$_POST['user_id'];
    $new_role = (int)$_POST['new_role'];
    $stmt = $conn->prepare("UPDATE uzytkownicy SET poziom_uprawnien = ? WHERE idu = ?");
    $stmt->execute([$new_role, $idu]);
}

// --- POBIERANIE DANYCH ---
$tematy = $conn->query("SELECT * FROM tematy")->fetchAll();
$cenzura = $conn->query("SELECT * FROM cenzura")->fetchAll();
$users = $conn->query("SELECT * FROM uzytkownicy ORDER BY poziom_uprawnien DESC")->fetchAll();

// Ostatnie posty do moderacji
$stmt_posts = $conn->query("
    SELECT w.*, u.login, t.nazwa_tematu 
    FROM watki w
    JOIN uzytkownicy u ON w.idu = u.idu
    JOIN tematy t ON w.idt = t.idt
    ORDER BY w.datagodzina DESC LIMIT 20
");
$recent_posts = $stmt_posts->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Moderacji - Forum Lab 17</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 25px; overflow: hidden; }
        .card-header { background: rgba(255,193,7, 0.1); border-bottom: 1px solid #444; color: #ffc107; font-weight: bold; padding: 12px 20px; }
        .table { color: #ccc; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-warning mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-warning fw-bold" href="index.php">🛡️ PANEL MODERACJI LAB 17</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Powrót do Forum</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <!-- Tematy -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">🏷️ Zarządzanie Tematami</div>
                    <div class="card-body">
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="add_topic" value="1">
                            <input type="text" name="nazwa_tematu" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" placeholder="Nazwa tematu" required>
                            <textarea name="opis" class="form-control form-control-sm bg-dark text-white border-secondary mb-2" placeholder="Opis..."></textarea>
                            <button type="submit" class="btn btn-sm btn-success w-100">Dodaj Temat</button>
                        </form>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($tematy as $t): ?>
                                <li class="list-group-item bg-transparent text-white border-secondary small">
                                    <strong><?php echo htmlspecialchars($t['nazwa_tematu']); ?></strong>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Cenzura -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">🚫 Zarządzanie Cenzurą</div>
                    <div class="card-body">
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="add_censure" value="1">
                            <div class="row g-2">
                                <div class="col-6"><input type="text" name="slowo" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Słowo" required></div>
                                <div class="col-6"><input type="text" name="zamiennik" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Zamiennik"></div>
                                <div class="col-12"><button type="submit" class="btn btn-sm btn-primary w-100">Dodaj do filtra</button></div>
                            </div>
                        </form>
                        <div style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-dark">
                                <thead><tr><th>Słowo</th><th>Zamiennik</th></tr></thead>
                                <tbody>
                                    <?php foreach ($cenzura as $c): ?>
                                        <tr><td><?php echo htmlspecialchars($c['slowo_zakazane']); ?></td><td><?php echo htmlspecialchars($c['zamiennik']); ?></td></tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Użytkownicy -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">👥 Użytkownicy</div>
                    <div class="card-body p-0">
                        <div style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-dark mb-0">
                                <thead><tr><th>Login</th><th>Rola</th><?php if($user_role==3): ?><th>Akcja</th><?php endif; ?></tr></thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($u['login']); ?></td>
                                            <td><?php echo getRoleLabel($u['poziom_uprawnien']); ?></td>
                                            <?php if($user_role == 3): ?>
                                            <td>
                                                <form method="POST" class="d-flex gap-1">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['idu']; ?>">
                                                    <select name="new_role" class="form-select form-select-sm bg-dark text-white border-secondary py-0" style="font-size: 0.7rem;">
                                                        <option value="1" <?php echo $u['poziom_uprawnien']==1?'selected':''; ?>>User</option>
                                                        <option value="2" <?php echo $u['poziom_uprawnien']==2?'selected':''; ?>>Mod</option>
                                                        <option value="3" <?php echo $u['poziom_uprawnien']==3?'selected':''; ?>>Admin</option>
                                                    </select>
                                                    <button type="submit" name="change_role" class="btn btn-sm btn-outline-warning py-0" style="font-size: 0.7rem;">OK</button>
                                                </form>
                                            </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posty -->
            <div class="col-12">
                <div class="admin-card">
                    <div class="card-header">📜 Ostatnie posty (Moderacja)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped table-hover mb-0 small">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Autor</th>
                                        <th>Temat/Tytuł</th>
                                        <th>Treść (fragment)</th>
                                        <th>Status</th>
                                        <th>Akcja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_posts as $p): ?>
                                        <tr>
                                            <td><?php echo $p['datagodzina']; ?></td>
                                            <td class="fw-bold"><?php echo htmlspecialchars($p['login']); ?></td>
                                            <td><small class="text-secondary">[<?php echo htmlspecialchars($p['nazwa_tematu']); ?>]</small><br><?php echo htmlspecialchars($p['tytul'] ?: '(odpowiedź)'); ?></td>
                                            <td><?php echo htmlspecialchars(mb_substr($p['tresc'], 0, 100)); ?>...</td>
                                            <td><?php echo $p['stan'] == 1 ? '<span class="text-success">Aktywny</span>' : '<span class="text-danger">Zablokowany</span>'; ?></td>
                                            <td>
                                                <?php if ($p['stan'] == 1): ?>
                                                    <a href="admin.php?block_post=<?php echo $p['idw']; ?>" class="btn btn-sm btn-outline-danger py-0" onclick="return confirm('Zablokować ten post?')">Blokuj</a>
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
