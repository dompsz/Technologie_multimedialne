<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab15_user_id']) || $_SESSION['lab15_role'] !== 'admin') {
    die("Brak uprawnień administratora.");
}

// 1. Statystyki wydajności pracowników
$stmt_stats = $conn->query("
    SELECT p.idp, p.nazwisko,
           (SELECT COUNT(*) FROM odpowiedzi o WHERE o.idp = p.idp) as liczba_odpowiedzi,
           (SELECT AVG(po.ocena_pracownika) FROM posty po 
            WHERE po.idpo IN (SELECT o2.idpo FROM odpowiedzi o2 WHERE o2.idp = p.idp)
            AND po.ocena_pracownika IS NOT NULL) as srednia_ocen
    FROM pracownicy p
    WHERE p.role = 'pracownik'
");
$stats = $stmt_stats->fetchAll();

// Obliczanie średniej zespołu dla wizualizacji
$total_replies = 0;
foreach ($stats as $s) $total_replies += $s['liczba_odpowiedzi'];
$team_avg = count($stats) > 0 ? $total_replies / count($stats) : 0;

function getEfficiencySymbol($replies, $avg) {
    if ($avg == 0) return '👤 (człowiek)';
    $ratio = $replies / $avg;
    if ($ratio > 1.5) return '🐆 (puma - najszybszy)';
    if ($ratio > 0.8) return '👤 (człowiek - przeciętny)';
    if ($ratio > 0.4) return '🐢 (żółw - powolny)';
    return '🐌 (ślimak - najwolniejszy)';
}

// 2. Logi Klientów
$stmt_log_k = $conn->query("
    SELECT l.*, k.nazwisko 
    FROM logi_klientow l 
    JOIN klienci k ON l.idk = k.idk 
    ORDER BY l.datagodzina DESC LIMIT 20
");
$logi_k = $stmt_log_k->fetchAll();

// 3. Logi Pracowników
$stmt_log_p = $conn->query("
    SELECT l.*, p.nazwisko 
    FROM logi_pracownikow l 
    JOIN pracownicy p ON l.idp = p.idp 
    ORDER BY l.datagodzina DESC LIMIT 20
");
$logi_p = $stmt_log_p->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel Administratora CRM - Lab 15</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-warning mb-4">
        <div class="container">
            <a class="navbar-brand text-warning fw-bold" href="dashboard.php">🛡️ CRM ADMIN PANEL</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Powrót do Dashboardu</a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <!-- Statystyki Wydajności -->
            <div class="col-12 mb-5">
                <div class="card bg-dark text-light border-warning">
                    <div class="card-header border-warning">
                        <h4 class="mb-0">📊 Statystyki Wydajności Pracowników</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped align-middle">
                                <thead>
                                    <tr>
                                        <th>Pracownik</th>
                                        <th>Liczba odpowiedzi</th>
                                        <th>Średnia Ocen</th>
                                        <th>Status Wydajności</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stats as $s): ?>
                                        <tr>
                                            <td class="fw-bold"><?php echo htmlspecialchars($s['nazwisko']); ?></td>
                                            <td><?php echo $s['liczba_odpowiedzi']; ?></td>
                                            <td>
                                                <?php echo $s['srednia_ocen'] ? round($s['srednia_ocen'], 2) . ' ⭐' : '<span class="text-muted">Brak ocen</span>'; ?>
                                            </td>
                                            <td>
                                                <?php echo getEfficiencySymbol($s['liczba_odpowiedzi'], $team_avg); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logi Klientów -->
            <div class="col-md-6 mb-4">
                <div class="card bg-dark text-light border-secondary h-100">
                    <div class="card-header border-secondary">
                        <h5 class="mb-0">🕵️ Logi Logowań Klientów</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-dark table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Klient</th>
                                        <th>IP</th>
                                        <th>System/Browser</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logi_k as $l): ?>
                                        <tr>
                                            <td class="text-secondary small"><?php echo $l['datagodzina']; ?></td>
                                            <td><?php echo htmlspecialchars($l['nazwisko']); ?></td>
                                            <td><?php echo $l['ip_address']; ?></td>
                                            <td class="small"><?php echo $l['system']; ?> / <?php echo $l['przegladarka']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Logi Pracowników -->
            <div class="col-md-6 mb-4">
                <div class="card bg-dark text-light border-secondary h-100">
                    <div class="card-header border-secondary">
                        <h5 class="mb-0">🔑 Logi Logowań Pracowników</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-dark table-sm table-hover mb-0" style="font-size: 0.85rem;">
                                <thead>
                                    <tr>
                                        <th>Data</th>
                                        <th>Pracownik</th>
                                        <th>IP</th>
                                        <th>System/Browser</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logi_p as $l): ?>
                                        <tr>
                                            <td class="text-secondary small"><?php echo $l['datagodzina']; ?></td>
                                            <td><?php echo htmlspecialchars($l['nazwisko']); ?></td>
                                            <td><?php echo $l['ip_address'] ?: '---'; ?></td>
                                            <td class="small"><?php echo ($l['system'] ?: 'Nieznany') . ' / ' . ($l['przegladarka'] ?: 'Nieznana'); ?></td>
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
