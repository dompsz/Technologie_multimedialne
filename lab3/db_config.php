<?php
// Lab 3 Database Configuration
$host = "localhost";
$db_name = "pszczolkowski_lab3";
$db_user = "pszczolkowski_lab3_user";
$db_pass = "password";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Błąd połączenia z bazą danych.");
}
?>
