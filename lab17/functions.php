<?php
// Funkcje pomocnicze Lab 17

/**
 * Filtruje treść posta (cenzura i usuwanie linków)
 */
function filterContent($text, $conn) {
    // 1. Usuwanie/Neutralizacja linków
    // Prosty regex do wykrywania linków http/https/www
    $pattern = '/(https?:\/\/[^\s]+|www\.[^\s]+)/i';
    $text = preg_replace($pattern, '[link usunięty ze względów bezpieczeństwa]', $text);

    // 2. Cenzura wulgaryzmów z bazy danych
    $stmt = $conn->query("SELECT slowo_zakazane, zamiennik FROM cenzura");
    $cenzura = $stmt->fetchAll();

    foreach ($cenzura as $c) {
        $slowo = preg_quote($c['slowo_zakazane'], '/');
        // case-insensitive replacement
        $text = preg_replace('/\b' . $slowo . '\b/i', $c['zamiennik'], $text);
    }

    return $text;
}

/**
 * Zwraca etykietę uprawnień
 */
function getRoleLabel($level) {
    switch ($level) {
        case 3: return '<span class="badge bg-danger">Administrator</span>';
        case 2: return '<span class="badge bg-warning text-dark">Moderator</span>';
        case 1: return '<span class="badge bg-primary">Użytkownik</span>';
        case 0: return '<span class="badge bg-secondary">Gość</span>';
        default: return 'Nieznany';
    }
}
?>
