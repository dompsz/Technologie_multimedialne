<?php
// Funkcje pomocnicze Lab 17

/**
 * Filtruje treść posta (cenzura i usuwanie linków)
 * Zwraca tablicę: ['text' => przefiltrowany_tekst, 'profanity_count' => liczba_wykrytych, 'malicious_found' => boolean]
 */
function filterContent($text, $conn) {
    $profanity_count = 0;
    $malicious_found = false;

    // 1. Wykrywanie i Neutralizacja linków + Sprawdzanie niebezpiecznych domen
    $pattern = '/(?:https?:\/\/|www\.)([a-z0-9.-]+\.[a-z]{2,})/i';
    
    // Znajdź wszystkie domeny w tekście
    if (preg_match_all($pattern, $text, $matches)) {
        $found_domains = array_unique($matches[1]);
        if (!empty($found_domains)) {
            // Przygotuj zapytanie do sprawdzenia wielu domen naraz
            $placeholders = implode(',', array_fill(0, count($found_domains), '?'));
            $stmt = $conn->prepare("SELECT domena FROM niebezpieczne_linki WHERE domena IN ($placeholders)");
            $stmt->execute($found_domains);
            $bad_domains = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($bad_domains)) {
                $malicious_found = true;
            }
        }
    }

    // Neutralizacja wszystkich linków
    $text = preg_replace('/(https?:\/\/[^\s]+|www\.[^\s]+)/i', '[link usunięty]', $text);

    // 2. Cenzura wulgaryzmów z bazy danych
    $stmt = $conn->query("SELECT slowo_zakazane, zamiennik FROM cenzura");
    $cenzura = $stmt->fetchAll();

    foreach ($cenzura as $c) {
        $slowo = preg_quote($c['slowo_zakazane'], '/');
        preg_match_all('/\b' . $slowo . '\b/i', $text, $matches);
        $profanity_count += count($matches[0]);
        $text = preg_replace('/\b' . $slowo . '\b/i', $c['zamiennik'], $text);
    }

    return [
        'text' => $text, 
        'profanity_count' => $profanity_count, 
        'malicious_found' => $malicious_found
    ];
}

/**
 * Sprawdza i nakłada automatyczne kary
 */
function handleProfanityOffense($user_id, $profanity_count, $conn, $is_malicious = false) {
    if ($profanity_count <= 0 && !$is_malicious) return false;

    // Pobierz aktualny stan użytkownika
    $stmt = $conn->prepare("SELECT liczba_ostrzezen FROM uzytkownicy WHERE idu = ?");
    $stmt->execute([$user_id]);
    $current_offenses = $stmt->fetchColumn();

    $new_offenses = $current_offenses + 1;
    $ban_until = null;
    $reason = "Automatyczna blokada (Incydent #$new_offenses)";

    if ($is_malicious) {
        // Natychmiastowy PERM za niebezpieczne linki
        $ban_until = '2037-12-31 23:59:59';
        $reason = "STAŁA BLOKADA: Próba udostępnienia niebezpiecznych linków (Phishing/Malware)";
    } elseif ($new_offenses == 1) {
        $ban_until = date('Y-m-d H:i:s', strtotime('+1 minute'));
    } elseif ($new_offenses == 2) {
        $ban_until = date('Y-m-d H:i:s', strtotime('+2 minutes'));
    } else {
        $ban_until = '2037-12-31 23:59:59';
        $reason = "STAŁA BLOKADA: Wielokrotne łamanie regulaminu";
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
