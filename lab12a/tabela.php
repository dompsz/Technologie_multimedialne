<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header("Location: index.php");
    exit();
}

try {
    // Pobranie danych pomiarowych z bazy
    $stmt = $conn->query("SELECT * FROM pomiary ORDER BY datetime DESC");
    $pomiary = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Błąd pobierania danych: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 12a - Tabela Pomiarów</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .container { margin-top: 50px; }
        .table-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-light">

<div class="container">
    <div class="nav-back mb-3">
        <a href="formularz.php" class="btn btn-secondary">← Powrót do formularza</a>
        <a href="wykres.php" class="btn btn-primary">Pokaż Wykresy</a>
        <a href="scada.php" class="btn btn-dark">Wizualizacja SCADA</a>
    </div>

    <div class="table-container">
        <h2 class="mb-4">Historia Pomiarów</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>x1 [V]</th>
                        <th>x2 [V]</th>
                        <th>x3 [V]</th>
                        <th>x4 [V]</th>
                        <th>x5 [V]</th>
                        <th>Data i Godzina</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pomiary)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Brak danych w bazie.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($pomiary as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo number_format($row['x1'], 2); ?></td>
                            <td><?php echo number_format($row['x2'], 2); ?></td>
                            <td><?php echo number_format($row['x3'], 2); ?></td>
                            <td><?php echo number_format($row['x4'], 2); ?></td>
                            <td><?php echo number_format($row['x5'], 2); ?></td>
                            <td><?php echo $row['datetime']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
