<?php
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit();
}
header('Content-Type: application/json');

try {
    $stmt = $conn->query("SELECT p.*, s.terrorysta, s.pozar, s.powodz, s.wiatrak 
                          FROM pomiary p 
                          LEFT JOIN statusy s ON p.id = s.pomiar_id 
                          ORDER BY p.datetime DESC LIMIT 20");
    $data = $stmt->fetchAll();
    echo json_encode(array_reverse($data));
} catch(PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
