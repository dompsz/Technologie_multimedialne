<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once 'db_config.php';
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_SESSION['lab20_user_id'] ?? null;
    $user_role = $_SESSION['lab20_role'] ?? 'guest';

    if (!$user_id && in_array($action, ['add_ad', 'edit_ad', 'delete_ad'])) {
        die("Brak uprawnień. Zaloguj się ponownie.");
    }

    try {
        if ($action === 'add_ad') {
            $idko = (int)$_POST['idko'];
            $tytul = trim($_POST['tytul']);
            $tresc = trim($_POST['tresc']);
            $cena = (float)$_POST['cena'];
            $lokalizacja_tekst = trim($_POST['lokalizacja_tekst']);
            $lat = (float)$_POST['lat'];
            $lng = (float)$_POST['lng'];

            if (!empty($tytul)) {
                $stmt = $conn->prepare("INSERT INTO ogloszenia (idko, idu, tytul, tresc, cena, lokalizacja_tekst, lat, lng) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$idko, $user_id, $tytul, $tresc, $cena, $lokalizacja_tekst, $lat, $lng]);
                $ido = $conn->lastInsertId();
                
                logAction($conn, $ido, $user_id, 'dodanie_ogloszenia');

                // Obsługa zdjęć
                if (isset($_FILES['zdjecia'])) {
                    $upload_dir = __DIR__ . '/uploads/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $files = $_FILES['zdjecia'];
                    $count = count($files['name']);

                    for ($i = 0; $i < $count; $i++) {
                        if ($files['error'][$i] === UPLOAD_ERR_OK) {
                            $ext = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                            
                            if (in_array($ext, $allowed)) {
                                $filename = uniqid('img_') . '_' . $i . '.' . $ext;
                                if (move_uploaded_file($files['tmp_name'][$i], $upload_dir . $filename)) {
                                    $stmt_img = $conn->prepare("INSERT INTO ogloszenia_zdjecia (ido, plik) VALUES (?, ?)");
                                    $stmt_img->execute([$ido, $filename]);
                                }
                            }
                        }
                    }
                }
                
                header("Location: category.php?id=$idko&msg=ad_added");
                exit();
            }
        }
        
        if ($action === 'edit_ad') {
            $ido = (int)$_POST['ido'];
            $tytul = trim($_POST['tytul']);
            $tresc = trim($_POST['tresc']);
            $cena = (float)$_POST['cena'];
            $lokalizacja_tekst = trim($_POST['lokalizacja_tekst']);

            // Sprawdzenie uprawnień (właściciel lub admin/moderator)
            $stmt_check = $conn->prepare("SELECT idu, idko FROM ogloszenia WHERE ido = ?");
            $stmt_check->execute([$ido]);
            $ad_info = $stmt_check->fetch();

            if ($ad_info && ($ad_info['idu'] == $user_id || $user_role === 'admin' || $user_role === 'moderator')) {
                $stmt = $conn->prepare("UPDATE ogloszenia SET tytul = ?, tresc = ?, cena = ?, lokalizacja_tekst = ? WHERE ido = ?");
                $stmt->execute([$tytul, $tresc, $cena, $lokalizacja_tekst, $ido]);
                
                logAction($conn, $ido, $user_id, 'edycja_ogloszenia');
                
                header("Location: ad.php?id=$ido&msg=updated");
                exit();
            } else {
                die("Brak uprawnień do edycji tego ogłoszenia.");
            }
        }

        if ($action === 'delete_ad') {
            $ido = (int)$_POST['ido'];

            $stmt_check = $conn->prepare("SELECT idu, idko FROM ogloszenia WHERE ido = ?");
            $stmt_check->execute([$ido]);
            $ad_info = $stmt_check->fetch();

            if ($ad_info && ($ad_info['idu'] == $user_id || $user_role === 'admin' || $user_role === 'moderator')) {
                logAction($conn, $ido, $user_id, 'usuniecie_ogloszenia');
                
                $stmt = $conn->prepare("DELETE FROM ogloszenia WHERE ido = ?");
                $stmt->execute([$ido]);
                
                header("Location: category.php?id=" . $ad_info['idko'] . "&msg=deleted");
                exit();
            } else {
                die("Brak uprawnień do usunięcia tego ogłoszenia.");
            }
        }

    } catch (PDOException $e) {
        die("Błąd bazy danych: " . $e->getMessage());
    }
}
header("Location: index.php");
exit();
?>
