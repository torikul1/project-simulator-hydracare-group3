<?php
require_once '../Control/header.php';
require_once '../Model/db_config.php';

$vendor_id = $_SESSION['user_id'];

$query = "SELECT DISTINCT 
            o.id, o.order_date, o.total_amount, o.status, o.shipping_address,
            u.name as customer_name, u.email as customer_email, u.phone as customer_phone
          FROM orders o 
          JOIN order_items oi ON o.id = oi.order_id 
          JOIN medicines m ON oi.medicine_id = m.id 
          JOIN users u ON o.user_id = u.id 
          WHERE m.vendor_id = ? 
          ORDER BY o.order_date DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $vendor_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<h2 class="mb-4">Orders (Containing My Medicines)</h2>

<div class="card">
    <div class="card-body">
        <?php if($orders->num_rows == 0): ?>
            <div class="alert alert-info">No orders found for your medicines.</div>
        <?php else: ?>
            <?php while($order = $orders->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-header bg-light">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Order #<?php echo $order['id']; ?></strong>
                                <br>
                                <small>Date: <?php echo date('F d, Y h:i A', strtotime($order['order_date'])); ?></small>
                            </div>
                            <div class="col-md-6 text-end">
                                <span class="badge bg-<?php echo $order['status'] == 'accepted' ? 'success' : ($order['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                    <?php echo strtoupper($order['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Customer Information</h6>
                                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
                                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['customer_email']); ?></p>
                                <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($order['customer_phone']); ?></p>
                                <p class="mb-0"><strong>Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Order Items (Your Medicines)</h6>
                                <?php
                                $items_query = "SELECT oi.quantity, oi.unit_price, m.name 
                                               FROM order_items oi 
                                               JOIN medicines m ON oi.medicine_id = m.id 
                                               WHERE oi.order_id = ? AND m.vendor_id = ?";
                                $items_stmt = $conn->prepare($items_query);
                                $items_stmt->bind_param("ii", $order['id'], $vendor_id);
                                $items_stmt->execute();
                                $items = $items_stmt->get_result();
                                $vendor_total = 0;
                                ?>
                                <table class="table table-sm">
                                    <thead>
                                        <tr><th>Medicine</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php while($item = $items->fetch_assoc()): 
                                            $item_total = $item['quantity'] * $item['unit_price'];
                                            $vendor_total += $item_total;
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                                            <td><?php echo $item['quantity']; ?></td>
                                            <td>$<?php echo number_format($item['unit_price'], 2); ?></td>
                                            <td>$<?php echo number_format($item_total, 2); ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr class="table-warning">
                                            <th colspan="3">Total from your items:</th>
                                            <th>$<?php echo number_format($vendor_total, 2); ?></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../Control/footer.php'; ?>
<br><a href="../View/dashboard.php"><button>Back to Dashboard</button></a>
