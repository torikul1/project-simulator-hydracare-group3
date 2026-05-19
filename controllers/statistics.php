<?php
// controllers/statistics.php

error_reporting(E_ALL);
ini_set('display_errors', 1);

// 1. Initialize session safely at the absolute top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ==========================================================================
// 🛠️ ABSOLUTE PATH AUTO-RESOLVER (Fixes XAMPP Stream Errors)
// ==========================================================================
if (!isset($conn)) {
    $base_dir = dirname(__DIR__);

    if (file_exists($base_dir . '/config/db_config.php')) {
        require_once $base_dir . '/config/db_config.php';
    } elseif (file_exists($base_dir . '/model/db_config.php')) {
        require_once $base_dir . '/model/db_config.php';
    } elseif (file_exists($base_dir . '/Model/db_config.php')) {
        require_once $base_dir . '/Model/db_config.php';
    } elseif (file_exists($base_dir . '/config/database.php')) {
        require_once $base_dir . '/config/database.php';
    } else {
        die("Database connection missing! Searched base path: " . $base_dir);
    }
}

// Map fallback variable names if using $pdo instead of $conn
if (!isset($conn) && isset($pdo)) {
    $conn = $pdo;
}

// Security Check gateway
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=auth_required");
    exit();
}

$vendor_id = intval($_SESSION['user_id']);
$vendor_name = isset($_SESSION['name']) ? $_SESSION['name'] : 'vendor';

// Initialize container arrays
$months = [];
$sales = [];
$cat_names = [];
$cat_counts = [];

