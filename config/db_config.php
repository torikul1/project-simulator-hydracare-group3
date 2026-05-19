<?php
// config/db_config.php

$host = "localhost";
$user = "root";
$pass = "";
$db_name = "hydracare";

$conn = new mysqli($host, $user, $pass, $db_name);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better compatibility
$conn->set_charset("utf8mb4");
?>