<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab14_user_id'])) {
    header("Location: login.php");
    exit();
}

$id_testu = $_GET['id'] ?? 1;

// Pobierz informacje o teście
$stmt = $conn->prepare("SELECT * FROM testy WHERE id_testu = ?");
$stmt->execute([$id_testu]);
$test = $stmt->fetch();

if (!$test) {
    die("Brak testu o podanym ID.");
}

// Pobierz pytania wraz z odpowiedziami
$stmt_pytania = $conn->prepare("SELECT * FROM pytania WHERE id_testu = ?");
$stmt_pytania->execute([$id_testu]);
$pytania = $stmt_pytania->fetchAll();

$czas_trwania = $test['czas_trwania']; // w sekundach
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Test: <?php echo htmlspecialchars($test['nazwa_testu']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        #timer { font-size: 1.5rem; font-weight: bold; position: fixed; top: 10px; right: 10px; background: rgba(0,0,0,0.8); padding: 10px; border-radius: 8px; border: 2px solid var(--accent-color); z-index: 1000; color: #fff; }
        .question-card { background: var(--card-bg); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; margin-bottom: 20px; color: #fff !important; }
        .form-check-input:checked { background-color: var(--accent-color); border-color: var(--accent-color); }
        .form-check-label { color: #eee !important; cursor: pointer; }
        .text-secondary { color: #bbb !important; }
        h2, h5 { color: #fff !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <div id="timer">Czas: <span id="time-left"><?php echo floor($czas_trwania / 60); ?>:<?php echo sprintf('%02d', $czas_trwania % 60); ?></span></div>

    <div class="container mt-5">
        <h2 class="mb-4">Test: <?php echo htmlspecialchars($test['nazwa_testu']); ?></h2>
        <p class="text-secondary mb-5">Powodzenia! System automatycznie prześle Twoje odpowiedzi po upływie czasu.</p>

        <form id="test-form" action="submit_test.php" method="POST">
            <input type="hidden" name="id_testu" value="<?php echo $id_testu; ?>">
            
            <?php foreach ($pytania as $i => $p): ?>
                <div class="question-card">
                    <h5><?php echo ($i+1); ?>. <?php echo htmlspecialchars($p['tresc_pytania']); ?></h5>
                    <hr class="border-secondary">
                    <?php
                        $stmt_odp = $conn->prepare("SELECT * FROM odpowiedzi WHERE id_pytania = ?");
                        $stmt_odp->execute([$p['id_pytania']]);
                        $odpowiedzi = $stmt_odp->fetchAll();
                        
                        foreach ($odpowiedzi as $o):
                    ?>
                        <div class="form-check my-2">
                            <input class="form-check-input" type="radio" 
                                   name="pytanie_<?php echo $p['id_pytania']; ?>" 
                                   id="odp_<?php echo $o['id_odpowiedzi']; ?>" 
                                   value="<?php echo $o['id_odpowiedzi']; ?>">
                            <label class="form-check-label" for="odp_<?php echo $o['id_odpowiedzi']; ?>">
                                <?php echo htmlspecialchars($o['tresc_odpowiedzi']); ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <div class="text-center my-5">
                <button type="submit" class="btn btn-lg btn-danger px-5" id="btn-submit">KONIEC</button>
            </div>
        </form>
    </div>

    <script>
        let timeLeft = <?php echo $czas_trwania; ?>;
        const timerDisplay = document.getElementById('time-left');
        const testForm = document.getElementById('test-form');

        const countdown = setInterval(() => {
            timeLeft--;
            
            let minutes = Math.floor(timeLeft / 60);
            let seconds = timeLeft % 60;
            timerDisplay.textContent = minutes + ":" + (seconds < 10 ? '0' : '') + seconds;

            if (timeLeft <= 0) {
                clearInterval(countdown);
                alert("Czas minął! Test zostanie automatycznie wysłany.");
                testForm.submit();
            }
        }, 1000);

        // Zapobieganie przypadkowemu wyjściu
        window.onbeforeunload = function() {
            if (timeLeft > 0) {
                return "Czy na pewno chcesz opuścić test? Twoje postępy mogą zostać utracone.";
            }
        };

        testForm.onsubmit = () => {
            window.onbeforeunload = null;
        };
    </script>
</body>
</html>
