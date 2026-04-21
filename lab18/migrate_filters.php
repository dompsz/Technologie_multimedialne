<?php
require_once 'db_config.php';
try {
    $conn->exec("ALTER TABLE zdjecia ADD COLUMN filtr VARCHAR(50) DEFAULT 'none'");
    echo "Sukces: Dodano kolumnę 'filtr' do tabeli 'zdjecia'.";
} catch (PDOException $e) {
    echo "Info: Kolumna prawdopodobnie już istnieje lub wystąpił błąd: " . $e->getMessage();
}
unlink(__FILE__); // Usuń ten skrypt po wykonaniu
?>
