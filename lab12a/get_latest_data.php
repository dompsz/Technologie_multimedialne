<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access denied']);
    exit();
}
header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT * FROM pomiary ORDER BY datetime DESC LIMIT 1");
    $latest = $stmt->fetch();
    
    if ($latest) {
        echo json_encode($latest);
    } else {
        echo json_encode(['error' => 'No data found']);
    }
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
