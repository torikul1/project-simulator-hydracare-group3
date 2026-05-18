<!DOCTYPE html>
<html>
<head>
    <title>Order Streams & System History</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6 max-w-7xl mx-auto">
    
    <!-- Incoming Orders Section -->
    <h2 class="text-2xl font-bold mb-2 text-gray-900">Incoming Store Order Requests</h2>
    <a href="?action=dashboard" class="text-blue-600 hover:text-blue-800 hover:underline inline-block mb-6 text-sm font-medium">Back to Dashboard</a><br><br>

    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white mb-10">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="p-4 w-24">Order ID</th>
                    <th class="p-4">Customer</th>
                    <th class="p-4 w-32">Total Charge</th>
                    <th class="p-4">Shipping Location</th>
                    <th class="p-4 w-44">Date Placed</th>
                    <th class="p-4 w-36">Current Status</th>
                    <th class="p-4 w-48 text-right">Action Toggle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-600">
                <?php foreach($orders as $o): ?>
                <tr id="order-row-<?=$o['id']?>" class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4 font-mono text-xs text-gray-400">#<?=$o['id']?></td>
                    <td class="p-4 font-medium text-gray-900"><?=$o['customer_name']?></td>
                    <td class="p-4 font-semibold text-gray-900">$<?=number_format($o['total_amount'], 2)?></td>
                    <td class="p-4 max-w-xs truncate" title="<?=$o['shipping_address']?>"><?=$o['shipping_address']?></td>
                    <td class="p-4 text-xs text-gray-500"><?=$o['order_date']?></td>
                    <td class="p-4 font-bold text-xs tracking-wider" id="status-<?=$o['id']?>" style="color: <?=$o['status']=='pending'?'#d97706':($o['status']=='accepted'?'#16a34a':'#dc2626')?>">
                        <?=strtoupper($o['status'])?>
                    </td>
                    <td class="p-4 text-right space-x-2">
                        <?php if($o['status'] === 'pending'): ?>
                            <button onclick="updateOrderStatus(<?=$o['id']?>, 'accepted')" class="bg-green-50 text-green-700 hover:bg-green-100 font-medium text-xs px-3 py-1.5 rounded transition-colors border border-green-200">Accept</button>
                            <button onclick="updateOrderStatus(<?=$o['id']?>, 'rejected')" class="bg-red-50 text-red-700 hover:bg-red-100 font-medium text-xs px-3 py-1.5 rounded transition-colors border border-red-200">Reject</button>
                        <?php else: ?>
                            <span class="text-xs font-semibold text-gray-400 bg-gray-100 px-2.5 py-1 rounded select-none">Processed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <hr class="border-gray-200 my-8">
    
    <!-- Purchase Logs History Section -->
    <h2 class="text-2xl font-bold mb-6 text-gray-900">Global Customer Purchase Records (History Logs)</h2>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="p-4 w-24">Order ID</th>
                    <th class="p-4">Customer Identity</th>
                    <th class="p-4">Purchased Medicine Item</th>
                    <th class="p-4 w-28">Qty Ordered</th>
                    <th class="p-4 w-28">Unit Rate</th>
                    <th class="p-4 w-36">Transaction Total</th>
                    <th class="p-4 w-44">Completion Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-600">
                <?php foreach($history as $h): ?>
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4 font-mono text-xs text-gray-400">#<?=$h['order_id']?></td>
                    <td class="p-4">
                        <div class="font-medium text-gray-900"><?=$h['customer_name']?></div>
                        <div class="text-xs text-gray-400 font-normal"><?=$h['email']?></div>
                    </td>
                    <td class="p-4"><span class="bg-blue-50 text-blue-700 text-xs font-medium px-2.5 py-1 rounded border border-blue-100"><?=$h['medicine_name']?></span></td>
                    <td class="p-4 font-mono text-gray-700"><?=$h['quantity']?></td>
                    <td class="p-4 text-gray-500">$<?=number_format($h['unit_price'], 2)?></td>
                    <td class="p-4 font-semibold text-gray-900">$<?=number_format(($h['quantity'] * $h['unit_price']), 2)?></td>
                    <td class="p-4 text-xs text-gray-500"><?=$h['order_date']?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function updateOrderStatus(orderId, nextStatus) {
        if(!confirm("Change order status to " + nextStatus + "?")) return;
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "?action=update_order_ajax", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                if(response.success) {
                    let statusBox = document.getElementById("status-" + orderId);
                    statusBox.innerText = nextStatus.toUpperCase();
                    statusBox.style.color = nextStatus === 'accepted' ? '#16a34a' : '#dc2626';
                    alert(response.message);
                    window.location.reload(); 
                } else {
                    alert("Error processing updates: " + response.message);
                }
            }
        };
        xhr.send("order_id=" + orderId + "&status=" + nextStatus);
    }
    </script>
</body>
</html>