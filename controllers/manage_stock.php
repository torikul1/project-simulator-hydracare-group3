<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

require_once '../Model/db_config.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$vendor_id = $_SESSION['user_id'];


if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $medicine_id = (int)$_POST['medicine_id'];
    $availability = (int)$_POST['availability'];

    $stmt = $conn->prepare("
        UPDATE medicines
        SET availability = ?
        WHERE id = ? AND vendor_id = ?
    ");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "iii",
        $availability,
        $medicine_id,
        $vendor_id
    );

    if ($stmt->execute()) {

        $success = "Stock updated successfully.";

    } else {

        $error = "Update failed: " . $stmt->error;
    }
}


$medicines = $conn->prepare("
    SELECT
        id,
        name,
        availability,
        price
    FROM medicines
    WHERE vendor_id = ?
    ORDER BY id DESC
");

$medicines->bind_param("i", $vendor_id);
$medicines->execute();

$result = $medicines->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Stock</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
          rel="stylesheet">

</head>

<body>

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-3">

        <a href="../View/dashboard.php"
           class="btn btn-dark">

            ← Dashboard

        </a>

        <a href="../Upload/medicines.php"
           class="btn btn-secondary">

            Medicine List

        </a>

    </div>

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h4>Manage Medicine Stock</h4>

        </div>

        <div class="card-body">

            <?php if (isset($success)) : ?>

                <div class="alert alert-success">
                    <?php echo $success; ?>
                </div>

            <?php endif; ?>

            <?php if (isset($error)) : ?>

                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>

            <?php endif; ?>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>
                            <th>Medicine Name</th>
                            <th>Price</th>
                            <th>Current Stock</th>
                            <th>Update Stock</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($row = $result->fetch_assoc()) : ?>

                        <tr>

                            <td>
                                <?php echo $row['id']; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($row['name']); ?>
                            </td>

                            <td>
                                ৳<?php echo $row['price']; ?>
                            </td>

                            <td>

                                <?php if ($row['availability'] > 0) : ?>

                                    <span class="badge bg-success">
                                        <?php echo $row['availability']; ?>
                                    </span>

                                <?php else : ?>

                                    <span class="badge bg-danger">
                                        Out of Stock
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <form method="POST"
                                      class="d-flex gap-2">

                                    <input type="hidden"
                                           name="medicine_id"
                                           value="<?php echo $row['id']; ?>">

                                    <input type="number"
                                           name="availability"
                                           class="form-control"
                                           value="<?php echo $row['availability']; ?>"
                                           min="0"
                                           required>

                                    <button type="submit"
                                            class="btn btn-primary">

                                        Update

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>