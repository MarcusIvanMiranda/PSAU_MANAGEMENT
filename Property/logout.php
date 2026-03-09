<?php
session_start();
unset($_SESSION['property_loggedin']);
unset($_SESSION['property_username']);
unset($_SESSION['property_user_id']);
unset($_SESSION['property_full_name']);
unset($_SESSION['property_office']);
unset($_SESSION['property_members']);
unset($_SESSION['property_role']);
unset($_SESSION['property_dbname']);
session_destroy();
header("location: login.php");
exit();
?>
