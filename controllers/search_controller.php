<?php
require_once '../config/db_config.php';

$search = isset($_GET['query']) ? trim($_GET['query']) : '';

if (!empty($search)) {
    $query = "SELECT * FROM medicines WHERE name LIKE ? OR vendor_name LIKE ? OR description LIKE ?";
    $stmt = $conn->prepare($query);
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
} else {
    $query = "SELECT * FROM medicines";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {


        $display_img = str_replace('public/', '', $row['image_path']);
        if (empty($display_img) || !file_exists("../views/public/" . $display_img)) {
            $display_img = "public/hydracarelogo.png";
        } else {
            $display_img = "public/" . $display_img;
        }

        // Inside controllers/search_controller.php inside the while loop:

        echo '
        <div class="medicine-card">
            <div class="card-img-wrapper">
                <img src="' . htmlspecialchars($display_img) . '" alt="' . htmlspecialchars($row['name']) . '" class="med-thumb">
            </div>
            <div class="med-details">
                <span class="vendor-badge">' . htmlspecialchars($row['vendor_name']) . '</span>
                <h3>' . htmlspecialchars($row['name']) . '</h3>
                <p class="med-desc">' . htmlspecialchars($row['description']) . '</p>
                
                <div class="med-meta">
                    <p class="price">৳' . number_format($row['price'], 2) . '</p>
                    <p class="stock ' . ($row['availability'] > 0 ? 'in-stock' : 'out-of-stock') . '">
                        ' . ($row['availability'] > 0 ? 'Available: ' . $row['availability'] : 'Out of Stock') . '
                    </p>
                </div>
            </div>
            
            <button class="btn-add-cart" onclick="addToCart(' . $row['id'] . ')" ' . ($row['availability'] <= 0 ? 'disabled' : '') . '>
                ' . ($row['availability'] > 0 ? 'Add to Orders' : 'Unavailable') . '
            </button>
        </div>';
    }
} else {
    echo '<div class="no-results">No medications match your search keywords.</div>';
}

$stmt->close();
