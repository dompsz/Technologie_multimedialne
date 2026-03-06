<?php
// add.php - Obsługa zapisu pomiarów do bazy danych
require_once 'db_config.php';
session_start();
if(!isset($_SESSION['lab12a_user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Walidacja danych wejściowych
    $x1 = filter_input(INPUT_POST, 'x1', FILTER_VALIDATE_FLOAT);
    $x2 = filter_input(INPUT_POST, 'x2', FILTER_VALIDATE_FLOAT);
    $x3 = filter_input(INPUT_POST, 'x3', FILTER_VALIDATE_FLOAT);
    $x4 = filter_input(INPUT_POST, 'x4', FILTER_VALIDATE_FLOAT);
    $x5 = filter_input(INPUT_POST, 'x5', FILTER_VALIDATE_FLOAT);

    // Sprawdzenie czy wszystkie dane są poprawne
    if ($x1 === false || $x2 === false || $x3 === false || $x4 === false || $x5 === false) {
        die("Błąd: Nieprawidłowe dane wejściowe. Proszę podać liczby.");
    }

    try {
        $stmt = $conn->prepare("INSERT INTO pomiary (x1, x2, x3, x4, x5) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$x1, $x2, $x3, $x4, $x5]);
        
        // Przekierowanie z powrotem do formularza z komunikatem o sukcesie
        header("Location: formularz.php?success=1");
        exit;
    } catch(PDOException $e) {
        die("Błąd zapisu danych: " . $e->getMessage());
    }
} else {
    header("Location: formularz.php");
    exit;
}
?>
