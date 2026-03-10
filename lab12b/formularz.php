<?php
session_start();
if(!isset($_SESSION['lab12b_user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 12b - Formularz Pomiarowy</title>
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
        <h2 class="mb-4 text-center">Wprowadzanie Pomiarów (v0-v5)</h2>
        
        <?php if(isset($_GET['success'])): ?>
            <div class="alert alert-success">Pomiary zostały zapisane pomyślnie!</div>
        <?php endif; ?>

        <form action="add.php" method="POST">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="v0" class="form-label">Czujnik v0:</label>
                    <input type="number" step="0.01" class="form-control" id="v0" name="v0" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="v1" class="form-label">Czujnik v1:</label>
                    <input type="number" step="0.01" class="form-control" id="v1" name="v1" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="v2" class="form-label">Czujnik v2:</label>
                    <input type="number" step="0.01" class="form-control" id="v2" name="v2" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="v3" class="form-label">Czujnik v3:</label>
                    <input type="number" step="0.01" class="form-control" id="v3" name="v3" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="v4" class="form-label">Czujnik v4:</label>
                    <input type="number" step="0.01" class="form-control" id="v4" name="v4" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="v5" class="form-label">Czujnik v5:</label>
                    <input type="number" step="0.01" class="form-control" id="v5" name="v5" required>
                </div>
            </div>
            
            <div class="d-grid gap-2 mt-3">
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
