<?php
if(session_status() === PHP_SESSION_NONE){ session_start(); }
require_once '../Model/db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows > 0) {
        $error = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (name, email, password_hash, role, phone, address) VALUES (?, ?, ?, 'vendor', ?, ?)");
        $stmt->bind_param("sssss", $name, $email, $password, $phone, $address);
        
        if ($stmt->execute()) {
            $success = "Registration successful! You can now login.";
        } else {
            $error = "Registration failed: " . $conn->error;
        }
        $stmt->close();
    }
    $check->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration - HydraCare</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">Vendor Registration</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if(isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?> <a href="login.php">Login here</a></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label>Full Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="form-control" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label>Address</label>
                                    <textarea name="address" class="form-control" rows="2" required></textarea>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Register</button>
                        </form>
                        <hr>
                        <p class="text-center">Already have an account? <a href="login.php">Login here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>