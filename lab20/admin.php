<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Zabezpieczenie - tylko dla administratorów i moderatorów
if (!isset($_SESSION['lab20_user_id']) || !in_array($_SESSION['lab20_role'], ['admin', 'moderator'])) {
    die("Brak uprawnień administratora.");
}

$user_role = $_SESSION['lab20_role'];

// --- OBSŁUGA AKCJI ---

// 1. Zarządzanie Kategoriami
if (isset($_POST['add_category']) && $user_role === 'admin') {
    $nazwa = trim($_POST['nazwa_kategorii']);
    if (!empty($nazwa)) {
        $stmt = $conn->prepare("INSERT INTO kategorie_ogloszen (nazwa_kategorii) VALUES (?)");
        $stmt->execute([$nazwa]);
    }
}

// 2. Usuwanie
if (isset($_GET['delete_category']) && $user_role === 'admin') {
    $idko = (int)$_GET['delete_category'];
    $stmt = $conn->prepare("DELETE FROM kategorie_ogloszen WHERE idko = ?");
    $stmt->execute([$idko]);
}
if (isset($_GET['delete_ad'])) {
    $ido = (int)$_GET['delete_ad'];
    $stmt = $conn->prepare("DELETE FROM ogloszenia WHERE ido = ?");
    $stmt->execute([$ido]);
}

// 3. Zmiana uprawnień (Tylko Admin)
if (isset($_POST['change_role']) && $user_role === 'admin') {
    $idu = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    $stmt = $conn->prepare("UPDATE uzytkownicy SET rola = ? WHERE idu = ?");
    $stmt->execute([$new_role, $idu]);
}

// --- POBIERANIE DANYCH ---
$kategorie = $conn->query("SELECT * FROM kategorie_ogloszen ORDER BY idko DESC")->fetchAll();
$users = $conn->query("SELECT * FROM uzytkownicy ORDER BY rola DESC")->fetchAll();

// Ostatnie ogłoszenia i logi
$recent_ads = $conn->query("SELECT o.*, u.login, k.nazwa_kategorii FROM ogloszenia o JOIN uzytkownicy u ON o.idu = u.idu JOIN kategorie_ogloszen k ON o.idko = k.idko ORDER BY o.datagodzina DESC LIMIT 10")->fetchAll();
$logs = $conn->query("SELECT l.*, u.login, o.tytul FROM logi_ogloszen l LEFT JOIN uzytkownicy u ON l.idu = u.idu LEFT JOIN ogloszenia o ON l.ido = o.ido ORDER BY l.datagodzina DESC LIMIT 20")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administracyjny - Portal GIS Lab 20</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 25px; overflow: hidden; }
        .card-header { background: rgba(13, 110, 253, 0.1); border-bottom: 1px solid #444; color: #0d6efd; font-weight: bold; padding: 12px 20px; }
        .table { color: #ccc; }
        .btn-xxs { padding: 0px 5px; font-size: 0.65rem; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-primary fw-bold" href="index.php">🛡️ PANEL ADMINISTRACYJNY LAB 20</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Powrót do Portalu</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <!-- Kategorie -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">📂 Kategorie Ogłoszeń</div>
                    <div class="card-body">
                        <?php if ($user_role === 'admin'): ?>
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="add_category" value="1">
                            <div class="input-group">
                                <input type="text" name="nazwa_kategorii" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Nowa kategoria" required>
                                <button type="submit" class="btn btn-sm btn-primary">Dodaj</button>
                            </div>
                        </form>
                        <?php endif; ?>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-dark">
                                <thead><tr><th>Nazwa</th><th>Akcja</th></tr></thead>
                                <tbody>
                                    <?php foreach ($kategorie as $k): ?>
                                        <tr>
                                            <td class="small"><?php echo htmlspecialchars($k['nazwa_kategorii']); ?></td>
                                            <td class="text-end">
                                                <?php if ($user_role === 'admin'): ?>
                                                    <a href="admin.php?delete_category=<?php echo $k['idko']; ?>" class="btn btn-xxs btn-danger" onclick="return confirm('Usunąć kategorię?')">Usuń</a>
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

            <!-- Użytkownicy -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">👥 Użytkownicy</div>
                    <div class="card-body p-0">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-dark mb-0">
                                <thead><tr><th>User</th><th>Rola</th><th>Akcja</th></tr></thead>
                                <tbody>
                                    <?php foreach ($users as $u): ?>
                                        <tr class="border-secondary align-middle">
                                            <td class="small fw-bold"><?php echo htmlspecialchars($u['login']); ?></td>
                                            <td><?php echo getRoleLabel($u['rola']); ?></td>
                                            <td>
                                                <?php if ($user_role === 'admin'): ?>
                                                <form method="POST" class="d-flex gap-1">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['idu']; ?>">
                                                    <select name="new_role" class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-1" style="font-size: 0.6rem; width: auto;">
                                                        <option value="zalogowany" <?php echo $u['rola']=='zalogowany'?'selected':''; ?>>User</option>
                                                        <option value="moderator" <?php echo $u['rola']=='moderator'?'selected':''; ?>>Mod</option>
                                                        <option value="admin" <?php echo $u['rola']=='admin'?'selected':''; ?>>Admin</option>
                                                    </select>
                                                    <button type="submit" name="change_role" class="btn btn-xxs btn-outline-primary">Zmień</button>
                                                </form>
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

            <!-- Ostatnie ogłoszenia -->
            <div class="col-lg-4">
                <div class="admin-card">
                    <div class="card-header">📜 Ostatnie ogłoszenia</div>
                    <div class="card-body p-0">
                        <div style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-dark table-striped mb-0 small">
                                <tbody>
                                    <?php foreach ($recent_ads as $o): ?>
                                        <tr class="align-middle">
                                            <td class="ps-2"><?php echo htmlspecialchars($o['tytul']); ?></td>
                                            <td><small class="text-secondary"><?php echo htmlspecialchars($o['login']); ?></small></td>
                                            <td class="text-end pe-2">
                                                <a href="admin.php?delete_ad=<?php echo $o['ido']; ?>" class="btn btn-xxs btn-danger">Usuń</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logi -->
            <div class="col-12">
                <div class="admin-card">
                    <div class="card-header">🕵️ Logi systemowe</div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-dark table-striped mb-0 small">
                            <thead>
                                <tr>
                                    <th class="ps-3">Data</th>
                                    <th>Użytkownik</th>
                                    <th>Akcja</th>
                                    <th>Ogłoszenie</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($logs as $l): ?>
                                    <tr>
                                        <td class="ps-3 text-secondary"><?php echo $l['datagodzina']; ?></td>
                                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($l['login'] ?? 'System'); ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($l['akcja']); ?></span></td>
                                        <td class="text-truncate" style="max-width: 300px;"><?php echo htmlspecialchars($l['tytul'] ?? 'N/A'); ?></td>
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
