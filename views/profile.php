<?php
// views/profile.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Session Gate: Kick out if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once '../config/db_config.php';

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>HydraCare | My Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background: #f8fafc;
            display: block;
            height: auto;
            padding: 0;
            margin: 0;
        }

        .nav-bar {
            background: #fff;
            padding: 15px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .profile-wrapper {
            max-width: 700px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .avatar-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #2ecc71;
            margin-bottom: 15px;
        }

        .avatar-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            background: #fafafa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 1px dashed #cbd5e1;
        }
    </style>
</head>

<body>

    <nav class="nav-bar">
        <div style="display: flex; align-items: center; gap: 8px;">
            <img src="../public/hydracarelogo.png" alt="Logo" style="height: 38px; width: auto;">
        </div>
        <div style="display: flex; align-items: center; gap: 20px;">
            <a href="../public/index.php" style="color: #475569; text-decoration: none; font-weight: 600;"><i class="ri-home-4-line"></i> Home Catalog</a>
            <a href="../controllers/logout_controller.php" style="color: #e74c3c; text-decoration: none; font-weight: 600;"><i class="ri-logout-box-line"></i> Logout</a>
        </div>
    </nav>

    <div class="profile-wrapper">
        <div class="register-container advanced-card" style="width: 100%; padding: 35px;">
            <div class="auth-header">
                <h2>Manage Profile</h2>
                <p>Update your personal information & password security</p>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <?php if ($_GET['msg'] == 'success'): ?>
                    <div class="alert" style="background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0;"><i class="ri-checkbox-circle-line"></i> Profile updated successfully!</div>
                <?php elseif ($_GET['msg'] == 'wrong_password'): ?>
                    <div class="alert alert-danger"><i class="ri-error-warning-line"></i> Current password does not match!</div>
                <?php elseif ($_GET['msg'] == 'invalid_file'): ?>
                    <div class="alert alert-danger"><i class="ri-error-warning-line"></i> Invalid file type! Only JPG, JPEG, & PNG allowed.</div>
                <?php endif; ?>
            <?php endif; ?>

            <form action="../controllers/profile_controller.php" method="POST" enctype="multipart/form-data">

               <div class="avatar-section">
    <?php
    
    $pic = !empty($user['profile_pic']) ? $user['profile_pic'] : 'profileavater.png';

    
    if (file_exists("public/uploads/" . $pic)) {
        
        $pic_path = "public/uploads/" . $pic;
    } else {
        
        $pic_path = "public/profileavater.png";
    }
    ?>

    <img src="<?php echo $pic_path; ?>" class="avatar-preview" alt="Profile Picture">
    <label style="text-align: center; margin-top: 10px; font-weight: 600;">Change Profile Picture</label>
    <input type="file" name="profile_pic" accept="image/*" style="padding: 5px; font-size: 0.85rem; border: none; background: transparent; cursor: pointer;">
</div>

                <div class="form-grid">
                    <div class="input-group">
                        <label>Full Name</label>
                        <div class="input-field-wrapper">
                            <i class="ri-user-line field-icon"></i>
                            <input type="text" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Email Address</label>
                        <div class="input-field-wrapper">
                            <i class="ri-mail-line field-icon"></i>
                            <input type="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Phone Number</label>
                        <div class="input-field-wrapper">
                            <i class="ri-phone-line field-icon"></i>
                            <input type="text" name="phone" required value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Delivery Address</label>
                        <div class="input-field-wrapper">
                            <i class="ri-map-pin-line field-icon"></i>
                            <input type="text" name="address" required value="<?php echo htmlspecialchars($user['address']); ?>">
                        </div>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 25px 0;">
                <h3 style="font-size: 1.1rem; color: #1e293b; margin-bottom: 15px;"><i class="ri-lock-password-line"></i> Security Update (Leave blank to keep current)</h3>

                <div class="form-grid">
                    <div class="input-group">
                        <label>New Password</label>
                        <div class="input-field-wrapper">
                            <i class="ri-lock-line field-icon"></i>
                            <input type="password" name="new_password" placeholder="Enter new password">
                        </div>
                    </div>

                    <div class="input-group">
                        <label>Current Password (Required to save updates)</label>
                        <div class="input-field-wrapper">
                            <i class="ri-shield-user-line field-icon"></i>
                            <input type="password" name="current_password" required placeholder="Verify current password">
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit advanced-btn" style="margin-top: 25px;">
                    <i class="ri-save-line"></i> Save Changes
                </button>
            </form>
        </div>
    </div>
</body>

</html>