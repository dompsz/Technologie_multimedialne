<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab14_user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id_wyniku = $_GET['id'];
$user_id = $_SESSION['lab14_user_id'];

// Pobierz wynik i upewnij się, że należy do użytkownika (chyba że to admin/coach)
$stmt_wynik = $conn->prepare("
    SELECT w.*, t.nazwa_testu 
    FROM wyniki w 
    JOIN testy t ON w.id_testu = t.id_testu 
    WHERE w.id_wyniku = ?
");
$stmt_wynik->execute([$id_wyniku]);
$wynik = $stmt_wynik->fetch();

if (!$wynik || ($wynik['id_uzytkownika'] != $user_id && !in_array($_SESSION['lab14_role'], ['admin', 'coach']))) {
    header("Location: index.php");
    exit();
}

// Pobierz szczegóły wyników
$stmt_details = $conn->prepare("
    SELECT ws.id_pytania, ws.id_odpowiedzi, p.tresc_pytania 
    FROM wyniki_szczegoly ws
    JOIN pytania p ON ws.id_pytania = p.id_pytania
    WHERE ws.id_wyniku = ?
");
$stmt_details->execute([$id_wyniku]);
$details = $stmt_details->fetchAll();

$user_results = [];
$correct_count = 0;

foreach ($details as $d) {
    // Pobierz wszystkie odpowiedzi dla tego pytania
    $stmt_odp = $conn->prepare("SELECT id_odpowiedzi, tresc_odpowiedzi, czy_poprawna FROM odpowiedzi WHERE id_pytania = ?");
    $stmt_odp->execute([$d['id_pytania']]);
    $odpowiedzi = $stmt_odp->fetchAll();
    
    $is_correct = false;
    foreach ($odpowiedzi as $o) {
        if ($d['id_odpowiedzi'] == $o['id_odpowiedzi'] && $o['czy_poprawna'] == 1) {
            $is_correct = true;
            $correct_count++;
        }
    }
    
    $user_results[] = [
        'tresc_pytania' => $d['tresc_pytania'],
        'odpowiedzi' => $odpowiedzi,
        'id_wybranej' => $d['id_odpowiedzi'],
        'czy_poprawna' => $is_correct
    ];
}

$total_questions = count($user_results);
$wynik_procentowy = $wynik['wynik_procentowy'];
$username = $_SESSION['lab14_username'] ?? 'Użytkownik';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Szczegóły Wyniku - <?php echo htmlspecialchars($wynik['nazwa_testu']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        :root {
            --success-color: #28a745;
            --danger-color: #dc3545;
        }
        .card { background: var(--card-bg); border: 1px solid var(--border-color); color: #fff !important; }
        .btn-accent { background: var(--accent-color) !important; color: #000 !important; font-weight: bold; }
        
        .question-block {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 5px solid transparent;
        }
        .question-block.correct { border-left-color: var(--success-color); }
        .question-block.wrong { border-left-color: var(--danger-color); }
        
        .ans { padding: 5px 10px; border-radius: 4px; margin: 2px 0; }
        .ans.user-choice { font-weight: bold; text-decoration: underline; }
        .ans.correct-ans { background: rgba(40, 167, 69, 0.2); color: #85ff9d; border: 1px solid var(--success-color); }
        .ans.wrong-ans { background: rgba(220, 53, 69, 0.2); color: #ff8585; border: 1px solid var(--danger-color); }

        @media print {
            body { background: white !important; color: black !important; }
            .bg-dark { background: white !important; color: black !important; }
            .card { background: white !important; border: 1px solid #ccc !important; color: black !important; box-shadow: none !important; }
            .no-print { display: none !important; }
            .question-block { background: #f9f9f9 !important; border: 1px solid #ddd !important; page-break-inside: avoid; }
            .ans.correct-ans { background: #d4edda !important; color: #155724 !important; border: 1px solid #c3e6cb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .ans.wrong-ans { background: #f8d7da !important; color: #721c24 !important; border: 1px solid #f5c6cb !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .text-success { color: #28a745 !important; }
            .text-danger { color: #dc3545 !important; }
            h2, h4, p, div { color: black !important; }
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5 mb-5">
        <div class="card p-4 shadow-lg">
            <div class="text-center mb-4">
                <h2 class="mb-1">Szczegóły Wyniku 📝</h2>
                <h4 class="text-secondary"><?php echo htmlspecialchars($wynik['nazwa_testu']); ?></h4>
                <p class="mb-0">Użytkownik: <strong><?php echo htmlspecialchars($username); ?></strong> | Data: <?php echo $wynik['data_zakonczenia']; ?></p>
            </div>

            <hr class="border-secondary mb-4">

            <div class="row align-items-center mb-4">
                <div class="col-md-6 text-center">
                    <div class="display-2 fw-bold <?php echo $wynik_procentowy >= 50 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo round($wynik_procentowy); ?>%
                    </div>
                    <p class="lead">Wynik końcowy</p>
                </div>
                <div class="col-md-6">
                    <div class="alert <?php echo $wynik_procentowy >= 50 ? 'alert-success bg-dark border-success text-success' : 'alert-danger bg-dark border-danger text-danger'; ?>">
                        <h5 class="alert-heading"><?php echo $wynik_procentowy >= 50 ? 'Zaliczono! 🎉' : 'Niezaliczono... 😕'; ?></h5>
                        <p class="mb-0">
                            Poprawne odpowiedzi: <strong><?php echo $correct_count; ?></strong> z <strong><?php echo $total_questions; ?></strong>.
                        </p>
                    </div>
                </div>
            </div>

            <h4 class="mb-3">Szczegóły odpowiedzi:</h4>
            <?php foreach ($user_results as $index => $res): ?>
                <div class="question-block <?php echo $res['czy_poprawna'] ? 'correct' : 'wrong'; ?>">
                    <h5>Pytanie <?php echo $index + 1; ?>: <?php echo htmlspecialchars($res['tresc_pytania']); ?></h5>
                    <div class="mt-2">
                        <?php foreach ($res['odpowiedzi'] as $o): 
                            $is_user_choice = ($o['id_odpowiedzi'] == $res['id_wybranej']);
                            $is_correct_answer = ($o['czy_poprawna'] == 1);
                            
                            $class = "";
                            if ($is_correct_answer) $class = "correct-ans";
                            elseif ($is_user_choice && !$is_correct_answer) $class = "wrong-ans";
                            
                            $choice_marker = $is_user_choice ? "🔘 " : "⚪ ";
                        ?>
                            <div class="ans <?php echo $class; ?> <?php echo $is_user_choice ? 'user-choice' : ''; ?>">
                                <?php echo $choice_marker; ?>
                                <?php echo htmlspecialchars($o['tresc_odpowiedzi']); ?>
                                <?php if ($is_user_choice) echo " <em>(Twoja odpowiedź)</em>"; ?>
                                <?php if ($is_correct_answer) echo " ✔️"; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="d-flex gap-3 justify-content-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-primary px-4">
                    🖨️ Generuj PDF / Drukuj
                </button>
                <a href="index.php" class="btn btn-accent px-4">
                    Powrót do Dashboardu
                </a>
            </div>
        </div>
    </div>
</body>
</html>
