<?php
// Lab 1 Database Configuration
$host = "localhost"; // Usually localhost on shared hosting
$db_name = "pszczolkowski_lab1"; // Placeholder name
$db_user = "pszczolkowski_lab1_user"; // Placeholder user
$db_pass = "password"; // Placeholder password

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // In production, we shouldn't show the error details to users
    // echo "Connection failed: " . $e->getMessage();
    die("Błąd połączenia z bazą danych.");
}
?>
