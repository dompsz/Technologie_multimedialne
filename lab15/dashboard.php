<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab15_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab15_user_id'];
$username = $_SESSION['lab15_username'];
$role = $_SESSION['lab15_role'];

// Obsługa POST dla Klienta (Nowe zgłoszenie)
if ($role === 'client' && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_post'])) {
    $idz = $_POST['idz'];
    $tresc = trim($_POST['tresc']);
    $priorytet = (int)$_POST['priorytet'];

    if (!empty($tresc)) {
        $stmt = $conn->prepare("INSERT INTO posty (idz, idk, tresc, priorytet, stan) VALUES (?, ?, ?, ?, 0)");
        $stmt->execute([$idz, $user_id, $tresc, $priorytet]);
        header("Location: dashboard.php?msg=dodano");
        exit();
    }
}

// Pobieranie danych w zależności od roli
if ($role === 'client') {
    // Widok dla Klienta - własne posty
    $stmt = $conn->prepare("
        SELECT p.*, z.nazwa as kategoria, 
        (SELECT COUNT(*) FROM odpowiedzi o WHERE o.idpo = p.idpo) as liczba_odpowiedzi
        FROM posty p 
        JOIN zagadnienia z ON p.idz = z.idz 
        WHERE p.idk = ? 
        ORDER BY p.datagodzina DESC
    ");
    $stmt->execute([$user_id]);
    $user_posts = $stmt->fetchAll();

    // Kategorie do formularza
    $stmt_z = $conn->query("SELECT * FROM zagadnienia");
    $zagadnienia = $stmt_z->fetchAll();
} else {
    // Widok dla Pracownika/Admina - wszystkie posty
    // Opcjonalne filtrowanie
    $where = "1=1";
    $params = [];
    if (isset($_GET['filter_cat']) && $_GET['filter_cat'] != '') {
        $where .= " AND p.idz = ?";
        $params[] = $_GET['filter_cat'];
    }
    if (isset($_GET['filter_stan']) && $_GET['filter_stan'] != '') {
        $where .= " AND p.stan = ?";
        $params[] = $_GET['filter_stan'];
    }

    $stmt = $conn->prepare("
        SELECT p.*, z.nazwa as kategoria, k.nazwisko as klient_nazwisko 
        FROM posty p 
        JOIN zagadnienia z ON p.idz = z.idz 
        JOIN klienci k ON p.idk = k.idk 
        WHERE $where
        ORDER BY p.priorytet DESC, p.datagodzina ASC
    ");
    $stmt->execute($params);
    $all_posts = $stmt->fetchAll();

    $stmt_z = $conn->query("SELECT * FROM zagadnienia");
    $zagadnienia = $stmt_z->fetchAll();
}

function getStanLabel($stan) {
    switch ($stan) {
        case 0: return '<span class="badge bg-danger">Oczekujący</span>';
        case 1: return '<span class="badge bg-warning text-dark">W trakcie</span>';
        case 2: return '<span class="badge bg-success">Zakończony</span>';
        default: return 'Nieznany';
    }
}

function getPriorityColor($priority) {
    if ($priority >= 4) return 'border-danger';
    if ($priority == 3) return 'border-warning';
    return 'border-info';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard CRM - Lab 15</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .sidebar { background: var(--card-bg); border-right: 1px solid var(--border-color); min-height: calc(100vh - 56px); }
        .post-card { background: var(--card-bg); border-left: 5px solid; transition: transform 0.2s; }
        .post-card:hover { transform: scale(1.01); }
        .nav-link { color: #ccc; }
        .nav-link:hover, .nav-link.active { color: var(--accent-color); }
        .btn-accent { background: var(--accent-color); color: #000; font-weight: bold; }
        .btn-accent:hover { background: var(--accent-hover); }
    </style>
</head>
<body class="bg-dark text-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">CRM System 🏢</a>
            <div class="d-flex align-items-center">
                <span class="text-secondary me-3">Witaj, <strong><?php echo htmlspecialchars($username); ?></strong> (<?php echo $role; ?>)</span>
                <?php if ($role !== 'client'): ?>
                    <a href="history.php" class="btn btn-outline-info btn-sm me-2">Moja Historia</a>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <a href="admin.php" class="btn btn-outline-warning btn-sm me-2">Panel Administratora</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <?php if ($role === 'client'): ?>
                <!-- WIDOK KLIENTA -->
                <div class="col-md-4">
                    <div class="card bg-dark text-light border-secondary p-4 mb-4">
                        <h4>Nowe Zgłoszenie ➕</h4>
                        <hr class="border-secondary">
                        <form method="POST">
                            <input type="hidden" name="add_post" value="1">
                            <div class="mb-3">
                                <label class="form-label small text-secondary">Kategoria</label>
                                <select name="idz" class="form-select bg-dark text-light border-secondary" required>
                                    <?php foreach ($zagadnienia as $z): ?>
                                        <option value="<?php echo $z['idz']; ?>"><?php echo htmlspecialchars($z['nazwa']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-secondary">Priorytet (1-5)</label>
                                <input type="range" name="priorytet" class="form-range" min="1" max="5" step="1" value="1" oninput="this.nextElementSibling.value = this.value">
                                <output class="small">1</output>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small text-secondary">Treść problemu</label>
                                <textarea name="tresc" class="form-control bg-dark text-light border-secondary" rows="4" placeholder="Opisz swoje zgłoszenie..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-accent w-100">WYŚLIJ ZGŁOSZENIE</button>
                        </form>
                    </div>
                </div>
                <div class="col-md-8">
                    <h4 class="mb-4">Twoje Zgłoszenia 📋</h4>
                    <?php if (empty($user_posts)): ?>
                        <p class="text-muted">Nie masz jeszcze żadnych zgłoszeń.</p>
                    <?php else: ?>
                        <?php foreach ($user_posts as $p): ?>
                            <div class="card post-card mb-3 <?php echo getPriorityColor($p['priorytet']); ?> p-3 shadow-sm">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">#<?php echo $p['idpo']; ?> [<?php echo htmlspecialchars($p['kategoria']); ?>]</h5>
                                        <small class="text-secondary"><?php echo $p['datagodzina']; ?></small>
                                    </div>
                                    <div class="text-end">
                                        <?php echo getStanLabel($p['stan']); ?>
                                        <div class="small text-secondary mt-1">Priorytet: <?php echo $p['priorytet']; ?>/5</div>
                                    </div>
                                </div>
                                <p class="mt-3 text-light"><?php echo nl2br(htmlspecialchars($p['tresc'])); ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="badge bg-secondary"><?php echo $p['liczba_odpowiedzi']; ?> odpowiedzi</span>
                                    <a href="view_ticket.php?id=<?php echo $p['idpo']; ?>" class="btn btn-sm btn-outline-info">Pokaż wątek & odpowiedz</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- WIDOK PRACOWNIKA / ADMINA -->
                <div class="col-md-3">
                    <div class="card bg-dark text-light border-secondary p-3 mb-4">
                        <h5>Filtrowanie 🔍</h5>
                        <form method="GET">
                            <div class="mb-3">
                                <label class="small text-secondary">Kategoria</label>
                                <select name="filter_cat" class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">Wszystkie</option>
                                    <?php foreach ($zagadnienia as $z): ?>
                                        <option value="<?php echo $z['idz']; ?>" <?php echo (isset($_GET['filter_cat']) && $_GET['filter_cat'] == $z['idz']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($z['nazwa']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="small text-secondary">Stan</label>
                                <select name="filter_stan" class="form-select form-select-sm bg-dark text-light border-secondary">
                                    <option value="">Wszystkie</option>
                                    <option value="0" <?php echo (isset($_GET['filter_stan']) && $_GET['filter_stan'] === '0') ? 'selected' : ''; ?>>Oczekujący</option>
                                    <option value="1" <?php echo (isset($_GET['filter_stan']) && $_GET['filter_stan'] === '1') ? 'selected' : ''; ?>>W trakcie</option>
                                    <option value="2" <?php echo (isset($_GET['filter_stan']) && $_GET['filter_stan'] === '2') ? 'selected' : ''; ?>>Zakończony</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-sm btn-accent w-100">FILTRUJ</button>
                            <a href="dashboard.php" class="btn btn-sm btn-link text-secondary w-100 mt-1">Wyczyść</a>
                        </form>
                    </div>
                </div>
                <div class="col-md-9">
                    <h4 class="mb-4">Zgłoszenia do Obsługi 🛠️</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead class="table-secondary">
                                <tr>
                                    <th>ID</th>
                                    <th>Priorytet</th>
                                    <th>Klient</th>
                                    <th>Kategoria</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Akcja</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($all_posts as $p): ?>
                                    <tr class="<?php echo $p['priorytet'] >= 5 ? 'table-danger' : ''; ?>">
                                        <td>#<?php echo $p['idpo']; ?></td>
                                        <td>
                                            <span class="fw-bold <?php echo $p['priorytet'] >= 4 ? 'text-danger' : ($p['priorytet'] == 3 ? 'text-warning' : 'text-info'); ?>">
                                                <?php echo $p['priorytet']; ?>/5
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($p['klient_nazwisko']); ?></td>
                                        <td><?php echo htmlspecialchars($p['kategoria']); ?></td>
                                        <td class="small text-secondary"><?php echo $p['datagodzina']; ?></td>
                                        <td><?php echo getStanLabel($p['stan']); ?></td>
                                        <td>
                                            <a href="view_ticket.php?id=<?php echo $p['idpo']; ?>" class="btn btn-sm btn-info text-dark">Obsługuj</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
