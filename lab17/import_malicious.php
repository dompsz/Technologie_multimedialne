<?php
require_once 'db_config.php';

if (!file_exists('hole_cert.txt')) {
    die("Błąd: Nie znaleziono pliku hole_cert.txt");
}

echo "<h2>Importowanie niebezpiecznych domen...</h2>";

$file = fopen('hole_cert.txt', 'r');
$count = 0;
$batch_size = 500;
$current_batch = 0;

try {
    $stmt = $conn->prepare("INSERT IGNORE INTO niebezpieczne_linki (domena) VALUES (?)");
    $conn->beginTransaction();
    
    while (($line = fgets($file)) !== false) {
        $domena = trim($line);
        if (!empty($domena)) {
            $stmt->execute([$domena]);
            $count++;
            $current_batch++;
            
            // Co 500 rekordów zatwierdzamy transakcję i otwieramy nową
            if ($current_batch >= $batch_size) {
                $conn->commit();
                $conn->beginTransaction();
                $current_batch = 0;
            }
        }
    }
    
    $conn->commit();
    fclose($file);
    echo "✓ Sukces! Zaimportowano/Zaktualizowano <b>$count</b> domen w bazie.<br>";
    echo "<a href='index.php'>Powrót do Forum</a>";
} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    die("Błąd: " . $e->getMessage());
}
?>