<?php
// Konfiguracja Bazy Danych - Laboratorium 12b
require_once __DIR__ . '/../load_env.php';

// Wczytuje .env z tego samego folderu co db_config.php
loadEnv(__DIR__ . '/.env');

$host_raw = $_ENV['DB_HOST'] ?? 'srv58.mikr.us:20125';
$db_name = $_ENV['DB_NAME'] ?? 'db_dominik'; 
$db_user = $_ENV['DB_USER'] ?? 'dominik';   
$db_pass = $_ENV['DB_PASS'] ?? 'D2vsGrhrZFD3_';

// Rozbicie hosta i portu
if (strpos($host_raw, ':') !== false) {
    list($host, $port) = explode(':', $host_raw);
} else {
    $host = $host_raw;
    $port = '3306';
}

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";
    $conn = new PDO($dsn, $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Błąd połączenia Lab 12b!<br>DSN: mysql:host=$host;port=$port;dbname=$db_name<br>User: $db_user<br>Error: " . $e->getMessage());
}
?>
