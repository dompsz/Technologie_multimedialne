<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab14_user_id'])) {
    header("Location: login.php");
    exit();
}

// Pobierz dostępne testy
$stmt = $conn->query("SELECT * FROM testy");
$testy = $stmt->fetchAll();

// Pobierz wyniki użytkownika
$stmt_wyniki = $conn->prepare("
    SELECT w.*, t.nazwa_testu 
    FROM wyniki w 
    JOIN testy t ON w.id_testu = t.id_testu 
    WHERE w.id_uzytkownika = ? 
    ORDER BY w.data_zakonczenia DESC
");
$stmt_wyniki->execute([$_SESSION['lab14_user_id']]);
$moje_wyniki = $stmt_wyniki->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Lab 14 E-learning</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .card { background: var(--card-bg); border: 1px solid var(--border-color); color: white; margin-bottom: 20px; }
        .btn-accent { background: var(--accent-color); color: black; font-weight: bold; }
        .btn-accent:hover { background: var(--accent-hover); }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Witaj, <?php echo htmlspecialchars($_SESSION['lab14_username']); ?>! 🎓</h1>
            <div>
                <a href="../index.php" class="btn btn-outline-light me-2">Strona Główna</a>
                <a href="logout.php" class="btn btn-danger">Wyloguj</a>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <h3>Dostępne Kursy i Testy</h3>
                <hr>
                <?php foreach ($testy as $t): ?>
                    <div class="card p-4">
                        <h4><?php echo htmlspecialchars($t['nazwa_testu']); ?></h4>
                        <p class="text-secondary"><?php echo htmlspecialchars($t['opis']); ?></p>
                        <p><small>Czas na rozwiązanie: <?php echo floor($t['czas_trwania'] / 60); ?> min</small></p>
                        <div class="d-flex gap-2">
                            <a href="training.php?id=<?php echo $t['id_testu']; ?>" class="btn btn-outline-info">Moduł Szkoleniowy</a>
                            <a href="test.php?id=<?php echo $t['id_testu']; ?>" class="btn btn-accent">Rozpocznij Test</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-md-4">
                <h3>Twoje Wyniki</h3>
                <hr>
                <?php if (empty($moje_wyniki)): ?>
                    <p class="text-muted">Nie ukończyłeś jeszcze żadnego testu.</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($moje_wyniki as $w): ?>
                            <div class="list-group-item bg-dark text-light border-secondary">
                                <div class="d-flex justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($w['nazwa_testu']); ?></h6>
                                    <span class="badge <?php echo $w['wynik_procentowy'] >= 50 ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo round($w['wynik_procentowy']); ?>%
                                    </span>
                                </div>
                                <small class="text-muted"><?php echo $w['data_zakonczenia']; ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
