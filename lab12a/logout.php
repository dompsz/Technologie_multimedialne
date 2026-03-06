<?php
session_start();
unset($_SESSION['lab12a_user_id']);
unset($_SESSION['lab12a_username']);
header("Location: index.php");
exit();
?>
