<?php
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['lab18_user_id'] ?? null;

    if (!$user_id) {
        die("Brak uprawnień.");
    }

    if ($action === 'add_gallery') {
        $nazwa = trim($_POST['nazwa_galerii']);
        $komercyjna = isset($_POST['czy_komercyjna']) ? 1 : 0;

        if (!empty($nazwa)) {
            $stmt = $conn->prepare("INSERT INTO galerie (idu, nazwa_galerii, czy_komercyjna) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $nazwa, $komercyjna]);
            header("Location: index.php?msg=gallery_added");
            exit();
        }
    }
    
    if ($action === 'add_photo') {
        $idg = (int)$_POST['idg'];
        $tytul = trim($_POST['tytul']);
        $opis = trim($_POST['opis']);
        
        if (isset($_FILES['plik']) && $_FILES['plik']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['plik']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            $upload_path = 'uploads/' . $filename;
            
            if (move_uploaded_file($_FILES['plik']['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("INSERT INTO zdjecia (idg, idu, tytul, opis, plik) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$idg, $user_id, $tytul, $opis, $filename]);
                header("Location: gallery.php?id=$idg&msg=photo_added");
                exit();
            }
        }
    }

    if ($action === 'add_comment') {
        $idz = (int)$_POST['idz'];
        $tresc = trim($_POST['tresc']);
        
        if (!empty($tresc)) {
            $stmt = $conn->prepare("INSERT INTO komentarze (idz, idu, tresc) VALUES (?, ?, ?)");
            $stmt->execute([$idz, $user_id, $tresc]);
            header("Location: photo.php?id=$idz&msg=comment_added");
            exit();
        }
    }

    if ($action === 'rate') {
        $idz = (int)$_POST['idz'];
        $ocena = (int)$_POST['ocena'];
        
        if ($ocena >= 1 && $ocena <= 5) {
            // UPSERT rating
            $stmt = $conn->prepare("INSERT INTO oceny (idz, idu, ocena) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE ocena = VALUES(ocena)");
            $stmt->execute([$idz, $user_id, $ocena]);
            header("Location: photo.php?id=$idz&msg=rated");
            exit();
        }
    }
}
header("Location: index.php");
exit();
?>
