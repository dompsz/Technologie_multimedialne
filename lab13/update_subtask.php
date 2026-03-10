<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab13_user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    exit("Zaloguj się.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $idpz = (int)$_POST['idpz'];
    $stan = (int)$_POST['stan'];
    $user_id = $_SESSION['lab13_user_id'];

    // Weryfikacja: Tylko manager zadania głównego LUB przypisany wykonawca może zmienić stan
    // PDF mówi: "Project Manager przypisuje wykonawców do podzadań i określa ich stan realizacji za pomocą suwaka"
    // Ale też "Pracownicy mogą być jednocześnie managerami swoich zadań i wykonawcami zadań innych osób".
    // Pozwólmy obu.
    
    try {
        $stmt = $conn->prepare("
            SELECT p.idpz, z.idp as manager_id, p.idp as wykonawca_id 
            FROM podzadanie p 
            JOIN zadanie z ON p.idz = z.idz 
            WHERE p.idpz = ?
        ");
        $stmt->execute([$idpz]);
        $data = $stmt->fetch();

        if ($data && ($data['manager_id'] == $user_id || $data['wykonawca_id'] == $user_id)) {
            $stmt_upd = $conn->prepare("UPDATE podzadanie SET stan = ? WHERE idpz = ?");
            $stmt_upd->execute([$stan, $idpz]);
            
            // Możemy zwrócić sukces dla fetch/ajax lub przekierować
            if (isset($_POST['ajax'])) {
                echo json_encode(['success' => true, 'stan' => $stan]);
            } else {
                header("Location: dashboard.php?msg=stan_zaktualizowany");
            }
        } else {
            die("Brak uprawnień.");
        }
    } catch (PDOException $e) {
        die("Błąd: " . $e->getMessage());
    }
}
?>
