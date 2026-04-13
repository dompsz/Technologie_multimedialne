<?php
// Skrypt do importowania pełnej listy cenzury z Gist (Lab 17)
require_once 'db_config.php';

echo "<h2>Importowanie bazy cenzury...</h2>";

// URL do wersji RAW Gista
$gist_url = "https://gist.githubusercontent.com/sylweriusz/8b41d76c9cfb49635eb37dc6af0ab257/raw/2622416d6f28688435d82088b901614777e584f2/vulgar_pl.php";

$content = file_get_contents($gist_url);

// Wyciągnięcie słów z formatu PHP array
// Zakładamy że format to: 'slowo', 'slowo2', ...
preg_match_all("/'([^']+)'/", $content, $matches);
$words = array_unique($matches[1]);

if (empty($words)) {
    die("Błąd: Nie udało się pobrać słów z Gist.");
}

$conn->beginTransaction();
try {
    $stmt = $conn->prepare("INSERT IGNORE INTO cenzura (slowo_zakazane, zamiennik) VALUES (?, '***')");
    $count = 0;
    foreach ($words as $word) {
        $stmt->execute([$word]);
        $count += $stmt->rowCount();
    }
    $conn->commit();
    echo "✓ Zaimportowano <b>$count</b> nowych słów do tabeli cenzury.<br>";
    echo "<a href='index.php'>Powrót do Forum</a>";
} catch (Exception $e) {
    $conn->rollBack();
    die("Błąd bazy danych: " . $e->getMessage());
}
?>
