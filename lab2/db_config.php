<?php
// Konfiguracja Bazy Danych - Laboratorium 2
require_once __DIR__ . '/../load_env.php';

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = 'pszczolk_z2'; 
$db_user = 'pszczolk_z2';   
$db_pass = $_ENV['DB_PASS'] ?? '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Błąd połączenia z bazą danych Laboratorium 2.");
}
?>
