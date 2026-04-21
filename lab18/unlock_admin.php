<?php
require_once 'db_config.php';

echo "<h2>System Ratunkowy Lab 18</h2>";

try {
    $new_hash = password_hash('admin', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE uzytkownicy SET haslo = ? WHERE login = 'admin'");
    $stmt->execute([$new_hash]);
    
    if ($stmt->rowCount() > 0) {
        echo "✓ Hasło dla konta 'admin' zostało zresetowane na 'admin'.<br>";
    } else {
        $stmt_ins = $conn->prepare("INSERT INTO uzytkownicy (login, haslo, rola) VALUES ('admin', ?, 'admin')");
        $stmt_ins->execute([$new_hash]);
        echo "✓ Utworzono nowe konto 'admin' z hasłem 'admin'.<br>";
    }

    echo "<br><b>Gotowe! Możesz teraz przejść do <a href='login.php'>logowania</a>.</b>";

} catch (PDOException $e) {
    echo "Błąd: " . $e->getMessage();
}
?>
