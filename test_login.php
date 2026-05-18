<?php
session_start();
$_SESSION['role'] = 'admin';
$_SESSION['user_id'] = 1; 
echo "<h3>Admin Session Initialized!</h3>";
echo "<a href='controllers/AdminController.php?action=dashboard'>Enter Admin Management Panel</a>";
?>