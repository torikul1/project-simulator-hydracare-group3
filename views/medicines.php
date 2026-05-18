<!DOCTYPE html>
<html>
<head>
    <title>Manage Medicines</title>
    <link rel="stylesheet" type="text/css" href="../views/css/admin.css">
</head>
<body>
    <h2>Medicine Inventory Management</h2>
    <nav>
        <a href="?action=dashboard">Dashboard</a>
        <a href="?action=categories">Manage Categories</a>
        <a href="?action=medicines">Manage Medicines</a>
        <a href="?action=customers">Customers</a>
        <a href="?action=orders">Orders & History</a>
    </nav>
    <hr>

    <?php if(!empty($errors)): ?>
        <div class="error-box">
            <?php foreach($errors as $err) echo "<p>$err</p>"; ?>
        </div>
    <?php endif; ?>

    <div class="form-container">
        <h3>Add / Edit Medicine</h3>
        <form method="POST" action="?action=medicines" enctype="multipart/form-data" onsubmit="return validateMedicineForm()">
            <input type="hidden" name="id" id="med_id" value="">
            <input type="hidden" name="old_image" id="old_image" value="">
            
            <div class="form-group">
                <label>Medicine Name</label>
                <input type="text" name="name" id="med_name" required>
            </div>
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="med_cat" required>
                    <option value="">-- Select Category --</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?=$c['id']?>"><?=$c['name']?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Vendor Name</label>
                <input type="text" name="vendor_name" id="med_vendor" required>
            </div>
            <div class="form-group">
                <label>Price ($)</label>
                <input type="number" step="0.01" name="price" id="med_price" required>
            </div>
            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" name="availability" id="med_stock" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="med_desc" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Product Image (Max 2MB, JPG/PNG)</label>
                <input type="file" name="medicine_image" id="med_img">
            </div>
            <button type="submit" class="btn">Save Item</button>
            <button type="button" class="btn btn-danger" onclick="resetMedForm()">Clear</button>
        </form>
    </div>

    <h3>Inventory Products</h3>
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
            <?php foreach($medicines as $m): ?>
            <tr>
                <td><img src="../<?=$m['image_path']?>" width="50" onerror="this.src='https://placehold.co/50'"></td>
                <td><?=$m['name']?></td>
                <td><?=$m['category_name']?></td>
                <td><?=$m['vendor_name']?></td>
                <td>$<?=number_format($m['price'], 2)?></td>
                <td><?=$m['availability']?></td>
                <td>
                    <button class="btn" onclick="editMed(<?= htmlspecialchars(json_encode($m)) ?>)">Edit</button>
                    <a href="?action=medicines&delete=<?=$m['id']?>" class="btn btn-danger" onclick="return confirm('Delete item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    function validateMedicineForm() {
        const price = parseFloat(document.getElementById('med_price').value);
        const stock = parseInt(document.getElementById('med_stock').value);
        const fileInput = document.getElementById('med_img');

        if (price <= 0 || stock < 0) {
            alert('Price must be greater than 0 and stock cannot be negative.');
            return false;
        }

        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                alert('Only JPG, JPEG, and PNG images are allowed.');
                return false;
            }
            if (file.size > 2 * 1024 * 1024) {
                alert('Image execution block size limit exceeded. Keep it under 2MB.');
                return false;
            }
        }
        return true;
    }

    function editMed(data) {
        document.getElementById('med_id').value = data.id;
        document.getElementById('med_name').value = data.name;
        document.getElementById('med_cat').value = data.category_id;
        document.getElementById('med_vendor').value = data.vendor_name;
        document.getElementById('med_price').value = data.price;
        document.getElementById('med_stock').value = data.availability;
        document.getElementById('med_desc').value = data.description;
        document.getElementById('old_image').value = data.image_path;
    }

    function resetMedForm() {
        document.getElementById('med_id').value = '';
        document.getElementById('old_image').value = '';
        document.getElementById('med_name').value = '';
        document.getElementById('med_cat').value = '';
        document.getElementById('med_vendor').value = '';
        document.getElementById('med_price').value = '';
        document.getElementById('med_stock').value = '';
        document.getElementById('med_desc').value = '';
        document.getElementById('med_img').value = '';
    }
    </script>
</body>
</html>