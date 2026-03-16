<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab14_user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$id_testu = $_POST['id_testu'];
$user_id = $_SESSION['lab14_user_id'];

// Pobierz wszystkie pytania dla tego testu
$stmt_pytania = $conn->prepare("SELECT id_pytania FROM pytania WHERE id_testu = ?");
$stmt_pytania->execute([$id_testu]);
$pytania = $stmt_pytania->fetchAll();

$total_questions = count($pytania);
$correct_answers = 0;

foreach ($pytania as $p) {
    $field_name = "pytanie_" . $p['id_pytania'];
    
    if (isset($_POST[$field_name])) {
        $odpowiedz_uzytkownika = $_POST[$field_name];
        
        // Sprawdź czy odpowiedź jest poprawna
        $stmt_check = $conn->prepare("SELECT czy_poprawna FROM odpowiedzi WHERE id_odpowiedzi = ? AND id_pytania = ?");
        $stmt_check->execute([$odpowiedz_uzytkownika, $p['id_pytania']]);
        $res = $stmt_check->fetch();
        
        if ($res && $res['czy_poprawna'] == 1) {
            $correct_answers++;
        }
    }
}

// Oblicz wynik procentowy
$wynik_procentowy = ($total_questions > 0) ? ($correct_answers / $total_questions) * 100 : 0;

// Zapisz wynik w bazie
$stmt_insert = $conn->prepare("INSERT INTO wyniki (id_uzytkownika, id_testu, wynik_procentowy) VALUES (?, ?, ?)");
$stmt_insert->execute([$user_id, $id_testu, $wynik_procentowy]);

// Pobierz informacje o teście do wyświetlenia
$stmt_test = $conn->prepare("SELECT nazwa_testu FROM testy WHERE id_testu = ?");
$stmt_test->execute([$id_testu]);
$test_info = $stmt_test->fetch();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wynik Testu - Lab 14</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .card { background: var(--card-bg); border: 1px solid var(--border-color); color: #fff !important; }
        .btn-accent { background: var(--accent-color) !important; color: #000 !important; font-weight: bold; }
        h2, h4 { color: #fff !important; }
        .lead { color: #eee !important; }
        .alert-success { color: #28a745 !important; border-color: #28a745 !important; }
        .alert-danger { color: #dc3545 !important; border-color: #dc3545 !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5 text-center">
        <div class="card bg-dark text-light border-secondary p-5 mx-auto" style="max-width: 600px;">
            <h2>Koniec Testu! 🏁</h2>
            <hr class="border-secondary">
            <h4 class="mb-4">Twój wynik w teście: <br><strong><?php echo htmlspecialchars($test_info['nazwa_testu']); ?></strong></h4>
            
            <div class="display-3 fw-bold mb-4 <?php echo $wynik_procentowy >= 50 ? 'text-success' : 'text-danger'; ?>">
                <?php echo round($wynik_procentowy); ?>%
            </div>
            
            <p class="lead mb-4">
                Poprawne odpowiedzi: <?php echo $correct_answers; ?> z <?php echo $total_questions; ?>
            </p>
            
            <?php if ($wynik_procentowy >= 50): ?>
                <div class="alert alert-success border-success bg-dark text-success mb-4">
                    Gratulacje! Zaliczono moduł szkoleniowy.
                </div>
            <?php else: ?>
                <div class="alert alert-danger border-danger bg-dark text-danger mb-4">
                    Niestety wynik jest zbyt niski. Spróbuj ponownie po przypomnieniu materiałów.
                </div>
            <?php endif; ?>

            <a href="index.php" class="btn btn-accent px-5">Powrót do Dashboardu</a>
        </div>
    </div>
</body>
</html>
