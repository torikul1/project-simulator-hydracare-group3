<!DOCTYPE html>
<html>
<head>
    <title>Manage Categories</title>
    <link rel="stylesheet" type="text/css" href="../views/css/admin.css">
</head>
<body>
    <h2>Category Management</h2>
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
        <h3>Add / Edit Category</h3>
        <form method="POST" action="?action=categories">
            <input type="hidden" name="id" id="cat_id" value="">
            <div class="form-group">
                <label>Category Name</label>
                <input type="text" name="name" id="cat_name" required>
            </div>
            <div class="form-group">
                <label>Category Type</label>
                <select name="category_type" id="cat_type" required>
                    <option value="">-- Select Type --</option>
                    <option value="liquid">Liquid</option>
                    <option value="solid">Solid</option>
                </select>
            </div>
            <button type="submit" class="btn">Save Category</button>
            <button type="button" class="btn btn-danger" onclick="clearForm()">Clear</button>
        </form>
    </div>

    <h3>Existing Categories</h3>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($categories as $cat): ?>
            <tr>
                <td><?=$cat['id']?></td>
                <td><?=$cat['name']?></td>
                <td><?=ucfirst($cat['category_type'])?></td>
                <td>
                    <button class="btn" onclick="editCategory(<?=$cat['id']?>, '<?=addslashes($cat['name'])?>', '<?=$cat['category_type']?>')">Edit</button>
                    <a href="?action=categories&delete=<?=$cat['id']?>" class="btn btn-danger" onclick="return confirm('Delete this category?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    function editCategory(id, name, type) {
        document.getElementById('cat_id').value = id;
        document.getElementById('cat_name').value = name;
        document.getElementById('cat_type').value = type;
    }
    function clearForm() {
        document.getElementById('cat_id').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('cat_type').value = '';
    }
    </script>
</body>
</html>