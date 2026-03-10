<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab13_user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit("Zaloguj się.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nazwa = trim($_POST['nazwa_zadania']);
    $idp = $_SESSION['lab13_user_id'];

    if (!empty($nazwa)) {
        try {
            $stmt = $conn->prepare("INSERT INTO zadanie (idp, nazwa_zadania) VALUES (?, ?)");
            $stmt->execute([$idp, $nazwa]);
            header("Location: dashboard.php?msg=zadanie_dodane");
        } catch (PDOException $e) {
            die("Błąd: " . $e->getMessage());
        }
    } else {
        header("Location: dashboard.php?error=pusta_nazwa");
    }
}
?>
