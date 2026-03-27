<?php
require_once 'db_config.php';

echo "<h2>System Ratunkowy Lab 15</h2>";

try {
    // 1. Wyczyszczenie logów (opcjonalnie, Lab 15 nie ma tabeli 'logowanie' w tym samym formacie, ale ma logi_pracownikow)
    // $conn->exec("DELETE FROM logi_pracownikow");
    // echo "✓ Wyczyszczono tabelę logów pracowników.<br>";

    // 2. Wygenerowanie poprawnego hasha dla 'admin' i aktualizacja
    $new_hash = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE pracownicy SET haslo = ? WHERE nazwisko = 'admin'");
    $stmt->execute([$new_hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Hasło dla konta 'admin' zostało ustawione na 'admin'.<br>";
    } else {
        // Jeśli nie było konta admin, stwórz je
        $stmt_ins = $conn->prepare("INSERT INTO pracownicy (nazwisko, haslo, role) VALUES ('admin', ?, 'admin')");
        $stmt_ins->execute([$new_hash]);
        echo "✓ Utworzono nowe konto 'admin' z hasłem 'admin'.<br>";
    }

    echo "<br><b>Gotowe! Możesz teraz przejść do <a href='login.php'>logowania</a> (admin / admin jako pracownik).</b>";
    echo "<br><br><i>Po zalogowaniu zaleca się usunięcie tego pliku (unlock_admin.php) ze względów bezpieczeństwa.</i>";

} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?>
