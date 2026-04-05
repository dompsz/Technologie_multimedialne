<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id']) || $_SESSION['lab16_role'] !== 'admin') {
    header("Location: dashboard.php?error=admin_only");
    exit();
}

// Obsługa dodawania kategorii
if(isset($_POST['add_category'])) {
    $nazwa = trim($_POST['cat_name']);
    $slug = strtolower(str_replace(' ', '-', $nazwa));
    if(!empty($nazwa)) {
        $stmt = $conn->prepare("INSERT INTO kategorie (nazwa, slug) VALUES (?, ?)");
        $stmt->execute([$nazwa, $slug]);
    }
}

// Pobranie kategorii
$categories = $conn->query("SELECT * FROM kategorie ORDER BY nazwa ASC")->fetchAll();

// Pobranie użytkowników
$users = $conn->query("SELECT idu, nazwa_uzytkownika, rola, data_utworzenia FROM uzytkownicy ORDER BY data_utworzenia DESC")->fetchAll();

// Pobranie logów
$logs = $conn->query("SELECT l.*, u.nazwa_uzytkownika FROM logi_logowania l LEFT JOIN uzytkownicy u ON l.idu = u.idu ORDER BY l.datagodzina DESC LIMIT 20")->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administracyjny - CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .admin-section { background: #1e1e1e; padding: 20px; border-radius: 12px; margin-bottom: 30px; border: 1px solid #333; }
        .table { color: #eee; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary mb-4">
        <div class="container-fluid">
            <span class="navbar-brand">Panel Administracyjny CMS</span>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Powrót do treści</a>
        </div>
    </nav>

    <div class="container-fluid px-4">
        <div class="row">
            <!-- Zarządzanie Kategoriami -->
            <div class="col-md-4">
                <div class="admin-section">
                    <h4>Kategorie</h4>
                    <form method="POST" class="mb-3 d-flex gap-2">
                        <input type="text" name="cat_name" class="form-control" placeholder="Nowa kategoria" required>
                        <button type="submit" name="add_category" class="btn btn-success">Dodaj</button>
                    </form>
                    <ul class="list-group">
                        <?php foreach($categories as $cat): ?>
                            <li class="list-group-item bg-dark text-white border-secondary d-flex justify-content-between">
                                <?php echo htmlspecialchars($cat['nazwa']); ?>
                                <span class="text-secondary small">/<?php echo $cat['slug']; ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <!-- Zarządzanie Użytkownikami -->
            <div class="col-md-8">
                <div class="admin-section">
                    <h4>Użytkownicy</h4>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Użytkownik</th>
                                <th>Rola</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $u): ?>
                            <tr>
                                <td><?php echo $u['idu']; ?></td>
                                <td><?php echo htmlspecialchars($u['nazwa_uzytkownika']); ?></td>
                                <td><span class="badge <?php echo $u['rola'] === 'admin' ? 'bg-danger' : 'bg-primary'; ?>"><?php echo $u['rola']; ?></span></td>
                                <td><?php echo $u['data_utworzenia']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="admin-section">
                    <h4>Ostatnie logowania</h4>
                    <table class="table table-sm" style="font-size: 0.85rem;">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Próba jako</th>
                                <th>Użytkownik</th>
                                <th>Status</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($logs as $l): ?>
                            <tr>
                                <td><?php echo $l['datagodzina']; ?></td>
                                <td><?php echo htmlspecialchars($l['login_attempted']); ?></td>
                                <td><?php echo htmlspecialchars($l['nazwa_uzytkownika'] ?? '---'); ?></td>
                                <td>
                                    <?php if($l['stan'] == 1): ?>
                                        <span class="text-success">OK</span>
                                    <?php else: ?>
                                        <span class="text-danger">FAIL</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $l['ip_address']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
