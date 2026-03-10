<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab13_user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit("Zaloguj się.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idz = (int)$_POST['idz']; // ID zadania głównego
    $idp_wykonawca = (int)$_POST['idp_wykonawca']; // ID przypisanego pracownika
    $nazwa = trim($_POST['nazwa_podzadania']);
    $user_id = $_SESSION['lab13_user_id'];

    // Weryfikacja: Tylko manager zadania (osoba która je dodała) może dodawać podzadania
    $stmt_check = $conn->prepare("SELECT idp FROM zadanie WHERE idz = ?");
    $stmt_check->execute([$idz]);
    $zadanie = $stmt_check->fetch();

    if ($zadanie && $zadanie['idp'] == $user_id) {
        if (!empty($nazwa)) {
            try {
                $stmt = $conn->prepare("INSERT INTO podzadanie (idz, idp, nazwa_podzadania, stan) VALUES (?, ?, ?, 0)");
                $stmt->execute([$idz, $idp_wykonawca, $nazwa]);
                header("Location: dashboard.php?msg=podzadanie_dodane");
            } catch (PDOException $e) {
                die("Błąd: " . $e->getMessage());
            }
        } else {
            header("Location: dashboard.php?error=pusta_nazwa_pod");
        }
    } else {
        header("Location: dashboard.php?error=nie_jestes_managerem");
    }
}
?>
