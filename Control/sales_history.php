<?php
require_once '../Control/header.php';
require_once '../Model/db_config.php';

$vendor_id = $_SESSION['user_id'];

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

$count_query = "SELECT COUNT(*) as total FROM order_items oi 
                JOIN medicines m ON oi.medicine_id = m.id 
                WHERE m.vendor_id = ?";
$stmt = $conn->prepare($count_query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$total_records = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_records / $limit);

$query = "SELECT 
            oi.id, oi.quantity, oi.unit_price, 
            (oi.quantity * oi.unit_price) as total_price,
            o.order_date, o.status,
            m.name as medicine_name,
            u.name as customer_name
          FROM order_items oi 
          JOIN medicines m ON oi.medicine_id = m.id 
          JOIN orders o ON oi.order_id = o.id
          JOIN users u ON o.user_id = u.id
          WHERE m.vendor_id = ? 
          ORDER BY o.order_date DESC 
          LIMIT ? OFFSET ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("iii", $vendor_id, $limit, $offset);
$stmt->execute();
$sales = $stmt->get_result();

$summary_query = "SELECT 
                    SUM(oi.quantity) as total_units_sold,
                    SUM(oi.quantity * oi.unit_price) as total_revenue,
                    COUNT(DISTINCT oi.order_id) as total_orders
                  FROM order_items oi 
                  JOIN medicines m ON oi.medicine_id = m.id 
                  WHERE m.vendor_id = ?";
$stmt = $conn->prepare($summary_query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$summary = $stmt->get_result()->fetch_assoc();
?>

<h2 class="mb-4">Sales History</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6>Total Revenue</h6>
                <h3>$<?php echo number_format($summary['total_revenue'] ?? 0, 2); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6>Total Units Sold</h6>
                <h3><?php echo number_format($summary['total_units_sold'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>Total Orders</h6>
                <h3><?php echo number_format($summary['total_orders'] ?? 0); ?></h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Medicine</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th>Order Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($sale = $sales->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo date('Y-m-d', strtotime($sale['order_date'])); ?></td>
                        <td><?php echo htmlspecialchars($sale['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($sale['medicine_name']); ?></td>
                        <td><?php echo $sale['quantity']; ?></td>
                        <td>$<?php echo number_format($sale['unit_price'], 2); ?></td>
                        <td><strong>$<?php echo number_format($sale['total_price'], 2); ?></strong></td>
                        <td>
                            <span class="badge bg-<?php echo $sale['status'] == 'accepted' ? 'success' : ($sale['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                <?php echo $sale['status']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($sales->num_rows == 0): ?>
                    <tr><td colspan="7" class="text-center">No sales yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if($total_pages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../Control/footer.php'; ?>
<br><a href="../View/dashboard.php"><button>Back to Dashboard</button></a>
