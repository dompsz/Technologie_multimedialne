<?php
// Autonomiczna Konfiguracja Bazy Danych - Laboratorium 18
$host = 'localhost';
$db_name = 'pszczolk_z18';
$db_user = 'pszczolk_z18';
$db_pass = '';

// Ręczne wczytanie lokalnego pliku .env
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if ($key === 'DB_HOST') $host = $value;
        if ($key === 'DB_PASS') $db_pass = $value;
    }
}

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Błąd połączenia: " . $e->getMessage());
}
?>