// ==========================================================================
// DATA ENGINE FOR MASTER DEVS ('ara') vs INDIVIDUAL VENDORS
// ==========================================================================
if ($vendor_name === 'ara') {

    // --- QUERY 1: Monthly Sales Trend (Global Analytics) ---
    $monthly_query = "SELECT 
                        DATE_FORMAT(o.order_date, '%Y-%m') as month,
                        SUM(oi.quantity * oi.unit_price) as total_sales,
                        COUNT(DISTINCT o.id) as order_count
                      FROM order_items oi 
                      JOIN medicines m ON oi.medicine_id = m.id 
                      JOIN orders o ON oi.order_id = o.id
                      GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
                      ORDER BY month DESC
                      LIMIT 6";
    $monthly_data = $conn->query($monthly_query);
    while ($row = $monthly_data->fetch_assoc()) {
        array_unshift($months, $row['month']);
        array_unshift($sales, $row['total_sales']);
    }

    // --- QUERY 2: Top Selling Medicines (Global Analytics) ---
    $top_query = "SELECT 
                    m.name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.unit_price) as revenue
                  FROM order_items oi 
                  JOIN medicines m ON oi.medicine_id = m.id 
                  GROUP BY m.id 
                  ORDER BY total_sold DESC 
                  LIMIT 5";
    $top_medicines = $conn->query($top_query);

    // --- QUERY 3: Category Distribution (Global Analytics) ---
    $category_query = "SELECT 
                        c.name as category,
                        COUNT(m.id) as medicine_count
                      FROM medicines m 
                      JOIN categories c ON m.category_id = c.id 
                      GROUP BY c.id";
    $categories = $conn->query($category_query);
    while ($cat = $categories->fetch_assoc()) {
        $cat_names[] = $cat['category'];
        $cat_counts[] = $cat['medicine_count'];
    }

    // --- QUERY 4: Stock Status Overview Summary (Global Analytics) ---
    $stock_query = "SELECT 
                      SUM(CASE WHEN availability = 0 THEN 1 ELSE 0 END) as out_of_stock,
                      SUM(CASE WHEN availability > 0 AND availability < 10 THEN 1 ELSE 0 END) as low_stock,
                      SUM(CASE WHEN availability >= 10 THEN 1 ELSE 0 END) as in_stock
                    FROM medicines";
    $stock_summary = $conn->query($stock_query)->fetch_assoc();
} else {
    // STANDARD ACCOUNT FALLBACK FILTERING: Segregates by vendor_id

    // --- QUERY 1 ---
    $monthly_query = "SELECT 
                        DATE_FORMAT(o.order_date, '%Y-%m') as month,
                        SUM(oi.quantity * oi.unit_price) as total_sales,
                        COUNT(DISTINCT o.id) as order_count
                      FROM order_items oi 
                      JOIN medicines m ON oi.medicine_id = m.id 
                      JOIN orders o ON oi.order_id = o.id
                      WHERE m.vendor_id = ? 
                      GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
                      ORDER BY month DESC
                      LIMIT 6";
    $stmt = $conn->prepare($monthly_query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $monthly_data = $stmt->get_result();
    while ($row = $monthly_data->fetch_assoc()) {
        array_unshift($months, $row['month']);
        array_unshift($sales, $row['total_sales']);
    }
    $stmt->close();

    // --- QUERY 2 ---
    $top_query = "SELECT 
                    m.name,
                    SUM(oi.quantity) as total_sold,
                    SUM(oi.quantity * oi.unit_price) as revenue
                  FROM order_items oi 
                  JOIN medicines m ON oi.medicine_id = m.id 
                  WHERE m.vendor_id = ? 
                  GROUP BY m.id 
                  ORDER BY total_sold DESC 
                  LIMIT 5";
    $stmt = $conn->prepare($top_query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $top_medicines = $stmt->get_result();

    // --- QUERY 3 ---
    $category_query = "SELECT 
                        c.name as category,
                        COUNT(m.id) as medicine_count
                      FROM medicines m 
                      JOIN categories c ON m.category_id = c.id 
                      WHERE m.vendor_id = ? 
                      GROUP BY c.id";
    $stmt = $conn->prepare($category_query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $categories = $stmt->get_result();
    while ($cat = $categories->fetch_assoc()) {
        $cat_names[] = $cat['category'];
        $cat_counts[] = $cat['medicine_count'];
    }
    $stmt->close();

    // --- QUERY 4 ---
    $stock_query = "SELECT 
                      SUM(CASE WHEN availability = 0 THEN 1 ELSE 0 END) as out_of_stock,
                      SUM(CASE WHEN availability > 0 AND availability < 10 THEN 1 ELSE 0 END) as low_stock,
                      SUM(CASE WHEN availability >= 10 THEN 1 ELSE 0 END) as in_stock
                    FROM medicines WHERE vendor_id = ?";
    $stmt = $conn->prepare($stock_query);
    $stmt->bind_param("i", $vendor_id);
    $stmt->execute();
    $stock_summary = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Fallback logic to avoid frontend layout crashes if tables are completely blank
$stock_summary['in_stock']     = $stock_summary['in_stock'] ?? 0;
$stock_summary['low_stock']    = $stock_summary['low_stock'] ?? 0;
$stock_summary['out_of_stock'] = $stock_summary['out_of_stock'] ?? 0;
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<link rel="stylesheet" href="../views/css/statistics.css">

<h2 class="mb-4">Statistics Dashboard</h2>

<div class="row">
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Monthly Sales Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="salesChart" height="300"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Stock Summary</h5>
            </div>
            <div class="card-body">
                <canvas id="stockChart" height="250"></canvas>
                <div class="mt-3">
                    <p><span class="badge bg-success" style="background-color:#28a745; color:white; padding:4px 8px; border-radius:4px;">In Stock (≥10)</span> <?php echo $stock_summary['in_stock']; ?> medicines</p>
                    <p><span class="badge bg-warning" style="background-color:#ffc107; color:black; padding:4px 8px; border-radius:4px;">Low Stock (<10)< /span> <?php echo $stock_summary['low_stock']; ?> medicines</p>
                    <p><span class="badge bg-danger" style="background-color:#dc3545; color:white; padding:4px 8px; border-radius:4px;">Out of Stock</span> <?php echo $stock_summary['out_of_stock']; ?> medicines</p>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Top Selling Medicines</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table" style="width:100%; border-collapse: collapse; text-align:left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ddd;">
                                <th>Medicine</th>
                                <th>Units Sold</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($top_medicines) && $top_medicines->num_rows > 0): ?>
                                <?php while ($top = $top_medicines->fetch_assoc()): ?>
                                    <tr style="border-bottom: 1px solid #eee;">
                                        <td><?php echo htmlspecialchars($top['name']); ?></td>
                                        <td><?php echo $top['total_sold']; ?></td>
                                        <td>৳<?php echo number_format($top['revenue'], 2); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3" class="text-center" style="padding:15px; text-align:center; color:#888;">No sales data available</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Category Distribution</h5>
            </div>
            <div class="card-body">
                <canvas id="categoryChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx1 = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(!empty($months) ? $months : ["No Data"]); ?>,
            datasets: [{
                label: 'Sales Amount (৳)',
                data: <?php echo json_encode(!empty($sales) ? $sales : [0]); ?>,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Amount (৳)'
                    }
                }
            }
        }
    });

    const ctx2 = document.getElementById('stockChart').getContext('2d');
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: ['In Stock (≥10)', 'Low Stock (<10)', 'Out of Stock'],
            datasets: [{
                data: [<?php echo $stock_summary['in_stock']; ?>, <?php echo $stock_summary['low_stock']; ?>, <?php echo $stock_summary['out_of_stock']; ?>],
                backgroundColor: ['#28a745', '#ffc107', '#dc3545']
            }]
        }
    });

    const ctx3 = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(!empty($cat_names) ? $cat_names : ["General"]); ?>,
            datasets: [{
                label: 'Number of Medicines',
                data: <?php echo json_encode(!empty($cat_counts) ? $cat_counts : [0]); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Count'
                    }
                }
            }
        }
    });
</script>

<?php
$footer_path = dirname(__DIR__) . '/Control/footer.php';
if (file_exists($footer_path)) {
    require_once $footer_path;
}
?>
<br><a href="../views/vendor_dashboard.php"><button style="padding:7px 15px; background:#1e293b; color:white; border:none; border-radius:4px; cursor:pointer;">Back to Dashboard</button></a>