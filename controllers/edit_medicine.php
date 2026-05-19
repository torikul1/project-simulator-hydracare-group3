<?php
require_once '../Control/header.php';
require_once '../Model/db_config.php';

$vendor_id = $_SESSION['user_id'];

$medicine_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($medicine_id <= 0) {
    header("Location: ../Upload/medicines.php");
    exit();
}


$stmt = $conn->prepare("
    SELECT *
    FROM medicines
    WHERE id = ? AND vendor_id = ?
");

$stmt->bind_param("ii", $medicine_id, $vendor_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: ../Upload/medicines.php");
    exit();
}

$medicine = $result->fetch_assoc();


$categories = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $availability = (int)$_POST['availability'];
    $description = trim($_POST['description']);

    
    $check = $conn->prepare("
        SELECT id
        FROM categories
        WHERE id = ?
    ");

    $check->bind_param("i", $category_id);
    $check->execute();

    $category_result = $check->get_result();

    if ($category_result->num_rows == 0) {

        $error = "Invalid category selected.";

    } else {

        
        $image_path = $medicine['image_path'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $upload_dir = __DIR__ . '/uploads/medicines/';

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = time() . '_' . basename($_FILES['image']['name']);

            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {

                if (!empty($medicine['image_path'])) {
                    $old_image = dirname(__DIR__) . '/' . $medicine['image_path'];
                    if (file_exists($old_image)) {
                        unlink($old_image);
                    }
                }

                $image_path = 'Control/uploads/medicines/' . $filename;
            }
        }

        
        $update = $conn->prepare("
            UPDATE medicines
            SET
                name = ?,
                category_id = ?,
                price = ?,
                availability = ?,
                description = ?,
                image_path = ?
            WHERE id = ? AND vendor_id = ?
        ");

        $update->bind_param(
            "sidissii",
            $name,
            $category_id,
            $price,
            $availability,
            $description,
            $image_path,
            $medicine_id,
            $vendor_id
        );

        if ($update->execute()) {

            header("Location: ../Upload/medicines.php?updated=1");
            exit();

        } else {

            $error = "Update failed: " . $conn->error;
        }
    }
}
?>

<div class="container mt-4">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">
                    <h4>Edit Medicine</h4>
                </div>

                <div class="card-body">

                    <?php if (isset($error)) : ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">
                                Medicine Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($medicine['name']); ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Category
                            </label>

                            <select
                                name="category_id"
                                class="form-control"
                                required
                            >

                                <option value="">
                                    Select Category
                                </option>

                                <?php while ($cat = $categories->fetch_assoc()) : ?>

                                    <option
                                        value="<?php echo $cat['id']; ?>"
                                        <?php
                                        if ($medicine['category_id'] == $cat['id']) {
                                            echo 'selected';
                                        }
                                        ?>
                                    >
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Price
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                name="price"
                                class="form-control"
                                value="<?php echo $medicine['price']; ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Stock Quantity
                            </label>

                            <input
                                type="number"
                                name="availability"
                                class="form-control"
                                value="<?php echo $medicine['availability']; ?>"
                                required
                            >
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                            ><?php echo htmlspecialchars($medicine['description']); ?></textarea>
                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Current Image
                            </label>

                            <br>

                            <?php if (!empty($medicine['image_path'])) : ?>

                                <img
                                    src="../<?php echo $medicine['image_path']; ?>"
                                    width="120"
                                    class="img-thumbnail mb-2"
                                >

                            <?php else : ?>

                                <p>No image uploaded</p>

                            <?php endif; ?>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Change Image
                            </label>

                            <input
                                type="file"
                                name="image"
                                class="form-control"
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Update Medicine
                        </button>

                        <a
                            href="../Upload/medicines.php"
                            class="btn btn-secondary"
                        >
                            Back
                        </a>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<?php require_once '../Control/footer.php'; ?>