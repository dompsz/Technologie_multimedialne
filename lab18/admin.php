<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Zabezpieczenie - tylko dla administratorów (rola admin LUB login admin)
if (!isset($_SESSION['lab18_user_id']) || ($_SESSION['lab18_role'] !== 'admin' && $_SESSION['lab18_login'] !== 'admin')) {
    die("Brak uprawnień administratora.");
}

$user_role = $_SESSION['lab18_role'];

// --- OBSŁUGA AKCJI ---

// 1. Zarządzanie Galeriami
if (isset($_POST['add_gallery'])) {
    $nazwa = trim($_POST['nazwa_galerii']);
    $komercyjna = isset($_POST['czy_komercyjna']) ? 1 : 0;
    if (!empty($nazwa)) {
        $stmt = $conn->prepare("INSERT INTO galerie (idu, nazwa_galerii, czy_komercyjna) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['lab18_user_id'], $nazwa, $komercyjna]);
    }
}

// 2. Usuwanie obiektów
if (isset($_GET['delete_gallery'])) {
    $idg = (int)$_GET['delete_gallery'];
    $stmt = $conn->prepare("DELETE FROM galerie WHERE idg = ?");
    $stmt->execute([$idg]);
}
if (isset($_GET['delete_photo'])) {
    $idz = (int)$_GET['delete_photo'];
    $stmt = $conn->prepare("DELETE FROM zdjecia WHERE idz = ?");
    $stmt->execute([$idz]);
}
if (isset($_GET['delete_comment'])) {
    $idk = (int)$_GET['delete_comment'];
    $stmt = $conn->prepare("DELETE FROM komentarze WHERE idk = ?");
    $stmt->execute([$idk]);
}

// 3. Zmiana uprawnień
if (isset($_POST['change_role'])) {
    $idu = (int)$_POST['user_id'];
    $new_role = $_POST['new_role'];
    $stmt = $conn->prepare("UPDATE uzytkownicy SET rola = ? WHERE idu = ?");
    $stmt->execute([$new_role, $idu]);
}

// --- POBIERANIE DANYCH ---
$galerie = $conn->query("SELECT * FROM galerie ORDER BY idg DESC")->fetchAll();
$users = $conn->query("SELECT * FROM uzytkownicy ORDER BY rola DESC")->fetchAll();

// Ostatnie zdjęcia i komentarze do moderacji
$recent_photos = $conn->query("SELECT z.*, u.login, g.nazwa_galerii FROM zdjecia z JOIN uzytkownicy u ON z.idu = u.idu JOIN galerie g ON z.idg = g.idg ORDER BY z.datagodzina DESC LIMIT 10")->fetchAll();
$recent_comments = $conn->query("SELECT k.*, u.login, z.tytul FROM komentarze k JOIN uzytkownicy u ON k.idu = u.idu JOIN zdjecia z ON k.idz = z.idz ORDER BY k.datagodzina DESC LIMIT 10")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administracyjny - Galeria Lab 18</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 25px; overflow: hidden; }
        .card-header { background: rgba(255,193,7, 0.1); border-bottom: 1px solid #444; color: #ffc107; font-weight: bold; padding: 12px 20px; }
        .table { color: #ccc; }
        .btn-xxs { padding: 0px 5px; font-size: 0.65rem; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-warning mb-4">
        <div class="container-fluid">
            <a class="navbar-brand text-warning fw-bold" href="index.php">🛡️ PANEL ADMINISTRACYJNY LAB 18</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Powrót do Galerii</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            
            <!-- Galerie -->
            <div class="col-lg-6">
                <div class="admin-card">
                    <div class="card-header">📂 Zarządzanie Galeriami</div>
                    <div class="card-body">
                        <form method="POST" class="mb-4">
                            <input type="hidden" name="add_gallery" value="1">
                            <div class="input-group">
                                <input type="text" name="nazwa_galerii" class="form-control form-control-sm bg-dark text-white border-secondary" placeholder="Nowa galeria" required>
                                <button type="submit" class="btn btn-sm btn-success">Dodaj</button>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" name="czy_komercyjna" id="comCheck">
                                <label class="form-check-label small" for="comCheck">Komercyjna (Znaki wodne)</label>
                            </div>
                        </form>
                        <div style="max-height: 250px; overflow-y: auto;">
                            <table class="table table-sm table-dark">
                                <thead><tr><th>Nazwa</th><th>Akcja</th></tr></thead>
                                <tbody>
                                    <?php foreach ($galerie as $g): ?>
                                        <tr>
                                            <td class="small"><?php echo htmlspecialchars($g['nazwa_galerii']); ?></td>
                                            <td class="text-end">
                                                <a href="admin.php?delete_gallery=<?php echo $g['idg']; ?>" class="btn btn-xxs btn-danger" onclick="return confirm('Usunąć galerię?')">Usuń</a>
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
            <div class="col-lg-6">
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
                                                <form method="POST" class="d-flex gap-1">
                                                    <input type="hidden" name="user_id" value="<?php echo $u['idu']; ?>">
                                                    <select name="new_role" class="form-select form-select-sm bg-dark text-white border-secondary py-0 px-1" style="font-size: 0.6rem; width: auto;">
                                                        <option value="user" <?php echo $u['rola']=='user'?'selected':''; ?>>User</option>
                                                        <option value="admin" <?php echo $u['rola']=='admin'?'selected':''; ?>>Admin</option>
                                                    </select>
                                                    <button type="submit" name="change_role" class="btn btn-xxs btn-outline-warning">Zmień</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Posty (Zdjęcia i Komentarze) -->
            <div class="col-12">
                <div class="admin-card">
                    <div class="card-header">📜 Moderacja aktywności</div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            <div class="col-md-6 border-end border-secondary">
                                <div class="p-2 border-bottom border-secondary bg-black small fw-bold text-center">Ostatnie zdjęcia</div>
                                <table class="table table-sm table-dark table-striped mb-0 small">
                                    <tbody>
                                        <?php foreach ($recent_photos as $z): ?>
                                            <tr class="align-middle">
                                                <td><img src="uploads/<?php echo $z['plik']; ?>" style="width:30px; height:30px; object-fit:cover;"></td>
                                                <td><?php echo htmlspecialchars($z['tytul']); ?></td>
                                                <td><small class="text-secondary"><?php echo htmlspecialchars($z['login']); ?></small></td>
                                                <td class="text-end pe-2"><a href="admin.php?delete_photo=<?php echo $z['idz']; ?>" class="btn btn-xxs btn-danger">Usuń</a></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="p-2 border-bottom border-secondary bg-black small fw-bold text-center">Ostatnie komentarze</div>
                                <table class="table table-sm table-dark table-striped mb-0 small">
                                    <tbody>
                                        <?php foreach ($recent_comments as $k): ?>
                                            <tr class="align-middle">
                                                <td class="ps-2 fw-bold"><?php echo htmlspecialchars($k['login']); ?></td>
                                                <td class="text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($k['tresc']); ?></td>
                                                <td class="text-end pe-2"><a href="admin.php?delete_comment=<?php echo $k['idk']; ?>" class="btn btn-xxs btn-danger">Usuń</a></td>
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
    </div>
</body>
</html>
