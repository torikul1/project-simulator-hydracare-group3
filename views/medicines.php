<?php
// views/medicines.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================================
// 🔌 DATABASE AUTOMATIC COUPLING 
// ==========================================================================
if (!isset($conn)) {
    if (file_exists('../config/db_config.php')) {
        require_once '../config/db_config.php';
    } elseif (file_exists('../config/database.php')) {
        require_once '../config/database.php';
    } elseif (file_exists('../Model/db_config.php')) {
        require_once '../Model/db_config.php';
    }
}

// Verify active connection exists
if (!isset($conn) || !$conn) {
    die("<div style='padding:20px; background:#fee2e2; color:#991b1b;'><strong>Database Link Crash:</strong> System connection missing. Check configuration pathways.</div>");
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=auth_required");
    exit();
}

$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'vendor';
$user_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'ara';

$errors = [];
$success_msg = "";

// ==========================================================================
// FORM CRUD OPERATION ENGINE
// ==========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? intval($_POST['id']) : null;
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $vendor_name = !empty($_POST['vendor_name']) ? trim($_POST['vendor_name']) : $user_name;
    $price = floatval($_POST['price']);
    $availability = intval($_POST['availability']);
    $description = trim($_POST['description']);

    $image_path = !empty($_POST['old_image']) ? $_POST['old_image'] : '';
    if (isset($_FILES['medicine_image']) && $_FILES['medicine_image']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['medicine_image']['tmp_name'];
        $fileName = $_FILES['medicine_image']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = 'med_' . time() . '.' . $fileExtension;
        $uploadFileDir = '../public/uploads/';

        if (!is_dir($uploadFileDir)) {
            mkdir($uploadFileDir, 0755, true);
        }
        if (move_uploaded_file($fileTmpPath, $uploadFileDir . $newFileName)) {
            $image_path = 'public/uploads/' . $newFileName;
        }
    }

    if (empty($name) || $price <= 0 || $availability < 0 || empty($category_id)) {
        $errors[] = "Please provide data inputs for name, pricing, stock count, and target category fields.";
    }

    if (empty($errors)) {
        if ($id) {
            $stmt = $conn->prepare("UPDATE medicines SET name=?, category_id=?, vendor_name=?, price=?, availability=?, description=?, image_path=? WHERE id=?");
            $stmt->bind_param("sisdissi", $name, $category_id, $vendor_name, $price, $availability, $description, $image_path, $id);
            if ($stmt->execute()) {
                $success_msg = "Medicine row records updated successfully!";
            } else {
                $errors[] = "SQL Execution Error: " . $conn->error;
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO medicines (name, category_id, vendor_name, price, availability, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sisdiss", $name, $category_id, $vendor_name, $price, $availability, $description, $image_path);
            if ($stmt->execute()) {
                $success_msg = "Product successfully created inside your storage array!";
            } else {
                $errors[] = "SQL Execution Error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}

// DELETE ENTRY OPERATION
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    if ($user_name === 'ara') {
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id=?");
        $stmt->bind_param("i", $delete_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM medicines WHERE id=? AND vendor_name=?");
        $stmt->bind_param("is", $delete_id, $user_name);
    }
    $stmt->execute();
    $stmt->close();
    $success_msg = "Product entry has been successfully expunged.";
}

// ==========================================================================
// FETCH RECOVERY ENGINE (CATEGORIES & COMPLETE MEDICINES CATALOG)
// ==========================================================================
$categories = [];
$medicines = [];

// 1. Recover Category Options for Dynamic Dropdown Selection
$cat_res = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
if ($cat_res) {
    while ($r = $cat_res->fetch_assoc()) {
        $categories[] = $r;
    }
}

// 2. Fetch Complete Shared Table Catalog for User 'ara'
$med_sql = "SELECT m.*, c.name AS category_name FROM medicines m LEFT JOIN categories c ON m.category_id = c.id";
if ($user_name === 'ara' || $user_role === 'admin') {
    $med_res = $conn->query($med_sql . " ORDER BY m.id DESC");
} else {
    $m_stmt = $conn->prepare($med_sql . " WHERE m.vendor_name = ? ORDER BY m.id DESC");
    $m_stmt->bind_param("s", $user_name);
    $m_stmt->execute();
    $med_res = $m_stmt->get_result();
}

if ($med_res) {
    while ($r = $med_res->fetch_assoc()) {
        $medicines[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Manage Medicines Inventory</title>
    <style>
        body {
            font-family: system-ui, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            margin: 0;
            padding: 30px;
        }

        h2 {
            color: #1e3a8a;
            font-size: 24px;
            margin-bottom: 5px;
        }

        nav {
            background: #1e293b;
            padding: 14px;
            border-radius: 6px;
            margin-bottom: 25px;
        }

        nav a {
            color: #f8fafc;
            text-decoration: none;
            margin-right: 20px;
            font-weight: 500;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .form-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 5px;
            color: #475569;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn {
            background: #2563eb;
            color: #fff;
            border: none;
            padding: 10px 22px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-danger {
            background: #dc2626;
            text-decoration: none;
            display: inline-block;
        }

        .btn-danger:hover {
            background: #b91c1c;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
            font-size: 14px;
        }

        th {
            background: #0f172a;
            color: #fff;
            font-weight: 600;
        }

        tr:hover {
            background: #f8fafc;
        }
    </style>
</head>

<body>

    <h2>Medicine Inventory Management</h2>
    <p style="margin:0 0 15px 0; color:#475569;">Active Session Dashboard Link: <strong><?= htmlspecialchars($user_name); ?></strong></p>

    <nav>
        <a href="../views/dashboard.php">Dashboard Home</a>
        <a href="medicines.php">My Inventory Items</a>
        <a href="cart.php">Shopping Carts</a>
    </nav>

    <?php if (!empty($success_msg)): ?>
        <div style="background: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #bbf7d0;">🎉 <?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div style="background: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fca5a5;">
            <?php foreach ($errors as $err) echo "• " . htmlspecialchars($err) . "<br>"; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h3 id="form-title" style="margin-top:0; color:#1e293b;">Add New Medicine</h3>
        <form method="POST" action="medicines.php" enctype="multipart/form-data">
            <input type="hidden" name="id" id="med_id" value="">
            <input type="hidden" name="old_image" id="old_image" value="">

            <div class="form-group">
                <label>Medicine Name</label>
                <input type="text" name="name" id="med_name" required placeholder="e.g. Napa 500mg">
            </div>

            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="med_cat" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= htmlspecialchars($c['id']) ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Vendor Distribution Tag</label>
                <input type="text" name="vendor_name" id="med_vendor" value="<?= htmlspecialchars($user_name === 'ara' ? 'Beximco Pharma' : $user_name) ?>" required>
            </div>

            <div class="form-group">
                <label>Price (৳)</label>
                <input type="number" step="0.01" name="price" id="med_price" required placeholder="0.00">
            </div>

            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="availability" id="med_stock" required placeholder="0">
            </div>

            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="med_desc" rows="3" placeholder="Enter details..."></textarea>
            </div>

            <div class="form-group">
                <label>Product Image</label>
                <input type="file" name="medicine_image" id="med_img" accept="image/*">
            </div>

            <button type="submit" class="btn">Save Product</button>
            <button type="button" class="btn btn-danger" onclick="resetMedForm()">Clear Form</button>
        </form>
    </div>

    <h3 style="color:#1e293b;">Inventory Products</h3>
    <table>
        <thead>
            <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Vendor</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($medicines)): ?>
                <tr>
                    <td colspan="7" style="text-align: center; color: #64748b; padding: 30px;">No medicine records found in the database.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($medicines as $m): ?>
                    <tr>
                        <td><img src="../<?= !empty($m['image_path']) ? htmlspecialchars($m['image_path']) : 'public/uploads/profileavater.png' ?>" width="45" style="border-radius:4px; object-fit:cover;" onerror="this.src='https://placehold.co/45'"></td>
                        <td><strong><?= htmlspecialchars($m['name']) ?></strong></td>
                        <td><?= htmlspecialchars($m['category_name'] ?? 'General') ?></td>
                        <td><span style="color:#4f46e5; font-weight:500;"><?= htmlspecialchars($m['vendor_name']) ?></span></td>
                        <td>৳<?= number_format($m['price'], 2) ?></td>
                        <td><b><?= htmlspecialchars($m['availability']) ?></b> units</td>
                        <td>
                            <button class="btn" style="padding: 5px 12px; font-size:12px;" onclick='editMed(<?= json_encode($m) ?>)'>Edit</button>
                            <a href="medicines.php?delete_id=<?= $m['id'] ?>" class="btn btn-danger" style="padding: 5px 12px; font-size:12px;" onclick="return confirm('Delete record?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function editMed(data) {
            document.getElementById('form-title').innerText = "Edit Product: " + data.name;
            document.getElementById('med_id').value = data.id;
            document.getElementById('med_name').value = data.name;
            document.getElementById('med_cat').value = data.category_id;
            document.getElementById('med_vendor').value = data.vendor_name;
            document.getElementById('med_price').value = data.price;
            document.getElementById('med_stock').value = data.availability;
            document.getElementById('med_desc').value = data.description;
            document.getElementById('old_image').value = data.image_path;
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        function resetMedForm() {
            document.getElementById('form-title').innerText = "Add New Medicine";
            document.getElementById('med_id').value = '';
            document.getElementById('old_image').value = '';
            document.getElementById('med_name').value = '';
            document.getElementById('med_cat').value = '';
            document.getElementById('med_vendor').value = "<?= htmlspecialchars($user_name === 'ara' ? 'Beximco Pharma' : $user_name) ?>";
            document.getElementById('med_price').value = '';
            document.getElementById('med_stock').value = '';
            document.getElementById('med_desc').value = '';
            document.getElementById('med_img').value = '';
        }
    </script>
</body>

</html>