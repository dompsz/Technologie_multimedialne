<?php
// API dla aplikacji mobilnej - Lab 18
require_once 'db_config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Prosta autoryzacja loginem i hasłem w POST (dla uproszczenia labu)
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';
    $idg = (int)($_POST['idg'] ?? 0);
    $tytul = $_POST['tytul'] ?? 'Mobilne zdjęcie';
    $opis = $_POST['opis'] ?? 'Przesłano z aplikacji mobilnej';

    $stmt = $conn->prepare("SELECT idu, haslo FROM uzytkownicy WHERE login = ?");
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['haslo'])) {
        $user_id = $user['idu'];
        
        if (isset($_FILES['plik']) && $_FILES['plik']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['plik']['name'], PATHINFO_EXTENSION);
            $filename = 'mob_' . uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['plik']['tmp_name'], $upload_path)) {
                $stmt_ins = $conn->prepare("INSERT INTO zdjecia (idg, idu, tytul, opis, plik) VALUES (?, ?, ?, ?, ?)");
                $stmt_ins->execute([$idg, $user_id, $tytul, $opis, $filename]);
                
                echo json_encode(['success' => true, 'message' => 'Zdjęcie przesłane pomyślnie.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Błąd zapisu pliku.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Brak pliku lub błąd uploadu.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Błędne dane logowania.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Tylko POST jest obsługiwany.']);
}
?>
