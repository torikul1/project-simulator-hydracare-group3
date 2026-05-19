<!DOCTYPE html>
<html>
<head>
    <title>Orders & History</title>
    <link rel="stylesheet" type="text/css" href="../views/css/admin.css">
</head>
<body>
    <h2>Purchase Requests & Complete History</h2>
    <nav>
        <a href="?action=dashboard">Dashboard</a>
        <a href="?action=categories">Manage Categories</a>
        <a href="?action=medicines">Manage Medicines</a>
        <a href="?action=customers">Customers</a>
        <a href="?action=orders">Orders & History</a>
    </nav>
    <hr>

    <h3>Active Checkout Requests</h3>
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Date</th>
                <th>Total Cost</th>
                <th>Current Status</th>
                <th>Action Interface</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($orders as $o): ?>
            <tr id="order-row-<?=$o['id']?>">
                <td><?=$o['id']?></td>
                <td><?=$o['customer_name']?></td>
                <td><?=$o['order_date']?></td>
                <td>$<?=number_format($o['total_amount'], 2)?></td>
                <td><span id="status-text-<?=$o['id']?>" class="status-<?=$o['status']?>"><?=ucfirst($o['status'])?></span></td>
                <td>
                    <?php if($o['status'] === 'pending'): ?>
                        <div id="btn-group-<?=$o['id']?>">
                            <button class="btn" onclick="processOrder(<?=$o['id']?>, 'accepted')">Accept</button>
                            <button class="btn btn-danger" onclick="processOrder(<?=$o['id']?>, 'rejected')">Reject</button>
                        </div>
                    <?php else: ?>
                        <span>Locked</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Completed Sales History Log</h3>
    <table>
        <thead>
            <tr>
                <th>Invoice Code</th>
                <th>Date</th>
                <th>Customer Name</th>
                <th>Medicine Target</th>
                <th>Qty</th>
                <th>Rate Paid</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($history as $h): ?>
            <tr>
                <td>#ORD-<?=$h['order_id']?></td>
                <td><?=$h['order_date']?></td>
                <td><?=$h['customer_name']?></td>
                <td><?=$h['medicine_name']?></td>
                <td><?=$h['quantity']?> items</td>
                <td>$<?=number_format($h['unit_price'], 2)?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
    function processOrder(id, statusTarget) {
        const formData = new FormData();
        formData.append('order_id', id);
        formData.append('status', statusTarget);

        fetch('?action=update_order_ajax', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Order updated to ' + statusTarget);
                const statusSpan = document.getElementById('status-text-' + id);
                statusSpan.textContent = statusTarget.charAt(0).toUpperCase() + statusTarget.slice(1);
                statusSpan.className = 'status-' + statusTarget;
                
                const btnGroup = document.getElementById('btn-group-' + id);
                if(btnGroup) btnGroup.innerHTML = '<span>Locked</span>';
            } else {
                alert('Error processing status change request: ' + data.message);
            }
        })
        .catch(err => console.error('AJAX fault:', err));
    }
    </script>
</body>
</html>