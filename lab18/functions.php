<?php
// Funkcje pomocnicze Lab 18

/**
 * Filtruje treść (cenzura wulgaryzmów)
 */
function filterContent($text, $conn) {
    $stmt = $conn->query("SELECT slowo_zakazane, zamiennik FROM cenzura");
    $cenzura = $stmt->fetchAll();

    foreach ($cenzura as $c) {
        $slowo = preg_quote($c['slowo_zakazane'], '/');
        // Filtrujemy niezależnie od wielkości liter
        $text = preg_replace('/\b' . $slowo . '\b/i', $c['zamiennik'], $text);
    }

    return $text;
}

/**
 * Zwraca etykietę uprawnień
 */
function getRoleLabel($rola) {
    switch ($rola) {
        case 'admin': return '<span class="badge bg-danger">Administrator</span>';
        case 'user': return '<span class="badge bg-primary">Użytkownik</span>';
        default: return '<span class="badge bg-secondary">Gość</span>';
    }
}
?>
