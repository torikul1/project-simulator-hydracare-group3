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

if (isset($_GET['delete'])) {

    $medicine_id = (int)$_GET['delete'];

    
    $img = $conn->prepare("
        SELECT image_path
        FROM medicines
        WHERE id = ? AND vendor_id = ?
    ");

    $img->bind_param("ii", $medicine_id, $vendor_id);
    $img->execute();

    $img_result = $img->get_result();

    if ($img_result->num_rows > 0) {

        $medicine = $img_result->fetch_assoc();

        if (!empty($medicine['image_path'])) {
            $old_image = dirname(__DIR__) . '/' . $medicine['image_path'];
            if (file_exists($old_image)) {
                unlink($old_image);
            }
        }

      
        $delete = $conn->prepare("
            DELETE FROM medicines
            WHERE id = ? AND vendor_id = ?
        ");

        $delete->bind_param("ii", $medicine_id, $vendor_id);

        if ($delete->execute()) {

            header("Location: medicines.php?deleted=1");
            exit();

        } else {

            $error = "Delete failed.";
        }
    }
}


$stmt = $conn->prepare("
    SELECT
        m.*,
        c.name AS category_name
    FROM medicines m
    LEFT JOIN categories c
    ON m.category_id = c.id
    WHERE m.vendor_id = ?
    ORDER BY m.id DESC
");

$stmt->bind_param("i", $vendor_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Medicines</title>

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

        <a href="../Control/add_medicine.php"
           class="btn btn-success">

            + Add Medicine

        </a>

    </div>

    <?php if (isset($_GET['success'])) : ?>

        <div class="alert alert-success">
            Medicine added successfully.
        </div>

    <?php endif; ?>

    <?php if (isset($_GET['updated'])) : ?>

        <div class="alert alert-primary">
            Medicine updated successfully.
        </div>

    <?php endif; ?>

    <?php if (isset($_GET['deleted'])) : ?>

        <div class="alert alert-danger">
            Medicine deleted successfully.
        </div>

    <?php endif; ?>

    <?php if (isset($error)) : ?>

        <div class="alert alert-danger">
            <?php echo $error; ?>
        </div>

    <?php endif; ?>

    <div class="card shadow">

        <div class="card-header bg-primary text-white">

            <h4>My Medicine List</h4>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead class="table-dark">


    <tr>

        <th>ID</th>
        <th>Image</th>
        <th>Name</th>
        <th>Company</th>
        <th>Category</th>
        <th>Price</th>
        <th>Stock</th>
        <th>Actions</th>

    </tr>

</thead>

                    <tbody>

                    <?php if ($result->num_rows > 0) : ?>

                        <?php while ($row = $result->fetch_assoc()) : ?>

                           <tr>

    <td>
        <?php echo $row['id']; ?>
    </td>

    <td>

        <?php if (!empty($row['image_path'])) : ?>

            <img
                src="../<?php echo $row['image_path']; ?>"
                width="70"
                height="70"
                class="img-thumbnail"
            >

        <?php else : ?>

            No Image

        <?php endif; ?>

    </td>

    <td>
        <?php echo htmlspecialchars($row['name']); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($row['company_name']); ?>
    </td>

    <td>
        <?php echo htmlspecialchars($row['category_name']); ?>
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

        <a href="../Control/edit_medicine.php?id=<?php echo $row['id']; ?>"
           class="btn btn-primary btn-sm">

            Edit

        </a>

        <a href="../Control/manage_stock.php"
           class="btn btn-warning btn-sm">

            Stock

        </a>

        <a href="medicines.php?delete=<?php echo $row['id']; ?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('Delete this medicine?')">

            Delete

        </a>

    </td>

</tr>

                        <?php endwhile; ?>

                    <?php else : ?>

                        <tr>

                            <td colspan="7"
                                class="text-center">

                                No medicines found.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>