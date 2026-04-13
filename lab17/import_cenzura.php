<?php
// Poprawiony skrypt do importowania bazy cenzury (Lab 17)
require_once 'db_config.php';

echo "<h2>Importowanie bazy cenzury (Poprawione)...</h2>";

// URL do wersji RAW (zawsze najnowsza wersja)
$gist_url = "https://gist.githubusercontent.com/sylweriusz/8b41d76c9cfb49635eb37dc6af0ab257/raw/vulgar_pl.php";

/**
 * Bezpieczne pobieranie treści za pomocą CURL
 */
function fetchUrl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Omijanie problemów z certyfikatami na localhost
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

$content = fetchUrl($gist_url);

if (!$content) {
    die("Błąd: Nie udało się połączyć z GitHubem. Sprawdź połączenie internetowe.");
}

// Wyciągnięcie słów z formatu PHP array (obsługa cudzysłowów i apostrofów)
preg_match_all("/['\"]([^'\"]+)['\"]/", $content, $matches);
$words = array_unique($matches[1]);

// Usuwamy elementy które nie są słowami (np. 'vulgar_pl', 'array')
$blacklist = ['vulgar_pl', 'array', 'utf-8', 'php'];
$words = array_filter($words, function($w) use ($blacklist) {
    return !in_array(strtolower($w), $blacklist) && strlen($w) > 2;
});

if (empty($words)) {
    echo "<p>Treść pobrana z URL:</p><pre>" . htmlspecialchars(substr($content, 0, 500)) . "...</pre>";
    die("Błąd: Nie znaleziono słów w pobranej treści. Sprawdź format pliku na Gist.");
}

$conn->beginTransaction();
try {
    // Czyścimy tabelę przed nowym importem (opcjonalnie, lub używamy INSERT IGNORE)
    $stmt = $conn->prepare("INSERT IGNORE INTO cenzura (slowo_zakazane, zamiennik) VALUES (?, '***')");
    $count = 0;
    foreach ($words as $word) {
        $stmt->execute([trim($word)]);
        $count += $stmt->rowCount();
    }
    $conn->commit();
    echo "✓ Sukces! Zaimportowano <b>$count</b> nowych słów do bazy.<br>";
    echo "<a href='index.php'>Powrót do Forum</a>";
} catch (Exception $e) {
    $conn->rollBack();
    die("Błąd bazy danych: " . $e->getMessage());
}
?>
