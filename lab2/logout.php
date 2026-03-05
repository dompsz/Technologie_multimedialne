<?php
session_start();
unset($_SESSION['lab2_user_id']);
unset($_SESSION['lab2_username']);
header("Location: index.php");
exit();
?>
