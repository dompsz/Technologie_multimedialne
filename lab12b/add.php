<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12b_user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $x1 = filter_input(INPUT_POST, 'x1', FILTER_VALIDATE_FLOAT);
    $x2 = filter_input(INPUT_POST, 'x2', FILTER_VALIDATE_FLOAT);
    $x3 = filter_input(INPUT_POST, 'x3', FILTER_VALIDATE_FLOAT);
    $x4 = filter_input(INPUT_POST, 'x4', FILTER_VALIDATE_FLOAT);
    $x5 = filter_input(INPUT_POST, 'x5', FILTER_VALIDATE_FLOAT);
    
    $terrorysta = isset($_POST['terrorysta']) ? 1 : 0;
    $pozar = $_POST['pozar'] ?? 'brak';
    $powodz = $_POST['powodz'] ?? 'brak';
    $wiatrak = $_POST['wiatrak'] ?? 'wyłączony';

    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("INSERT INTO pomiary (x1, x2, x3, x4, x5) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$x1, $x2, $x3, $x4, $x5]);
        $pomiar_id = $conn->lastInsertId();
        
        $stmt2 = $conn->prepare("INSERT INTO statusy (pomiar_id, terrorysta, pozar, powodz, wiatrak) VALUES (?, ?, ?, ?, ?)");
        $stmt2->execute([$pomiar_id, $terrorysta, $pozar, $powodz, $wiatrak]);
        
        $conn->commit();
        echo "success";
    } catch(PDOException $e) {
        $conn->rollBack();
        die("Błąd zapisu: " . $e->getMessage());
    }
}
?>
