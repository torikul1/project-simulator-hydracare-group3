<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header("Location: ../views/login.php");
    exit();
}

require_once '../config/db_config.php';

$user_id = $_SESSION['user_id'];
$name = trim($_POST['name']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$new_password = trim($_POST['new_password']);
$current_password = trim($_POST['current_password']);

// 1. Fetch current password hash to verify identity
// FIXED HERE: Changed 'password' to 'password_hash' column
$stmt = $conn->prepare("SELECT password_hash, profile_pic FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// FIXED HERE: Used $user['password_hash']
if (!password_verify($current_password, $user['password_hash'])) {
    header("Location: ../views/profile.php?msg=wrong_password");
    exit();
}

// 2. Handle Profile Picture Uploading securely
$profile_pic_name = $user['profile_pic']; // Default back to current asset

if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
    $fileName = $_FILES['profile_pic']['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    $allowedExtensions = ['jpg', 'jpeg', 'png'];
    
    if (in_array($fileExtension, $allowedExtensions)) {
        // Generate a clean, completely unique name for the file
        $profile_pic_name = "user_" . $user_id . "_" . time() . "." . $fileExtension;
        
        // ⚠️ FIXED HERE: Target path matches your nested public directory inside views
        $uploadFileDir = '../views/public/uploads/';
        
        // Ensure folder directory configuration path mapping exists local-side
        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        
        move_uploaded_file($fileTmpPath, $uploadFileDir . $profile_pic_name);
    } else {
        header("Location: ../views/profile.php?msg=invalid_file");
        exit();
    }
}

// 3. Process data structural adjustments update mapping
if (!empty($new_password)) {
    // If updating security credentials, hash it down 
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    // ⚠️ FIXED HERE: Target database column 'password_hash'
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, password_hash = ?, profile_pic = ? WHERE id = ?");
    $update_stmt->bind_param("ssssssi", $name, $email, $phone, $address, $hashed_password, $profile_pic_name, $user_id);
} else {
    // Standard data metadata update 
    $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, address = ?, profile_pic = ? WHERE id = ?");
    $update_stmt->bind_param("sssssi", $name, $email, $phone, $address, $profile_pic_name, $user_id);
}

if ($update_stmt->execute()) {
    // Update active active session nickname parameters to keep navbar matched in sync
    $_SESSION['name'] = $name;
    header("Location: ../views/profile.php?msg=success");
} else {
    header("Location: ../views/profile.php?msg=error");
}
$update_stmt->close();
exit();