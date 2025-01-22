<?php
// Database connection (assuming you have this already)
require '../includes/config.php';


// Pagination
$items_per_page = 4;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// SQL query with LIMIT and OFFSET
$sql = "
    SELECT 
        p.id, 
        p.name, 
        p.quantity, 
        p.buy_price, 
        p.sale_price, 
        p.file_name,
        p.categorie_id,
        c.name AS category_name, 
        p.media_id, 
        p.date
    FROM products p
    LEFT JOIN categories c ON p.categorie_id = c.id
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get total number of products
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_pages = ceil($total_products / $items_per_page);

$content = ''; // Initialize content variable

$content .= '
    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="py-3 px-4 text-left border-b border-gray-300">ID</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Name</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Quantity</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Buy Price</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Sale Price</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Category</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Media ID</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Media Image</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Date</th>
                    <th class="py-3 px-4 text-center border-b border-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">';

if (count($products) > 0) {
    foreach ($products as $product) {
        $content .= '
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 border-b border-gray-300">' . $product['id'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($product['name']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['quantity'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">$' . number_format($product['buy_price'], 2) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">$' . number_format($product['sale_price'], 2) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['category_name'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['media_id'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">
                    <img src="uploads/' . $product['file_name'] . '" alt="' . $product['name'] . '" class="w-16 h-16 object-cover rounded-lg">
                </td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['date'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300 text-center">
                    <button onclick="openEditModal(' . $product['id'] . ', \'' . addslashes(htmlspecialchars($product['name'])) . '\', ' . $product['quantity'] . ', ' . $product['buy_price'] . ', ' . $product['sale_price'] . ', ' . $product['categorie_id'] . ', ' . $product['media_id'] . ', \'' . $product['date'] . '\')" class="text-blue-500 hover:underline">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="button" onclick="openDeleteModal(' . $product['id'] . ')" class="text-red-500 hover:underline ml-4">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </td>
            </tr>';
    }
} else {
    $content .= '
        <tr>
            <td colspan="10" class="py-6 px-4 text-center text-gray-500">No products found</td>
        </tr>';
}

$content .= '
            </tbody>
        </table>
    </div>';

// Add pagination buttons
$content .= '
    <div class="mt-6 flex justify-center">
        <div class="inline-flex rounded-md shadow-sm" role="group">
            <a href="?page=' . max(1, $current_page - 1) . '" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-l-lg hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700' . ($current_page == 1 ? ' opacity-50 cursor-not-allowed' : '') . '">
                Previous
            </a>
            <a href="?page=' . min($total_pages, $current_page + 1) . '" class="px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-200 rounded-r-md hover:bg-gray-100 hover:text-blue-700 focus:z-10 focus:ring-2 focus:ring-blue-700 focus:text-blue-700' . ($current_page == $total_pages ? ' opacity-50 cursor-not-allowed' : '') . '">
                Next
            </a>
        </div>
    </div>';

echo $content;

?>



<!-- Include the layout -->
<?php include '../layouts/layout.php'; ?>

<!-- Add Product Modal -->
<!-- Add Product Modal -->
<div id="addProductModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Product</h2>
        <form action="view_products.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="quantity" class="block text-sm font-medium text-gray-600">Quantity</label>
                <input type="number" name="quantity" id="quantity" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="buy_price" class="block text-sm font-medium text-gray-600">Buy Price</label>
                <input type="number" step="0.01" name="buy_price" id="buy_price" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="sale_price" class="block text-sm font-medium text-gray-600">Sale Price</label>
                <input type="number" step="0.01" name="sale_price" id="sale_price" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="categorie_id" class="block text-sm font-medium text-gray-600">Category</label>
                <select name="categorie_id" id="categorie_id" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
                    <?php
                    // Fetch all categories from the database
                    $stmt = $pdo->query("SELECT id, name FROM categories");
                    while ($categorie = $stmt->fetch()) {
                        echo '<option value="' . $categorie['id'] . '">' . $categorie['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="media_image" class="block text-sm font-medium">Upload Image</label>
                <input 
                    type="file" 
                    id="media_image" 
                    name="media_image" 
                    accept="image/*" 
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md"
                >
            </div>
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-600">Date</label>
                <input type="datetime-local" name="date" id="date" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="add_product" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all">Add Product</button>
                <button type="button" onclick="closeAddProductModal()" class="bg-gray-400 text-white px-4 py-2 rounded ml-2 hover:bg-gray-500 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<!-- Update Product Modal -->
<div id="updateProductModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Update Product</h2>
        <form action="view_products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id"> <!-- Hidden field for product ID -->
            <div class="mb-4">
                <label for="update_name" class="block text-sm font-medium text-gray-600">Name</label>
                <input type="text" name="name" id="update_name" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="update_quantity" class="block text-sm font-medium text-gray-600">Quantity</label>
                <input type="number" name="quantity" id="update_quantity" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="update_buy_price" class="block text-sm font-medium text-gray-600">Buy Price</label>
                <input type="number" step="0.01" name="buy_price" id="update_buy_price" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="update_sale_price" class="block text-sm font-medium text-gray-600">Sale Price</label>
                <input type="number" step="0.01" name="sale_price" id="update_sale_price" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="update_categorie_id" class="block text-sm font-medium text-gray-600">Category</label>
                <select name="categorie_id" id="update_categorie_id" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
                    <?php
                    // Fetch all categories from the database
                    $stmt = $pdo->query("SELECT id, name FROM categories");
                    while ($categorie = $stmt->fetch()) {
                        echo '<option value="' . $categorie['id'] . '">' . $categorie['name'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="update_media_image" class="block text-sm font-medium">Upload Image</label>
                <input 
                    type="file" 
                    id="update_media_image" 
                    name="media_image" 
                    accept="image/*" 
                    class="mt-1 block w-full p-2 border border-gray-300 rounded-md"
                >
            </div>
            <div class="mb-4">
                <label for="update_date" class="block text-sm font-medium text-gray-600">Date</label>
                <input type="datetime-local" name="date" id="update_date" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="update_product" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all">Update Product</button>
                <button type="button" onclick="closeUpdateProductModal()" class="bg-gray-400 text-white px-4 py-2 rounded ml-2 hover:bg-gray-500 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Product Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
        <p class="mb-4">Are you sure you want to delete this product?</p>
        <form action="view_products.php" method="POST" id="deleteForm">
            <input type="hidden" name="delete_id" id="delete_id">
            <div class="flex justify-end gap-2">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to open the edit modal
    function openEditModal(id, name, quantity, buyPrice, salePrice, categoryId, mediaId, date) {
    // Set values in the modal fields
    document.getElementById('product_id').value = id;
    document.getElementById('update_name').value = name;
    document.getElementById('update_quantity').value = quantity;
    document.getElementById('update_buy_price').value = buyPrice;
    document.getElementById('update_sale_price').value = salePrice;
    document.getElementById('update_categorie_id').value = categoryId;
    document.getElementById('update_date').value = date;

    // Show the modal
    document.getElementById('updateProductModal').classList.remove('hidden');
}


    // Function to close the edit modal
    function closeUpdateProductModal() {
    document.getElementById('updateProductModal').classList.add('hidden');
}


    // Function to open the add product modal
    function openAddProductModal() {
        document.getElementById('addProductModal').classList.remove('hidden');
    }

    // Function to close the add product modal
    function closeAddProductModal() {
        document.getElementById('addProductModal').classList.add('hidden');
    }

    // Function to open the delete modal
    function openDeleteModal(productId) {
        document.getElementById('delete_id').value = productId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    // Function to close the delete modal
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        let modals = [
            document.getElementById('addProductModal'),
            document.getElementById('editModal'),
            document.getElementById('deleteModal')
        ];
        
        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
</script>
<?php
include_once '../layouts/layout.php';
?>














