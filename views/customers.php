<!DOCTYPE html>
<html>
<head>
    <title>Customer Directory</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold mb-2 text-gray-900">Registered Customer Base</h2>
    <a href="?action=dashboard" class="text-blue-600 hover:text-blue-800 hover:underline inline-block mb-6 text-sm font-medium">Back to Dashboard</a><br><br>
    
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="p-4 w-12">ID</th>
                    <th class="p-4 w-16 text-center">Profile Img</th>
                    <th class="p-4">Full Name</th>
                    <th class="p-4">Email</th>
                    <th class="p-4">Phone</th>
                    <th class="p-4">Address</th>
                    <th class="p-4">Registered At</th>
                    <th class="p-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-600">
                <?php foreach($customers as $cust): ?>
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4 font-mono text-xs text-gray-400"><?=$cust['id']?></td>
                    <td class="p-4">
                        <div class="flex justify-center">
                            <img src="<?=$cust['profile_picture']?>" width="40" onerror="this.src='https://placehold.co/40'" class="h-10 w-10 rounded-full object-cover border border-gray-200 bg-gray-100">
                        </div>
                    </td>
                    <td class="p-4 font-medium text-gray-900"><?=$cust['name']?></td>
                    <td class="p-4"><?=$cust['email']?></td>
                    <td class="p-4"><?=$cust['phone']?></td>
                    <td class="p-4 max-w-xs truncate" title="<?=$cust['address']?>"><?=$cust['address']?></td>
                    <td class="p-4 text-xs text-gray-500"><?=$cust['created_at']?></td>
                    <td class="p-4 text-right">
                        <a href="?action=customers&delete=<?=$cust['id']?>" onclick="return confirm('Cascade delete user cart items?')" class="text-red-600 hover:text-red-900 font-medium hover:underline inline-block text-xs uppercase tracking-wider">Remove Customer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>