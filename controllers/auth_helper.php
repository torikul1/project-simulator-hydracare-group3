<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function checkRememberMe()
{
    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_user_id'])) {


        $_SESSION['user_id'] = $_COOKIE['remember_user_id'];


        $_SESSION['name']    = isset($_COOKIE['remember_user_email']) ? explode('@', $_COOKIE['remember_user_email'])[0] : 'User';
        $_SESSION['role']    = 'customer'; // Default role fallback

        header("Location:index.php");
        exit();
    }
}
