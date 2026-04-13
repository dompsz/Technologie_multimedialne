<?php
// Funkcje pomocnicze Lab 17

/**
 * Filtruje treść posta (cenzura i usuwanie linków)
 * Zwraca tablicę: ['text' => przefiltrowany_tekst, 'profanity_count' => liczba_wykrytych]
 */
function filterContent($text, $conn) {
    $profanity_count = 0;

    // 1. Usuwanie/Neutralizacja linków
    $pattern = '/(https?:\/\/[^\s]+|www\.[^\s]+)/i';
    $text = preg_replace($pattern, '[link usunięty]', $text);

    // 2. Cenzura wulgaryzmów z bazy danych
    $stmt = $conn->query("SELECT slowo_zakazane, zamiennik FROM cenzura");
    $cenzura = $stmt->fetchAll();

    foreach ($cenzura as $c) {
        $slowo = preg_quote($c['slowo_zakazane'], '/');
        // Liczymy wystąpienia przed zamianą
        preg_match_all('/\b' . $slowo . '\b/i', $text, $matches);
        $profanity_count += count($matches[0]);
        
        $text = preg_replace('/\b' . $slowo . '\b/i', $c['zamiennik'], $text);
    }

    return ['text' => $text, 'profanity_count' => $profanity_count];
}

/**
 * Sprawdza i nakłada automatyczne kary za wulgaryzmy
 */
function handleProfanityOffense($user_id, $profanity_count, $conn) {
    if ($profanity_count <= 0) return false;

    // Pobierz aktualny stan użytkownika
    $stmt = $conn->prepare("SELECT liczba_ostrzezen FROM uzytkownicy WHERE idu = ?");
    $stmt->execute([$user_id]);
    $current_offenses = $stmt->fetchColumn();

    $new_offenses = $current_offenses + 1; // Liczymy to jako jeden incydent (nawet jeśli było wiele słów w jednym poście)
    
    $ban_until = null;
    $reason = "Automatyczna blokada za wulgaryzmy (Incydent #$new_offenses)";

    if ($new_offenses == 1) {
        $ban_until = date('Y-m-d H:i:s', strtotime('+1 minute'));
    } elseif ($new_offenses == 2) {
        $ban_until = date('Y-m-d H:i:s', strtotime('+2 minutes'));
    } else {
        // 3 i więcej incydentów = Perm (rok 2099)
        $ban_until = '2099-12-31 23:59:59';
        $reason = "STAŁA BLOKADA: Wielokrotne łamanie regulaminu (wulgaryzmy)";
    }

    $stmt_upd = $conn->prepare("UPDATE uzytkownicy SET liczba_ostrzezen = ?, ban_do = ?, powod_blokady = ? WHERE idu = ?");
    $stmt_upd->execute([$new_offenses, $ban_until, $reason, $user_id]);

    return [
        'new_offenses' => $new_offenses,
        'ban_until' => $ban_until,
        'reason' => $reason
    ];
}

/**
 * Sprawdza czy użytkownik jest obecnie zbanowany
 */
function isUserBanned($user_id, $conn) {
    $stmt = $conn->prepare("SELECT ban_do, powod_blokady FROM uzytkownicy WHERE idu = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user && $user['ban_do']) {
        if (strtotime($user['ban_do']) > time()) {
            return $user; // Zwracamy info o banie
        } else {
            // Ban minął - wyczyść w bazie (opcjonalnie)
            $conn->prepare("UPDATE uzytkownicy SET ban_do = NULL, powod_blokady = NULL WHERE idu = ?")->execute([$user_id]);
        }
    }
    return false;
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
