<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab15_user_id']) || $_SESSION['lab15_role'] === 'client') {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['lab15_user_id'];
$username = $_SESSION['lab15_username'];

// 1. Statystyki osobiste
$stmt_personal = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM odpowiedzi WHERE idp = ?) as liczba_odpowiedzi,
        (SELECT AVG(po.ocena_pracownika) FROM posty po 
         WHERE po.idpo IN (SELECT o2.idpo FROM odpowiedzi o2 WHERE o2.idp = ?)
         AND po.ocena_pracownika IS NOT NULL) as srednia_ocen
");
$stmt_personal->execute([$user_id, $user_id]);
$my_stats = $stmt_personal->fetch();

// 2. Średnia zespołu dla porównania
$stmt_team = $conn->query("
    SELECT AVG(cnt) as team_avg FROM (
        SELECT COUNT(*) as cnt FROM odpowiedzi GROUP BY idp
    ) as counts
");
$team_data = $stmt_team->fetch();
$team_avg = $team_data['team_avg'] ?? 0;

function getEfficiencySymbol($replies, $avg) {
    if ($avg == 0) return '👤 (człowiek)';
    $ratio = $replies / $avg;
    if ($ratio > 1.5) return '🐆 (puma - najszybszy)';
    if ($ratio > 0.8) return '👤 (człowiek - przeciętny)';
    if ($ratio > 0.4) return '🐢 (żółw - powolny)';
    return '🐌 (ślimak - najwolniejszy)';
}

// 3. Własna historia logowań
$stmt_log = $conn->prepare("
    SELECT * FROM logi_pracownikow 
    WHERE idp = ? 
    ORDER BY datagodzina DESC LIMIT 30
");
$stmt_log->execute([$user_id]);
$my_logs = $stmt_log->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Moja Historia i Statystyki - CRM Lab 15</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-info mb-4">
        <div class="container">
            <a class="navbar-brand text-info fw-bold" href="dashboard.php">📈 MOJE STATYSTYKI</a>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm">Powrót do Dashboardu</a>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <!-- Moje Statystyki -->
            <div class="col-lg-4 mb-4">
                <div class="card bg-dark text-light border-info h-100">
                    <div class="card-header border-info">
                        <h5 class="mb-0">📊 Twoja Wydajność</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 text-center">
                            <div class="display-4 fw-bold text-info"><?php echo $my_stats['liczba_odpowiedzi']; ?></div>
                            <div class="text-secondary small">Udzielonych odpowiedzi</div>
                        </div>
                        <div class="mb-4 text-center">
                            <div class="display-6 fw-bold text-warning">
                                <?php echo $my_stats['srednia_ocen'] ? round($my_stats['srednia_ocen'], 2) . ' ⭐' : '---'; ?>
                            </div>
                            <div class="text-secondary small">Twoja średnia ocen</div>
                        </div>
                        <hr class="border-secondary">
                        <div class="p-2 rounded bg-black">
                            <small class="text-secondary d-block mb-1">Status zespołu:</small>
                            <div class="fw-bold">
                                <?php echo getEfficiencySymbol($my_stats['liczba_odpowiedzi'], $team_avg); ?>
                            </div>
                            <small class="text-muted">Średnia zespołu: <?php echo round($team_avg, 1); ?> odp.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historia Logowań -->
            <div class="col-lg-8 mb-4">
                <div class="card bg-dark text-light border-secondary h-100">
                    <div class="card-header border-secondary">
                        <h5 class="mb-0">🔑 Twoja Historia Logowań</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-dark table-striped table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Data i godzina</th>
                                        <th>Adres IP</th>
                                        <th>System / Przeglądarka</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($my_logs)): ?>
                                        <tr><td colspan="3" class="text-center text-muted">Brak zarejestrowanych logowań.</td></tr>
                                    <?php else: ?>
                                        <?php foreach ($my_logs as $l): ?>
                                            <tr>
                                                <td class="text-info"><?php echo $l['datagodzina']; ?></td>
                                                <td><code><?php echo $l['ip_address'] ?: '---'; ?></code></td>
                                                <td>
                                                    <small>
                                                        <?php echo $l['system'] ?: 'Nieznany'; ?> / 
                                                        <?php echo $l['przegladarka'] ?: 'Nieznana'; ?>
                                                    </small>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
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
