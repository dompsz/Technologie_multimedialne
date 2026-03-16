<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratoria - Technologie Multimedialne</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 1000px;
            width: 100%;
        }
        h1 {
            text-align: center;
            margin-bottom: 40px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .tile {
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.3);
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 200px;
        }
        .tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.5);
            border-color: var(--accent-color) !important;
        }
        .tile h2 {
            margin: 0 0 10px 0;
            color: var(--accent-color) !important;
        }
        .lab-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Wybierz Laboratorium</h1>
        <div class="grid">
            <a href="lab1/index.php" class="tile">
                <div class="lab-icon">📁</div>
                <h2>Laboratorium 1</h2>
                <p>Podstawowe technologie i struktura</p>
            </a>
            <a href="lab2/index.php" class="tile">
                <div class="lab-icon">📷</div>
                <h2>Laboratorium 2</h2>
                <p>Multimedia i Obrazy</p>
            </a>
            <a href="lab3/index.php" class="tile">
                <div class="lab-icon">🎨</div>
                <h2>Laboratorium 3</h2>
                <p>Zaawansowane efekty wizualne</p>
            </a>
            <a href="lab12a/index.php" class="tile">
                <div class="lab-icon">📊</div>
                <h2>Zadanie 12a</h2>
                <p>SCADA i MySQL</p>
            </a>
            <div class="tile" style="cursor: default; opacity: 0.8;">
                <div class="lab-icon">🔌</div>
                <h2>Zadanie 12b</h2>
                <p>Arduino i IoT<br>(zrobione na localhost)</p>
            </div>
            <a href="lab13/index.php" class="tile">
                <div class="lab-icon">📝</div>
                <h2>Zadanie 13</h2>
                <p>Aplikacja Todo</p>
            </a>
        </div>
    </div>
</body>
</html>
