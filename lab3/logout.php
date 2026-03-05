<?php
session_start();
unset($_SESSION['lab3_user_id']);
unset($_SESSION['lab3_username']);
header("Location: index.php");
exit();
?>
