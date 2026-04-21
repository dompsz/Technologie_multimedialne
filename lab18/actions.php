<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['lab18_user_id'] ?? null;

    if (!$user_id) {
        die("Brak uprawnień. Zaloguj się ponownie.");
    }

    try {
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
            $filtr = $_POST['filtr'] ?? 'none';
            
            if (isset($_FILES['plik'])) {
                if ($_FILES['plik']['error'] !== UPLOAD_ERR_OK) {
                    die("Błąd przesyłania pliku. Kod błędu: " . $_FILES['plik']['error']);
                }

                $ext = strtolower(pathinfo($_FILES['plik']['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (!in_array($ext, $allowed)) {
                    die("Niedozwolony format pliku. Dopuszczalne: " . implode(', ', $allowed));
                }

                $filename = uniqid() . '.' . $ext;
                $upload_dir = __DIR__ . '/uploads/';
                $upload_path = $upload_dir . $filename;
                
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES['plik']['tmp_name'], $upload_path)) {
                    $stmt = $conn->prepare("INSERT INTO zdjecia (idg, idu, tytul, opis, plik, filtr) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$idg, $user_id, $tytul, $opis, $filename, $filtr]);
                    header("Location: gallery.php?id=$idg&msg=photo_added");
                    exit();
                } else {
                    die("Nie udało się zapisać pliku na serwerze.");
                }
            }
        }

        if ($action === 'edit_photo') {
            $idz = (int)$_POST['idz'];
            $tytul = trim($_POST['tytul']);
            $opis = trim($_POST['opis']);
            $filtr = $_POST['filtr'] ?? 'none';

            // Sprawdzenie uprawnień (tylko autor lub admin)
            $stmt_check = $conn->prepare("SELECT idu, idg FROM zdjecia WHERE idz = ?");
            $stmt_check->execute([$idz]);
            $photo_info = $stmt_check->fetch();

            if ($photo_info && ($photo_info['idu'] == $user_id || $_SESSION['lab18_login'] === 'admin')) {
                $stmt = $conn->prepare("UPDATE zdjecia SET tytul = ?, opis = ?, filtr = ? WHERE idz = ?");
                $stmt->execute([$tytul, $opis, $filtr, $idz]);
                header("Location: photo.php?id=$idz&msg=updated");
                exit();
            } else {
                die("Brak uprawnień do edycji tego zdjęcia.");
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
                $stmt = $conn->prepare("INSERT INTO oceny (idz, idu, ocena) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE ocena = VALUES(ocena)");
                $stmt->execute([$idz, $user_id, $ocena]);
                header("Location: photo.php?id=$idz&msg=rated");
                exit();
            }
        }
    } catch (PDOException $e) {
        die("Błąd bazy danych: " . $e->getMessage());
    }
}
header("Location: index.php");
exit();
?>
