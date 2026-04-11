<?php
require_once 'db_config.php';

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$query = trim($input['query'] ?? '');

if (empty($query)) {
    echo json_encode(['reply' => 'W czym mogę Ci dzisiaj pomóc?']);
    exit();
}

$response = "";
$found = 0;

// 1. Szukanie w słowniku bota
$stmt_slownik = $conn->query("SELECT * FROM slownik_bota");
$slownik = $stmt_slownik->fetchAll();

foreach ($slownik as $item) {
    $keys = explode(',', $item['pytanie_klucz']);
    foreach ($keys as $key) {
        if (stripos($query, trim($key)) !== false) {
            $response = $item['odpowiedz'];
            $found = 1;
            break 2;
        }
    }
}

// 2. Szukanie w podstronach (jeśli nie znaleziono w słowniku)
if (!$found) {
    $stmt_pages = $conn->prepare("SELECT tytul, tresc, slug FROM podstrony WHERE status = 'opublikowany' AND (tytul LIKE ? OR tresc LIKE ?)");
    $stmt_pages->execute(["%$query%", "%$query%"]);
    $page = $stmt_pages->fetch();

    if ($page) {
        $response = "Znalazłem coś na temat: <strong>" . htmlspecialchars($page['tytul']) . "</strong>.<br>" . 
                    strip_tags(mb_substr($page['tresc'], 0, 200)) . "... <br>" .
                    "<a href='view_page.php?slug=" . $page['slug'] . "' class='btn btn-sm btn-info mt-2'>Czytaj więcej</a>";
        $found = 1;
    }
}

if (!$found) {
    $response = "Przepraszam, nie znalazłem informacji na ten temat. Spróbuj zadać pytanie inaczej (np. 'kontakt', 'oferta').";
}

// 3. Logowanie zapytania
$stmt_log = $conn->prepare("INSERT INTO logi_bota (zapytanie_uzytkownika, czy_znaleziono_odp) VALUES (?, ?)");
$stmt_log->execute([$query, $found]);

echo json_encode(['reply' => $response]);
