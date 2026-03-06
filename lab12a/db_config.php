<?php
// Konfiguracja Bazy Danych - Laboratorium 12a
require_once __DIR__ . '/../load_env.php';

// Wczytuje .env z tego samego folderu co db_config.php
loadEnv(__DIR__ . '/.env');

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = 'pszczolk_z12a'; 
$db_user = 'pszczolk_z12a';   
$db_pass = $_ENV['DB_PASS'] ?? '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Błąd połączenia Lab 12a: " . $e->getMessage());
}
?>
