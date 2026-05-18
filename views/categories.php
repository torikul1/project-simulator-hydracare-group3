<!DOCTYPE html>
<html>
<head>
    <title>Category Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans p-6 max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-4 text-gray-900">Category Management (CRUD)</h2>
    <a href="?action=dashboard" class="text-blue-600 hover:text-blue-800 hover:underline inline-block mb-6">Back to Dashboard</a><br><br>

    <?php if(!empty($errors)) foreach($errors as $e) echo "<p class='text-red-600 bg-red-50 border border-red-200 rounded p-3 mb-4 font-medium'>$e</p>"; ?>

    <form method="POST" id="categoryForm" onsubmit="return validateCategoryForm()" class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 mb-8 flex flex-wrap items-end gap-4">
        <input type="hidden" name="id" id="cat_id">
        
        <div class="flex flex-col gap-1.5 flex-1 min-w-[200px]">
            <label class="text-sm font-semibold text-gray-700">Category Name:</label>
            <input type="text" name="name" id="cat_name" required class="border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        
        <div class="flex flex-col gap-1.5 min-w-[150px]">
            <label class="text-sm font-semibold text-gray-700">Segmentation Type:</label>
            <select name="category_type" id="cat_type" required class="border border-gray-300 rounded px-3 py-2 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="liquid">Liquid</option>
                <option value="solid">Solid</option>
            </select>
        </div>
        
        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2 rounded transition-colors duration-200 h-[42px]">Save Category</button>
    </form>

    <h3 class="text-xl font-semibold mb-3 text-gray-800">Existing Segments</h3>
    <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
        <table class="w-full text-left border-collapse bg-white">
            <thead>
                <tr class="bg-gray-100 border-b border-gray-200 text-sm font-semibold text-gray-700">
                    <th class="p-3 w-16">ID</th>
                    <th class="p-3">Name</th>
                    <th class="p-3 w-32">Type</th>
                    <th class="p-3 w-40">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-sm">
                <?php foreach($categories as $c): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="p-3 text-gray-500"><?=$c['id']?></td>
                    <td class="p-3 font-medium text-gray-900"><?=$c['name']?></td>
                    <td class="p-3">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium <?=$c['category_type'] === 'liquid' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'?>">
                            <?=ucfirst($c['category_type'])?>
                        </span>
                    </td>
                    <td class="p-3 flex gap-3">
                        <button onclick="editCategory(<?=$c['id']?>, '<?=$c['name']?>', '<?=$c['category_type']?>')" class="text-indigo-600 hover:text-indigo-900 font-medium hover:underline">Edit</button>
                        <a href="?action=categories&delete=<?=$c['id']?>" onclick="return confirm('Are you sure?')" class="text-red-600 hover:text-red-900 font-medium hover:underline">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script>
    function editCategory(id, name, type) {
        document.getElementById('cat_id').value = id;
        document.getElementById('cat_name').value = name;
        document.getElementById('cat_type').value = type;
    }
    function validateCategoryForm() {
        let name = document.getElementById('cat_name').value.trim();
        if(name === "") { alert("Category name cannot be blank."); return false; }
        return true;
    }
    </script>
</body>
</html>