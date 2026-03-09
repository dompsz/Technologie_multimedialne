<?php
session_start();
unset($_SESSION['lab12b_user_id']);
unset($_SESSION['lab12b_username']);
header("Location: index.php");
exit();
?>
