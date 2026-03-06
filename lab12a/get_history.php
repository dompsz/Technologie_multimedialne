<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}
header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT * FROM pomiary ORDER BY datetime DESC LIMIT 20");
    $data = $stmt->fetchAll();
    echo json_encode(array_reverse($data)); // Odwracamy dla wykresu (chronologicznie)
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
