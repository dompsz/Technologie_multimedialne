<?php
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 12a - Formularz Pomiarowy</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .container { margin-top: 50px; max-width: 600px; }
        .card { padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .nav-back { margin-bottom: 20px; }
        .form-control {
            background-color: #2c2c2c;
            border: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .form-control:focus {
            background-color: #333;
            color: #fff;
            border-color: var(--accent-color);
            box-shadow: none;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="nav-back">
        <a href="index.php" class="btn btn-secondary w-auto">← Powrót</a>
    </div>

    <div class="card">
        <h2 class="mb-4 text-center">Wprowadzanie Pomiarów (x1-x5)</h2>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Pomiary zostały zapisane pomyślnie!</div>
        <?php endif; ?>

        <form action="add.php" method="POST">
            <div class="mb-3">
                <label for="x1" class="form-label">Czujnik x1:</label>
                <input type="number" step="0.01" class="form-control" id="x1" name="x1" required>
            </div>
            <div class="mb-3">
                <label for="x2" class="form-label">Czujnik x2:</label>
                <input type="number" step="0.01" class="form-control" id="x2" name="x2" required>
            </div>
            <div class="mb-3">
                <label for="x3" class="form-label">Czujnik x3:</label>
                <input type="number" step="0.01" class="form-control" id="x3" name="x3" required>
            </div>
            <div class="mb-3">
                <label for="x4" class="form-label">Czujnik x4:</label>
                <input type="number" step="0.01" class="form-control" id="x4" name="x4" required>
            </div>
            <div class="mb-3">
                <label for="x5" class="form-label">Czujnik x5:</label>
                <input type="number" step="0.01" class="form-control" id="x5" name="x5" required>
            </div>
            
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Zapisz Pomiar</button>
                <a href="tabela.php" class="btn btn-outline-info">Zobacz Tabelę Wyników</a>
                <a href="scada.php" class="btn btn-outline-light">Wizualizacja SCADA</a>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
