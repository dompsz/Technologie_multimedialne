<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab3_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab3_user_id'];

// Pobieranie lub tworzenie ustawień użytkownika
$stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->execute([$user_id]);
$settings = $stmt->fetch();

if (!$settings) {
    $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)")->execute([$user_id]);
    $settings = ['theme' => 'light', 'font_size' => 16];
}

// Obsługa zmiany motywu przez AJAX (lub formularz)
if (isset($_POST['toggle_theme'])) {
    $new_theme = ($settings['theme'] == 'light') ? 'dark' : 'light';
    $conn->prepare("UPDATE user_settings SET theme = ? WHERE user_id = ?")->execute([$new_theme, $user_id]);
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Interaktywny Panel - Lab 3</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body.dark-mode { background-color: #1a1a1a; color: #f4f4f4; }
        body.dark-mode .card { background-color: #2d2d2d; color: #fff; box-shadow: 0 4px 10px rgba(255,255,255,0.05); }
        body.dark-mode h2, body.dark-mode h3 { color: #fff; }
        
        .interaction-card { max-width: 900px; margin: 30px auto; padding: 30px; border-radius: 12px; }
        canvas { background: #eee; display: block; margin: 20px auto; border-radius: 8px; border: 1px solid #ccc; cursor: crosshair; }
        body.dark-mode canvas { background: #333; border-color: #444; }
        
        .controls { display: flex; justify-content: center; gap: 10px; margin-bottom: 20px; }
    </style>
</head>
<body class="<?php echo ($settings['theme'] == 'dark') ? 'dark-mode' : ''; ?>">
    <div class="nav-back"><a href="index.php">← Powrót do Lab 3</a></div>
    
    <div class="interaction-card card">
        <h2>Zaawansowana Interakcja i Efekty</h2>
        <p>Witaj w Laboratorium 3, gdzie skupiamy się na wizualnej stronie i interakcji.</p>
        
        <div class="controls">
            <form method="POST">
                <button type="submit" name="toggle_theme" class="btn">
                    Przełącz na motyw <?php echo ($settings['theme'] == 'light') ? 'Ciemny' : 'Jasny'; ?>
                </button>
            </form>
            <button class="btn" onclick="clearCanvas()" style="background:#6c757d">Wyczyść Canvas</button>
        </div>

        <h3>Interaktywny Szkicownik (Canvas JS)</h3>
        <canvas id="drawingCanvas" width="800" height="400"></canvas>
    </div>

    <script>
        const canvas = document.getElementById('drawingCanvas');
        const ctx = canvas.getContext('2d');
        let painting = false;

        function startPosition(e) {
            painting = true;
            draw(e);
        }

        function finishedPosition() {
            painting = false;
            ctx.beginTransaction();
        }

        function draw(e) {
            if (!painting) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;

            ctx.lineWidth = 5;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '<?php echo ($settings['theme'] == 'dark') ? "#007bff" : "#0056b3"; ?>';

            ctx.lineTo(x, y);
            ctx.stroke();
            ctx.beginPath();
            ctx.moveTo(x, y);
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        canvas.addEventListener('mousedown', startPosition);
        canvas.addEventListener('mouseup', finishedPosition);
        canvas.addEventListener('mousemove', draw);
    </script>
</body>
</html>
