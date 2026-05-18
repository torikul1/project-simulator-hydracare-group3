<!DOCTYPE html>
<html>
<head>
    <title>Medicine Management</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6 max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold mb-2 text-gray-900">Medicine Inventory (Full CRUD)</h2>
    <a href="?action=dashboard" class="text-blue-600 hover:text-blue-800 hover:underline inline-block mb-6 text-sm font-medium">Back to Dashboard</a>

    <?php if(!empty($errors)) foreach($errors as $e) echo "<p class='text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4 font-medium text-sm'>$e</p>"; ?>

    <!-- Form Section -->
    <form method="POST" enctype="multipart/form-data" id="medForm" onsubmit="return validateMedicineForm()" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 mb-8">
        <input type="hidden" name="id" id="med_id">
        <input type="hidden" name="old_image" id="old_image">
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4">
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Name:</label> 
                <input type="text" name="name" id="name" required class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Category:</label> 
                <select name="category_id" id="category_id" required class="border border-gray-300 rounded px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <?php foreach($categories as $c): ?>
                        <option value="<?=$c['id']?>"><?=$c['name']?> (<?=$c['category_type']?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Vendor Name:</label> 
                <input type="text" name="vendor_name" id="vendor_name" required class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Price ($):</label> 
                <input type="number" step="0.01" name="price" id="price" required class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Stock Available:</label> 
                <input type="number" name="availability" id="availability" required class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>
            
            <div class="flex flex-col gap-1.5">
                <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Medicine Image (JPEG/PNG, ≤2MB):</label> 
                <input type="file" name="medicine_image" id="medicine_image" class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 file:cursor-pointer hover:file:bg-blue-100">
            </div>
        </div>

        <div class="flex flex-col gap-1.5 mb-5">
            <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">Description:</label> 
            <textarea name="description" id="description" rows="2" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
        </div>
        
        <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded transition-colors duration-200 text-sm shadow-sm">Save Stock Product</button>
    </form>

    <!-- Table Section -->
    <h3 class="text-xl font-semibold mb-3 text-gray-800">Product Inventory List</h3>
    <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-sm bg-white">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <th class="p-4 w-20 text-center">Image</th>
                    <th class="p-4">Name</th>
                    <th class="p-4">Category</th>
                    <th class="p-4">Vendor</th>
                    <th class="p-4 w-24">Price</th>
                    <th class="p-4 w-24">Stock</th>
                    <th class="p-4 w-36 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm text-gray-600">
                <?php foreach($medicines as $m): ?>
                <tr class="hover:bg-gray-50/70 transition-colors">
                    <td class="p-4">
                        <div class="flex justify-center">
                            <img src="<?=$m['image_path']?>" width="50" onerror="this.src='https://placehold.co/50'" class="h-12 w-12 rounded-lg object-cover border border-gray-200 bg-gray-50 shadow-sm">
                        </div>
                    </td>
                    <td class="p-4 font-medium text-gray-900"><?=$m['name']?></td>
                    <td class="p-4"><span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-1 rounded border border-gray-200"><?=$m['category_name']?></span></td>
                    <td class="p-4"><?=$m['vendor_name']?></td>
                    <td class="p-4 font-semibold text-gray-900">$<?=number_format($m['price'], 2)?></td>
                    <td class="p-4">
                        <span class="<?=$m['availability'] < 10 ? 'text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded' : 'text-gray-700'?>">
                            <?=$m['availability']?>
                        </span>
                    </td>
                    <td class="p-4 text-right space-x-3">
                        <button onclick="editMedicine(<?=htmlspecialchars(json_encode($m))?>)" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline">Edit</button>
                        <a href="?action=medicines&delete=<?=$m['id']?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900 font-medium hover:underline">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function editMedicine(m) {
        document.getElementById('med_id').value = m.id;
        document.getElementById('name').value = m.name;
        document.getElementById('category_id').value = m.category_id;
        document.getElementById('vendor_name').value = m.vendor_name;
        document.getElementById('price').value = m.price;
        document.getElementById('availability').value = m.availability;
        document.getElementById('description').value = m.description;
        document.getElementById('old_image').value = m.image_path;
    }

    function validateMedicineForm() {
        let price = parseFloat(document.getElementById('price').value);
        let stock = parseInt(document.getElementById('availability').value);
        let fileInput = document.getElementById('medicine_image');

        if (price <= 0) { alert("Price must be greater than 0."); return false; }
        if (stock < 0) { alert("Stock cannot be negative."); return false; }
        
        if (fileInput.files.length > 0) {
            let file = fileInput.files[0];
            if (file.size > 2 * 1024 * 1024) { alert("File size must be less than 2MB."); return false; }
        }
        return true;
    }
    </script>
</body>
</html>