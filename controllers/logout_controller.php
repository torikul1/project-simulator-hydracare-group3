<?php
// controllers/logout_controller.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

session_unset();
session_destroy();

// 2. Kill the browser cookie trackers from root access path "/"
if (isset($_COOKIE['remember_user_id'])) {
    setcookie('remember_user_id', '', time() - 3600, "/");
}
if (isset($_COOKIE['remember_user_email'])) {
    setcookie('remember_user_email', '', time() - 3600, "/");
}

header("Location: ../views/login.php");
exit();