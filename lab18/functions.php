<?php
// Funkcje pomocnicze Lab 18

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
