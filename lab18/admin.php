<?php
session_start();
require_once 'db_config.php';

// Zabezpieczenie - tylko dla administratorów
if (!isset($_SESSION['lab18_user_id']) || $_SESSION['lab18_role'] !== 'admin') {
    die("Brak uprawnień administratora.");
}

// Obsługa akcji administratora
if (isset($_GET['delete_gallery'])) {
    $idg = (int)$_GET['delete_gallery'];
    $stmt = $conn->prepare("DELETE FROM galerie WHERE idg = ?");
    $stmt->execute([$idg]);
    header("Location: admin.php?msg=gallery_deleted");
    exit();
}

if (isset($_GET['delete_photo'])) {
    $idz = (int)$_GET['delete_photo'];
    // Pobierz nazwę pliku, aby go usunąć z dysku
    $stmt_p = $conn->prepare("SELECT plik FROM zdjecia WHERE idz = ?");
    $stmt_p->execute([$idz]);
    $file = $stmt_p->fetchColumn();
    
    if ($file && file_exists('uploads/' . $file)) {
        unlink('uploads/' . $file);
    }

    $stmt = $conn->prepare("DELETE FROM zdjecia WHERE idz = ?");
    $stmt->execute([$idz]);
    header("Location: admin.php?msg=photo_deleted");
    exit();
}

// Pobieranie danych
$galerie = $conn->query("SELECT g.*, u.login FROM galerie g JOIN uzytkownicy u ON g.idu = u.idu ORDER BY g.idg DESC")->fetchAll();
$zdjecia = $conn->query("SELECT z.*, g.nazwa_galerii, u.login FROM zdjecia z JOIN galerie g ON z.idg = g.idg JOIN uzytkownicy u ON z.idu = u.idu ORDER BY z.idz DESC LIMIT 50")->fetchAll();
$uzytkownicy = $conn->query("SELECT * FROM uzytkownicy ORDER BY idu ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora - Galeria Lab 18</title>
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
            <a class="navbar-brand text-warning fw-bold" href="index.php">🛡️ PANEL ADMINA LAB 18</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">Powrót do Galerii</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            <!-- Zarządzanie Galeriami -->
            <div class="col-lg-6">
                <div class="admin-card">
                    <div class="card-header">📂 Zarządzanie Galeriami</div>
                    <div class="card-body p-0">
                        <table class="table table-dark table-striped table-hover mb-0 small">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nazwa</th>
                                    <th>Autor</th>
                                    <th>Typ</th>
                                    <th>Akcja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($galerie as $g): ?>
                                    <tr>
                                        <td><?php echo $g['idg']; ?></td>
                                        <td><?php echo htmlspecialchars($g['nazwa_galerii']); ?></td>
                                        <td><?php echo htmlspecialchars($g['login']); ?></td>
                                        <td><?php echo $g['czy_komercyjna'] ? '<span class="text-warning">Komercyjna</span>' : 'Zwykła'; ?></td>
                                        <td>
                                            <a href="admin.php?delete_gallery=<?php echo $g['idg']; ?>" class="btn btn-xxs btn-outline-danger p-0 px-2" style="font-size:0.7rem;" onclick="return confirm('Usunąć galerię wraz ze wszystkimi zdjęciami?')">Usuń</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Zarządzanie Użytkownikami -->
            <div class="col-lg-6">
                <div class="admin-card">
                    <div class="card-header">👥 Użytkownicy</div>
                    <div class="card-body p-0">
                        <table class="table table-dark table-striped table-hover mb-0 small">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Login</th>
                                    <th>Rola</th>
                                    <th>Rejestracja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($uzytkownicy as $u): ?>
                                    <tr>
                                        <td><?php echo $u['idu']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($u['login']); ?></td>
                                        <td><span class="badge <?php echo $u['rola'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>"><?php echo $u['rola']; ?></span></td>
                                        <td class="text-secondary"><?php echo $u['data_rejestracji']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Ostatnie Zdjęcia -->
            <div class="col-12">
                <div class="admin-card">
                    <div class="card-header">🖼️ Ostatnio Dodane Zdjęcia (Moderacja)</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped table-hover mb-0 small">
                                <thead>
                                    <tr>
                                        <th>Miniatura</th>
                                        <th>Tytuł</th>
                                        <th>Galeria</th>
                                        <th>Autor</th>
                                        <th>Data</th>
                                        <th>Akcja</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($zdjecia as $z): ?>
                                        <tr>
                                            <td><img src="uploads/<?php echo $z['plik']; ?>" style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px;"></td>
                                            <td><?php echo htmlspecialchars($z['tytul']); ?></td>
                                            <td><?php echo htmlspecialchars($z['nazwa_galerii']); ?></td>
                                            <td><?php echo htmlspecialchars($z['login']); ?></td>
                                            <td><?php echo $z['datagodzina']; ?></td>
                                            <td>
                                                <a href="admin.php?delete_photo=<?php echo $z['idz']; ?>" class="btn btn-sm btn-danger py-0" style="font-size: 0.7rem;" onclick="return confirm('Trwale usunąć to zdjęcie?')">Usuń</a>
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
