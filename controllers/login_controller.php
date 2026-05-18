<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/db_config.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Capture and clean inputs
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($email) || empty($password)) {
        header("Location: ../views/public/index.php");
        exit();
    }

    // 3. Query the database to find the user profile matching password_hash column
    $query = "SELECT * FROM users WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        // 4. Verify password against the password_hash column safely
        if ($user && password_verify($password, $user['password_hash'])) {
            $login_successful = true;
        } else {
            $login_successful = false;
        }
        $stmt->close();
    } else {
        die("Database operation failed: " . $conn->error);
    }

    // 5. Run your login success block
    if ($login_successful) {
        
        
        $_SESSION['user_id'] = $user['id'];      
        $_SESSION['name']    = $user['name'];    
        $_SESSION['role']    = $user['role'];    

        
        if (isset($_POST['remember_me'])) {
            $cookie_expiration = time() + (30 * 24 * 60 * 60); 
            setcookie('remember_user_id', $user['id'], $cookie_expiration, "/");
            setcookie('remember_user_email', $user['email'], $cookie_expiration, "/");
        }

        // Redirect directly to home dashboard
        header("Location: ../views/index.php");
        exit();
        
    } else {
        // Redirect back on validation error
        header("Location: ../views/login.php?msg=invalid_credentials");
        exit();
    }
} else {
    header("Location: ../views/login.php");
    exit();
}