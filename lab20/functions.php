<?php
// Funkcje pomocnicze Lab 20

/**
 * Zwraca etykietę uprawnień
 */
function getRoleLabel($rola) {
    switch ($rola) {
        case 'admin': return '<span class="badge bg-danger">Administrator</span>';
        case 'moderator': return '<span class="badge bg-warning text-dark">Moderator</span>';
        case 'zalogowany': return '<span class="badge bg-primary">Użytkownik</span>';
        default: return '<span class="badge bg-secondary">Gość</span>';
    }
}

/**
 * Loguje akcję do bazy danych
 */
function logAction($conn, $ido, $idu, $akcja) {
    $stmt = $conn->prepare("INSERT INTO logi_ogloszen (ido, idu, akcja) VALUES (?, ?, ?)");
    $stmt->execute([$ido, $idu, $akcja]);
}
?>
