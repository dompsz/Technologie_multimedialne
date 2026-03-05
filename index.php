<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratoria - Technologie Multimedialne</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 20px;
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
            color: #333;
            margin-bottom: 40px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .tile {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 30px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 200px;
        }
        .tile:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px rgba(0,0,0,0.15);
        }
        .tile h2 {
            margin: 0 0 10px 0;
            color: #007bff;
        }
        .tile p {
            color: #666;
            margin: 0;
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
        </div>
    </div>
</body>
</html>
