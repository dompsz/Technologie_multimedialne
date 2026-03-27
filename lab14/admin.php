<?php
session_start();
require_once 'db_config.php';

// Zabezpieczenie - tylko dla użytkownika o roli 'admin'
if (!isset($_SESSION['lab14_user_id']) || ($_SESSION['lab14_role'] ?? '') !== 'admin') {
    die("Brak uprawnień administratora. Musisz być zalogowany jako 'admin'.");
}

// 1. Pobierz wszystkich użytkowników (oprócz admina)
$stmt_users = $conn->query("SELECT id, username, created_at FROM users WHERE role != 'admin' ORDER BY created_at DESC");
$all_users = $stmt_users->fetchAll();

// 2. Pobierz statystyki testów (dla raportu ogólnego)
$stmt_stats = $conn->query("
    SELECT t.nazwa_testu, 
           COUNT(w.id_wyniku) as podejscia,
           AVG(w.wynik_procentowy) as sredni_wynik
    FROM testy t
    LEFT JOIN wyniki w ON t.id_testu = w.id_testu
    GROUP BY t.id_testu
");
$test_stats = $stmt_stats->fetchAll();

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Admina - Lab 14 E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-card { background: var(--card-bg); border: 1px solid var(--border-color); padding: 25px; border-radius: 12px; margin-bottom: 25px; color: #fff; }
        .text-secondary { color: #bbb !important; }
        .table { color: #eee !important; }
        .table thead th { color: #fff !important; border-bottom: 2px solid #444; }
        h2, h4 { color: #fff !important; }
        .status-passed { color: #28a745; font-weight: bold; }
        .status-failed { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2>🛡️ Panel Administratora E-learning</h2>
                <p class="text-secondary mb-0">Podgląd postępów wszystkich pracowników</p>
            </div>
            <a href="index.php" class="btn btn-danger">Powrót do Dashboardu</a>
        </div>

        <div class="row">
            <!-- Statystyki Testów -->
            <div class="col-md-12">
                <div class="admin-card">
                    <h4>📊 Statystyki Modułów</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover">
                            <thead>
                                <tr>
                                    <th>Nazwa Testu</th>
                                    <th>Liczba podejść</th>
                                    <th>Średni Wynik</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($test_stats as $ts): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($ts['nazwa_testu']); ?></td>
                                        <td><?php echo $ts['podejscia']; ?></td>
                                        <td>
                                            <div class="progress bg-dark" style="height: 12px; width: 150px; border: 1px solid #444;">
                                                <div class="progress-bar bg-info" 
                                                     role="progressbar" 
                                                     style="width: <?php echo $ts['sredni_wynik']; ?>%;" 
                                                     aria-valuenow="<?php echo $ts['sredni_wynik']; ?>" 
                                                     aria-valuemin="0" 
                                                     aria-valuemax="100">
                                                </div>
                                            </div>
                                            <span class="small"><?php echo round($ts['sredni_wynik']); ?>%</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Lista Użytkowników i ich wyniki -->
            <div class="col-md-12">
                <div class="admin-card">
                    <h4>👥 Postępy Użytkowników</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Użytkownik</th>
                                    <th>Data rejestracji</th>
                                    <th>Ostatnie wyniki</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($all_users as $u): 
                                    // Pobierz wyniki dla tego konkretnego użytkownika
                                    $stmt_u_wyniki = $conn->prepare("
                                        SELECT w.*, t.nazwa_testu 
                                        FROM wyniki w 
                                        JOIN testy t ON w.id_testu = t.id_testu 
                                        WHERE w.id_uzytkownika = ? 
                                        ORDER BY w.data_zakonczenia DESC
                                    ");
                                    $stmt_u_wyniki->execute([$u['id']]);
                                    $u_results = $stmt_u_wyniki->fetchAll();
                                ?>
                                    <tr>
                                        <td class="fw-bold"><?php echo htmlspecialchars($u['username']); ?></td>
                                        <td class="text-secondary"><?php echo $u['created_at']; ?></td>
                                        <td>
                                            <?php if(empty($u_results)): ?>
                                                <span class="text-muted small">Brak podejść</span>
                                            <?php else: ?>
                                                <?php foreach(array_slice($u_results, 0, 3) as $ur): ?>
                                                    <div class="small">
                                                        <?php echo htmlspecialchars($ur['nazwa_testu']); ?>: 
                                                        <a href="view_result.php?id=<?php echo $ur['id_wyniku']; ?>" class="text-decoration-none">
                                                            <span class="<?php echo $ur['wynik_procentowy'] >= 50 ? 'status-passed' : 'status-failed'; ?>">
                                                                <?php echo round($ur['wynik_procentowy']); ?>%
                                                            </span>
                                                            <small class="text-info">👁️</small>
                                                        </a>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                            $has_passed_any = false;
                                            foreach($u_results as $ur) if($ur['wynik_procentowy'] >= 50) $has_passed_any = true;
                                            ?>
                                            <?php if($has_passed_any): ?>
                                                <span class="badge bg-success">AKTYWNY / ZALICZONE</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">W TRAKCIE / BRAK ZALICZEŃ</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Pełna Historia Podejść -->
            <div class="col-md-12">
                <div class="admin-card">
                    <h4>📜 Pełna Historia Podejść (Logi)</h4>
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Użytkownik</th>
                                    <th>Szkolenie</th>
                                    <th>Wynik</th>
                                    <th>Zaliczono</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $stmt_hist = $conn->query("
                                    SELECT w.*, u.username, t.nazwa_testu 
                                    FROM wyniki w
                                    JOIN users u ON w.id_uzytkownika = u.id
                                    JOIN testy t ON w.id_testu = t.id_testu
                                    ORDER BY w.data_zakonczenia DESC
                                    LIMIT 50
                                ");
                                while($h = $stmt_hist->fetch()): ?>
                                    <tr>
                                        <td class="text-secondary small"><?php echo $h['data_zakonczenia']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($h['username']); ?></td>
                                        <td><?php echo htmlspecialchars($h['nazwa_testu']); ?></td>
                                        <td>
                                            <a href="view_result.php?id=<?php echo $h['id_wyniku']; ?>" class="text-decoration-none">
                                                <span class="<?php echo $h['wynik_procentowy'] >= 50 ? 'status-passed' : 'status-failed'; ?>">
                                                    <?php echo round($h['wynik_procentowy']); ?>%
                                                </span>
                                                <small class="text-info">👁️</small>
                                            </a>
                                        </td>
                                        <td>
                                            <?php if($h['wynik_procentowy'] >= 50): ?>
                                                <span class="badge bg-success">TAK</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">NIE</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
