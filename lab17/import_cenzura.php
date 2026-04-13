<?php
// Skrypt do importowania bazy cenzury z lokalnego pliku wulgaryzmy.php (Lab 17)
require_once 'db_config.php';

$file_path = 'wulgaryzmy.php';

echo "<h2>Importowanie lokalnej bazy cenzury...</h2>";

if (!file_exists($file_path)) {
    die("Błąd: Nie znaleziono pliku <b>$file_path</b> w folderze lab17/. Upewnij się, że plik tam jest.");
}

// Dołączamy plik, który definiuje zmienną $wulgaryzmy_pl
include $file_path;

if (!isset($wulgaryzmy_pl) || !is_array($wulgaryzmy_pl)) {
    die("Błąd: Plik $file_path nie definiuje poprawnej tablicy \$wulgaryzmy_pl.");
}

// Usuwamy duplikaty i puste wpisy dla pewności
$words = array_unique(array_filter($wulgaryzmy_pl));

$conn->beginTransaction();
try {
    // Używamy INSERT IGNORE, aby nie przejmować się duplikatami przy ponownym eksporcie
    $stmt = $conn->prepare("INSERT IGNORE INTO cenzura (slowo_zakazane, zamiennik) VALUES (?, '***')");
    $count = 0;
    foreach ($words as $word) {
        $stmt->execute([trim($word)]);
        $count += $stmt->rowCount();
    }
    $conn->commit();
    echo "✓ Sukces! Zaimportowano <b>$count</b> nowych słów z lokalnego pliku <b>$file_path</b>.<br>";
    echo "<a href='index.php'>Powrót do Forum</a>";
} catch (Exception $e) {
    $conn->rollBack();
    die("Błąd bazy danych: " . $e->getMessage());
}
?>
