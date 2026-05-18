<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

if(session_status() === PHP_SESSION_NONE){ session_start(); }

require_once '../Model/db_config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$vendor_name = $_SESSION['user_name'];
$vendor_id = $_SESSION['user_id'];


$total_medicines = 0;

$q1 = $conn->prepare("
    SELECT COUNT(*) AS total
    FROM medicines
    WHERE vendor_id = ?
");

$q1->bind_param("i", $vendor_id);
$q1->execute();

$r1 = $q1->get_result()->fetch_assoc();

$total_medicines = $r1['total'];


$total_stock = 0;

$q2 = $conn->prepare("
    SELECT SUM(availability) AS stock
    FROM medicines
    WHERE vendor_id = ?
");

$q2->bind_param("i", $vendor_id);
$q2->execute();

$r2 = $q2->get_result()->fetch_assoc();

$total_stock = $r2['stock'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Vendor Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container mt-5">

    <h2 class="mb-4">

        Vendor Dashboard

    </h2>

    <p>

        Welcome,
        <strong><?php echo htmlspecialchars($vendor_name); ?></strong>

    </p>

    <hr>

    <h4>Summary</h4>

    <table class="table table-bordered w-50">

        <tr>

            <th>Total Medicines</th>

            <td><?php echo $total_medicines; ?></td>

        </tr>

        <tr>

            <th>Total Stock</th>

            <td><?php echo $total_stock; ?></td>

        </tr>

    </table>

    <hr>

    <h4>Menu</h4>

    <div class="d-grid gap-2 col-md-4">

        <a href="../Control/add_medicine.php"
           class="btn btn-primary">

            Add Medicine

        </a>

        <a href="../Upload/medicines.php"
           class="btn btn-secondary">

            View Medicines

        </a>

        <a href="../Control/manage_stock.php"
           class="btn btn-warning">

            Manage Stock

        </a>

        <a href="../Control/statistics.php"
           class="btn btn-info">

            Statistics

        </a>

        <a href="../Control/sales_history.php"
           class="btn btn-success">

            Sales History

        </a>

        <a href="../Control/orders.php"
           class="btn btn-dark">

            Orders

        </a>

        <a href="../Control/logout.php"
           class="btn btn-danger">

            Logout

        </a>

    </div>

</div>

</body>
</html>