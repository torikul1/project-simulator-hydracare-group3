<?php
require_once '../config/db_config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize Inputs
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Don't escape yet, we will hash it
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    // Server-side Validation
    if (strlen($password) < 8) {
        die("Security error: Password must be 8+ characters.");
    }

    // Check if Email already exists
    $checkEmail = "SELECT email FROM users WHERE email = '$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        // In a real app, you'd redirect with an error message
        die("Error: This email is already registered.");
    } else {
        // Hash the password securely
        $password_hash = password_hash($password, PASSWORD_BCRYPT);

        // SQL Query matching your table structure
        $sql = "INSERT INTO users (name, email, password_hash, role, address, phone) 
                VALUES ('$name', '$email', '$password_hash', '$role', '$address', '$phone')";

        if ($conn->query($sql) === TRUE) {
            // Redirect to Login Page on Success
            header("Location: ../views/login.php?msg=signup_success");
            exit();
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
$conn->close();
?>