<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header("Location: index.php");
    exit();
}

try {
    // Pobranie ostatnich 20 pomiarów do wykresu
    $stmt = $conn->query("SELECT * FROM pomiary ORDER BY datetime ASC LIMIT 20");
    $data = $stmt->fetchAll();
    
    $labels = [];
    $x1_vals = [];
    $x2_vals = [];
    $x3_vals = [];
    $x4_vals = [];
    $x5_vals = [];
    
    foreach ($data as $row) {
        $labels[] = substr($row['datetime'], 11, 8); // Sama godzina
        $x1_vals[] = $row['x1'];
        $x2_vals[] = $row['x2'];
        $x3_vals[] = $row['x3'];
        $x4_vals[] = $row['x4'];
        $x5_vals[] = $row['x5'];
    }
} catch(PDOException $e) {
    die("Błąd pobierania danych: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab 12a - Wykresy Pomiarów</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="nav-back mb-3">
        <a href="formularz.php" class="btn btn-secondary">← Powrót do formularza</a>
        <a href="tabela.php" class="btn btn-info">Zobacz Tabelę</a>
        <a href="scada.php" class="btn btn-dark">Wizualizacja SCADA</a>
    </div>

    <div class="card p-4 shadow">
        <h2 class="mb-4">Wykres Pomiarów x1 - x5</h2>
        <div style="height: 500px;">
            <canvas id="myChart"></canvas>
        </div>
    </div>
</div>

<script>
const ctx = document.getElementById('myChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [
            { label: 'x1 [V]', data: <?php echo json_encode($x1_vals); ?>, borderColor: 'rgba(255, 99, 132, 1)', tension: 0.1 },
            { label: 'x2 [V]', data: <?php echo json_encode($x2_vals); ?>, borderColor: 'rgba(54, 162, 235, 1)', tension: 0.1 },
            { label: 'x3 [V]', data: <?php echo json_encode($x3_vals); ?>, borderColor: 'rgba(75, 192, 192, 1)', tension: 0.1 },
            { label: 'x4 [V]', data: <?php echo json_encode($x4_vals); ?>, borderColor: 'rgba(153, 102, 255, 1)', tension: 0.1 },
            { label: 'x5 [V]', data: <?php echo json_encode($x5_vals); ?>, borderColor: 'rgba(255, 159, 64, 1)', tension: 0.1 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                title: { display: true, text: 'Napięcie [V]' }
            },
            x: {
                title: { display: true, text: 'Czas' }
            }
        }
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
