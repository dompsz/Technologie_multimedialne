<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab14_user_id'])) {
    header("Location: login.php");
    exit();
}

$id_testu = $_GET['id'] ?? 1;

// Pobierz informacje o teście/szkoleniu
$stmt = $conn->prepare("SELECT * FROM testy WHERE id_testu = ?");
$stmt->execute([$id_testu]);
$test = $stmt->fetch();

if (!$test) {
    die("Brak szkolenia o podanym ID.");
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Szkolenie: <?php echo htmlspecialchars($test['nazwa_testu']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .card { background: var(--card-bg); border: 1px solid var(--border-color); color: #fff !important; }
        .text-secondary { color: #bbb !important; }
        .text-info { color: var(--accent-color) !important; }
        .alert-info { border-color: var(--accent-color) !important; }
        h2, h3, h4 { color: #fff !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between mb-4">
            <h2>Moduł Szkoleniowy: <?php echo htmlspecialchars($test['nazwa_testu']); ?></h2>
            <a href="index.php" class="btn btn-secondary">Powrót do Dashboardu</a>
        </div>

        <div class="card bg-dark text-light border-secondary p-4 mb-4">
            <h3>Materiały teoretyczne</h3>
            <hr>
            
            <div class="training-content mt-4">
                <?php if (!empty($test['tresc_szkolenia'])): ?>
                    <?php echo $test['tresc_szkolenia']; ?>
                <?php else: ?>
                    <p>Brak szczegółowych materiałów dla tego modułu.</p>
                <?php endif; ?>
            </div>

            <div class="alert alert-info bg-dark text-info border-info mt-4">
                <strong>Wskazówka:</strong> Zapamiętaj kluczowe definicje i skróty techniczne przed przejściem do testu.
            </div>
        </div>

        <div class="text-center mb-5">
            <a href="test.php?id=<?php echo $id_testu; ?>" class="btn btn-lg btn-success">Rozpocznij Test Wiedzy</a>
        </div>
    </div>
</body>
</html>
