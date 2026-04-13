<?php
require_once 'db_config.php';

if (!file_exists('hole_cert.txt')) {
    die("Błąd: Nie znaleziono pliku hole_cert.txt");
}

echo "<h2>Importowanie niebezpiecznych domen...</h2>";

$file = fopen('hole_cert.txt', 'r');
$conn->beginTransaction();

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO niebezpieczne_linki (domena) VALUES (?)");
    $count = 0;
    
    while (($line = fgets($file)) !== false) {
        $domena = trim($line);
        if (!empty($domena)) {
            $stmt->execute([$domena]);
            $count++;
        }
    }
    
    $conn->commit();
    fclose($file);
    echo "✓ Sukces! Zaimportowano <b>$count</b> domen.<br>";
    echo "<a href='index.php'>Powrót do Forum</a>";
} catch (Exception $e) {
    $conn->rollBack();
    die("Błąd: " . $e->getMessage());
}
?>