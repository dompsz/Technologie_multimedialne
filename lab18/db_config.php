<?php
// Konfiguracja Bazy Danych - Laboratorium 18 (Galeria)
require_once __DIR__ . '/../load_env.php';

// Wczytuje .env z katalogu nadrzędnego (hosting)
$env_loaded = loadEnv(__DIR__ . '/../.env');

$host = $_ENV['DB_HOST'] ?? 'localhost';
$db_name = 'pszczolk_z18'; 
$db_user = 'pszczolk_z18';   
$db_pass = $_ENV['DB_PASS'] ?? '';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    // Jeśli połączenie się nie uda, wyświetlamy jasny komunikat dla dewelopera
    header('Content-Type: text/plain; charset=utf-8');
    echo "Błąd połączenia z bazą danych.\n";
    echo "Baza: $db_name\n";
    echo "Użytkownik: $db_user\n";
    echo "Plik .env wczytany: " . ($env_loaded ? "TAK" : "NIE") . "\n";
    echo "Komunikat: " . $e->getMessage();
    exit;
}
?>
