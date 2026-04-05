<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$user_id = $_SESSION['lab16_user_id'];
$role = $_SESSION['lab16_role'];

if($id) {
    // Sprawdzenie czy użytkownik może usunąć
    $stmt = $conn->prepare("SELECT idu FROM podstrony WHERE idp = ?");
    $stmt->execute([$id]);
    $page = $stmt->fetch();

    if($page && ($role === 'admin' || $page['idu'] == $user_id)) {
        $del = $conn->prepare("DELETE FROM podstrony WHERE idp = ?");
        $del->execute([$id]);
        header("Location: dashboard.php?msg=deleted");
        exit();
    }
}

header("Location: dashboard.php");
exit();
?>
