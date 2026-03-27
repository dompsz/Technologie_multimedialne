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
$stmt_pytania = $conn->prepare("SELECT id_pytania, tresc_pytania FROM pytania WHERE id_testu = ?");
$stmt_pytania->execute([$id_testu]);
$pytania = $stmt_pytania->fetchAll();

$total_questions = count($pytania);
$correct_answers_count = 0;
$user_results = [];

// Rozpocznij transakcję zapisu wyników
$conn->beginTransaction();
try {
    // 1. Wstępny zapis wyniku (wynik procentowy zaktualizujemy na końcu)
    $stmt_insert_wynik = $conn->prepare("INSERT INTO wyniki (id_uzytkownika, id_testu, wynik_procentowy) VALUES (?, ?, 0)");
    $stmt_insert_wynik->execute([$user_id, $id_testu]);
    $id_wyniku = $conn->lastInsertId();

    foreach ($pytania as $p) {
        $id_pytania = $p['id_pytania'];
        $field_name = "pytanie_" . $id_pytania;
        $id_odpowiedzi_uzytkownika = isset($_POST[$field_name]) ? $_POST[$field_name] : null;
        
        // Pobierz możliwe odpowiedzi dla pytania
        $stmt_odp = $conn->prepare("SELECT id_odpowiedzi, tresc_odpowiedzi, czy_poprawna FROM odpowiedzi WHERE id_pytania = ?");
        $stmt_odp->execute([$id_pytania]);
        $odpowiedzi = $stmt_odp->fetchAll();
        
        $is_correct = false;
        foreach ($odpowiedzi as $o) {
            if ($id_odpowiedzi_uzytkownika == $o['id_odpowiedzi'] && $o['czy_poprawna'] == 1) {
                $is_correct = true;
                $correct_answers_count++;
            }
        }
        
        // Zapisz wybór użytkownika
        $stmt_detail = $conn->prepare("INSERT INTO wyniki_szczegoly (id_wyniku, id_pytania, id_odpowiedzi) VALUES (?, ?, ?)");
        $stmt_detail->execute([$id_wyniku, $id_pytania, $id_odpowiedzi_uzytkownika]);
        
        $user_results[] = [
            'tresc_pytania' => $p['tresc_pytania'],
            'odpowiedzi' => $odpowiedzi,
            'id_wybranej' => $id_odpowiedzi_uzytkownika,
            'czy_poprawna' => $is_correct
        ];
    }

    // 2. Finalizacja wyniku procentowego
    $wynik_procentowy = ($total_questions > 0) ? ($correct_answers_count / $total_questions) * 100 : 0;
    $stmt_update = $conn->prepare("UPDATE wyniki SET wynik_procentowy = ? WHERE id_wyniku = ?");
    $stmt_update->execute([$wynik_procentowy, $id_wyniku]);

    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    die("Błąd bazy danych: " . $e->getMessage());
}

// Dane do wyświetlenia
$stmt_test = $conn->prepare("SELECT nazwa_testu FROM testy WHERE id_testu = ?");
$stmt_test->execute([$id_testu]);
$test_info = $stmt_test->fetch();
$username = $_SESSION['lab14_username'] ?? 'Użytkownik';
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Podsumowanie Testu - <?php echo htmlspecialchars($test_info['nazwa_testu']); ?></title>
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
                <h2 class="mb-1">Podsumowanie Wyników 📝</h2>
                <h4 class="text-secondary"><?php echo htmlspecialchars($test_info['nazwa_testu']); ?></h4>
                <p class="mb-0">Użytkownik: <strong><?php echo htmlspecialchars($username); ?></strong> | Data: <?php echo date('d.m.Y H:i'); ?></p>
            </div>

            <hr class="border-secondary mb-4">

            <div class="row align-items-center mb-4">
                <div class="col-md-6 text-center">
                    <div class="display-2 fw-bold <?php echo $wynik_procentowy >= 50 ? 'text-success' : 'text-danger'; ?>">
                        <?php echo round($wynik_procentowy); ?>%
                    </div>
                    <p class="lead">Twój wynik końcowy</p>
                </div>
                <div class="col-md-6">
                    <div class="alert <?php echo $wynik_procentowy >= 50 ? 'alert-success bg-dark border-success text-success' : 'alert-danger bg-dark border-danger text-danger'; ?>">
                        <h5 class="alert-heading"><?php echo $wynik_procentowy >= 50 ? 'Gratulacje! 🎉' : 'Niestety... 😕'; ?></h5>
                        <p class="mb-0">
                            Poprawne odpowiedzi: <strong><?php echo $correct_answers_count; ?></strong> z <strong><?php echo $total_questions; ?></strong>.<br>
                            Status: <strong><?php echo $wynik_procentowy >= 50 ? 'ZALICZONE' : 'NIEZALICZONE'; ?></strong>
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
                <button id="downloadPdf" class="btn btn-primary px-4">
                    👁️ Podgląd PDF
                </button>
                <a href="index.php" class="btn btn-accent px-4">
                    Powrót do Dashboardu
                </a>
            </div>
        </div>
    </div>

    <!-- Biblioteki do generowania PDF po stronie klienta -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script>
        document.getElementById('downloadPdf').addEventListener('click', function () {
            const { jsPDF } = window.jspdf;
            const element = document.querySelector('.card');
            const buttons = document.querySelector('.no-print');
            
            // Ukryj przyciski na czas generowania
            buttons.style.visibility = 'hidden';

            html2canvas(element, {
                scale: 2, 
                useCORS: true,
                backgroundColor: "#212529"
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                
                pdf.addImage(imgData, 'PNG', 0, 0, pdfWidth, pdfHeight);
                
                // Otwarcie PDF w nowej karcie
                const blob = pdf.output('bloburl');
                window.open(blob, '_blank');
                
                buttons.style.visibility = 'visible';
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
