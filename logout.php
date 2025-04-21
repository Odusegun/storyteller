<?php
session_start();

// Destroy the session and log out the user
session_unset();
session_destroy();

// Redirect to login or home page after logout
header("Location: signin.php");
exit();
?>
