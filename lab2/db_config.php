<?php
// Lab 2 Database Configuration
$host = "localhost";
$db_name = "pszczolkowski_lab2";
$db_user = "pszczolkowski_lab2_user";
$db_pass = "password";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Błąd połączenia z bazą danych.");
}
?>
