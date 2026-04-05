<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab16_user_id'];
$username = $_SESSION['lab16_username'];
$role = $_SESSION['lab16_role'];

// Pobranie podstron
$stmt = $conn->prepare("SELECT p.*, k.nazwa as kategoria, u.nazwa_uzytkownika as autor FROM podstrony p LEFT JOIN kategorie k ON p.idk = k.idk JOIN uzytkownicy u ON p.idu = u.idu ORDER BY p.data_aktualizacji DESC");
$stmt->execute();
$pages = $stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel CMS - Zadanie 16</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: #121212; color: #eee; }
        .dashboard-container { padding: 30px; }
        .table { color: #eee; background: var(--card-bg); border-radius: 12px; overflow: hidden; }
        .table thead { background: rgba(255,255,255,0.05); }
        .badge-status { font-size: 0.8rem; }
        .btn-action { padding: 5px 10px; font-size: 0.9rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">CMS Lab 16</a>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3">Witaj, <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo $role; ?>)</span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid dashboard-container">
        <div class="row mb-4 align-items-center">
            <div class="col">
                <h2>Zarządzanie treścią</h2>
            </div>
            <div class="col text-end">
                <a href="add_page.php" class="btn btn-success">+ Dodaj nową stronę</a>
                <?php if($role === 'admin'): ?>
                    <a href="admin.php" class="btn btn-primary">Panel Admina</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Tytuł</th>
                        <th>Kategoria</th>
                        <th>Autor</th>
                        <th>Status</th>
                        <th>Ostatnia zmiana</th>
                        <th>Akcje</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($pages as $page): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($page['tytul']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($page['kategoria'] ?? 'Brak'); ?></span></td>
                        <td><?php echo htmlspecialchars($page['autor']); ?></td>
                        <td>
                            <?php if($page['status'] === 'opublikowany'): ?>
                                <span class="badge bg-success badge-status">Opublikowany</span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark badge-status">Szkic</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $page['data_aktualizacji']; ?></td>
                        <td>
                            <a href="edit_page.php?id=<?php echo $page['idp']; ?>" class="btn btn-sm btn-outline-info btn-action">Edytuj</a>
                            <?php if($role === 'admin' || $user_id == $page['idu']): ?>
                                <a href="delete_page.php?id=<?php echo $page['idp']; ?>" class="btn btn-sm btn-outline-danger btn-action" onclick="return confirm('Czy na pewno chcesz usunąć tę stronę?')">Usuń</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(empty($pages)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-secondary">Brak stron w systemie.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
