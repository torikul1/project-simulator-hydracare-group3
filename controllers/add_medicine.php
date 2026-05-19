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
$vendor_name = $_SESSION['user_name'];


$categories = $conn->query("
    SELECT id, name
    FROM categories
    ORDER BY name ASC
");


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $company_name = trim($_POST['company_name']);
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

    $result = $check->get_result();

    if ($result->num_rows == 0) {

        $error = "Invalid category selected.";
    } else {


        $image_path = "";

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

            $upload_dir = __DIR__ . "/uploads/medicines/";

            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = time() . "_" . basename($_FILES['image']['name']);

            $target_file = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = "Control/uploads/medicines/" . $filename;
            }
        }


        $stmt = $conn->prepare("
    INSERT INTO medicines
    (
        name,
        company_name,
        category_id,
        vendor_name,
        price,
        availability,
        description,
        image_path,
        vendor_id
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }

        $stmt->bind_param(
            "ssisdissi",
            $name,
            $company_name,
            $category_id,
            $vendor_name,
            $price,
            $availability,
            $description,
            $image_path,
            $vendor_id
        );

        if ($stmt->execute()) {

            header("Location: ../Upload/medicines.php?success=1");
            exit();
        } else {

            $error = "Insert failed: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>Add Medicine</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

</head>

<body>

    <div class="container mt-5">

        <div class="row justify-content-center">

            <div class="col-md-8">

                <div class="card shadow">

                    <div class="card-header bg-primary text-white">
                        <h4>Add Medicine</h4>
                    </div>

                    <div class="card-body">

                        <?php if (isset($error)) : ?>

                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>

                        <?php endif; ?>

                        <form method="POST"
                            enctype="multipart/form-data">

                            <div class="mb-3">

                                <label class="form-label">
                                    Medicine Name
                                </label>

                                <input type="text"
                                    name="name"
                                    class="form-control"
                                    required>

                            </div>
                            <div class="mb-3">

                                <label class="form-label">
                                    Medicine Company
                                </label>

                                <select name="company_name"
                                    class="form-control"
                                    required>

                                    <option value="">
                                        Select Company
                                    </option>

                                    <option value="Square">
                                        Square
                                    </option>

                                    <option value="Beximco">
                                        Beximco
                                    </option>

                                    <option value="Incepta">
                                        Incepta
                                    </option>

                                    <option value="Renata">
                                        Renata
                                    </option>

                                    <option value="ACI">
                                        ACI
                                    </option>

                                    <option value="Opsonin">
                                        Opsonin
                                    </option>

                                    <option value="Healthcare">
                                        Healthcare
                                    </option>

                                    <option value="Aristopharma">
                                        Aristopharma
                                    </option>

                                    <option value="Drug International">
                                        Drug International
                                    </option>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Category
                                </label>

                                <select name="category_id"
                                    class="form-control"
                                    required>

                                    <option value="">
                                        Select Category
                                    </option>

                                    <?php while ($cat = $categories->fetch_assoc()) : ?>

                                        <option value="<?php echo $cat['id']; ?>">

                                            <?php echo htmlspecialchars($cat['name']); ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Price
                                </label>

                                <input type="number"
                                    step="0.01"
                                    name="price"
                                    class="form-control"
                                    required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Stock Quantity
                                </label>

                                <input type="number"
                                    name="availability"
                                    class="form-control"
                                    required>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Description
                                </label>

                                <textarea name="description"
                                    class="form-control"
                                    rows="4"></textarea>

                            </div>

                            <div class="mb-3">

                                <label class="form-label">
                                    Medicine Image
                                </label>

                                <input type="file"
                                    name="image"
                                    class="form-control">

                            </div>

                            <button type="submit"
                                class="btn btn-success">

                                Add Medicine

                            </button>

                            <a href="../View/dashboard.php"
                                class="btn btn-secondary ms-2">

                                Back to Dashboard

                            </a>

                        </form>

                    </div>

                </div>

            </div>

        </div>

    </div>

</body>

</html>