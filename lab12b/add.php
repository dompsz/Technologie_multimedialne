<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12b_user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $v0 = filter_input(INPUT_POST, 'v0', FILTER_VALIDATE_FLOAT);
    $v1 = filter_input(INPUT_POST, 'v1', FILTER_VALIDATE_FLOAT);
    $v2 = filter_input(INPUT_POST, 'v2', FILTER_VALIDATE_FLOAT);
    $v3 = filter_input(INPUT_POST, 'v3', FILTER_VALIDATE_FLOAT);
    $v4 = filter_input(INPUT_POST, 'v4', FILTER_VALIDATE_FLOAT);
    $v5 = filter_input(INPUT_POST, 'v5', FILTER_VALIDATE_FLOAT);
    
    $terrorysta = isset($_POST['terrorysta']) ? 1 : 0;
    $pozar = $_POST['pozar'] ?? 'brak';
    $powodz = $_POST['powodz'] ?? 'brak';
    $wiatrak = $_POST['wiatrak'] ?? 'wyłączony';

    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("INSERT INTO pomiary (v0, v1, v2, v3, v4, v5) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$v0, $v1, $v2, $v3, $v4, $v5]);
        $pomiar_id = $conn->lastInsertId();
        
        $stmt2 = $conn->prepare("INSERT INTO statusy (pomiar_id, terrorysta, pozar, powodz, wiatrak) VALUES (?, ?, ?, ?, ?)");
        $stmt2->execute([$pomiar_id, $terrorysta, $pozar, $powodz, $wiatrak]);
        
        $conn->commit();
        header("Location: formularz.php?success=1");
    } catch(PDOException $e) {
        $conn->rollBack();
        die("Błąd zapisu: " . $e->getMessage());
    }
}
?>
