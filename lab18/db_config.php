<?php
// Konfiguracja Bazy Danych - Laboratorium 18 (Galeria)
require_once __DIR__ . '/../load_env.php';

// Próba wczytania .env z dwóch lokalizacji
$env_path_local = __DIR__ . '/.env';
$env_path_parent = __DIR__ . '/../.env';
$env_loaded = false;

if (file_exists($env_path_local)) {
    $env_loaded = loadEnv($env_path_local);
    $used_path = $env_path_local;
} elseif (file_exists($env_path_parent)) {
    $env_loaded = loadEnv($env_path_parent);
    $used_path = $env_path_parent;
}

// Pobieranie wartości z obsługą różnych tablic (niektóre hostingi blokują $_ENV)
$host = $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? 'localhost';
$db_pass = $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '';

$db_name = 'pszczolk_z18'; 
$db_user = 'pszczolk_z18';   

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "Błąd połączenia z bazą danych.\n";
    echo "Baza: $db_name\n";
    echo "Użytkownik: $db_user\n";
    echo "Host: $host\n";
    echo "Plik .env wczytany: " . ($env_loaded ? "TAK" : "NIE") . "\n";
    if ($env_loaded) echo "Użyta ścieżka: $used_path\n";
    echo "Komunikat: " . $e->getMessage();
    exit;
}
?>
