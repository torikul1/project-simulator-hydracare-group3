<?php
require_once '../Control/header.php';
require_once '../Model/db_config.php';

$vendor_id = $_SESSION['user_id'];

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
$months = [];
$sales = [];
while($row = $monthly_data->fetch_assoc()) {
    array_unshift($months, $row['month']);
    array_unshift($sales, $row['total_sales']);
}

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
$cat_names = [];
$cat_counts = [];
while($cat = $categories->fetch_assoc()) {
    $cat_names[] = $cat['category'];
    $cat_counts[] = $cat['medicine_count'];
}

$stock_query = "SELECT 
                  SUM(CASE WHEN availability = 0 THEN 1 ELSE 0 END) as out_of_stock,
                  SUM(CASE WHEN availability > 0 AND availability < 10 THEN 1 ELSE 0 END) as low_stock,
                  SUM(CASE WHEN availability >= 10 THEN 1 ELSE 0 END) as in_stock
                FROM medicines WHERE vendor_id = ?";
$stmt = $conn->prepare($stock_query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$stock_summary = $stmt->get_result()->fetch_assoc();
?>

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
                    <p><span class="badge bg-success">In Stock (≥10)</span> <?php echo $stock_summary['in_stock']; ?> medicines</p>
                    <p><span class="badge bg-warning">Low Stock (<10)</span> <?php echo $stock_summary['low_stock']; ?> medicines</p>
                    <p><span class="badge bg-danger">Out of Stock</span> <?php echo $stock_summary['out_of_stock']; ?> medicines</p>
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
                    <table class="table">
                        <thead>
                            <tr><th>Medicine</th><th>Units Sold</th><th>Revenue</th></tr>
                        </thead>
                        <tbody>
                            <?php while($top = $top_medicines->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($top['name']); ?></td>
                                <td><?php echo $top['total_sold']; ?></td>
                                <td>$<?php echo number_format($top['revenue'], 2); ?></td>
                            </tr>
                            <?php endwhile; ?>
                            <?php if($top_medicines->num_rows == 0): ?>
                            <tr><td colspan="3" class="text-center">No sales data</td></tr>
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
        labels: <?php echo json_encode($months); ?>,
        datasets: [{
            label: 'Sales Amount ($)',
            data: <?php echo json_encode($sales); ?>,
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
                    text: 'Amount ($)'
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
        labels: <?php echo json_encode($cat_names); ?>,
        datasets: [{
            label: 'Number of Medicines',
            data: <?php echo json_encode($cat_counts); ?>,
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

<?php require_once '../Control/footer.php'; ?>
<br><a href="../View/dashboard.php"><button>Back to Dashboard</button></a>
