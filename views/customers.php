<!DOCTYPE html>
<html>
<head>
    <title>Manage Customers</title>
    <link rel="stylesheet" type="text/css" href="../views/css/admin.css">
</head>
<body>
    <h2>Customer Accounts</h2>
    <nav>
        <a href="?action=dashboard">Dashboard</a>
        <a href="?action=categories">Manage Categories</a>
        <a href="?action=medicines">Manage Medicines</a>
        <a href="?action=customers">Customers</a>
        <a href="?action=orders">Orders & History</a>
    </nav>
    <hr>

    <table>
        <thead>
            <tr>
                <th>Avatar</th>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($customers as $cust): ?>
            <tr>
                <td><img src="../<?=$cust['profile_picture']?>" width="40" onerror="this.src='https://placehold.co/40'"></td>
                <td><?=$cust['id']?></td>
                <td><?=$cust['name']?></td>
                <td><?=$cust['email']?></td>
                <td>
                    <a href="?action=customers&delete=<?=$cust['id']?>" class="btn btn-danger" onclick="return confirm('Wipe customer account? This will cascade remove their matching carts and orders profiles.')">Remove Customer</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>