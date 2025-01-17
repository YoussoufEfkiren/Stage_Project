<?php
//view_products.php
session_start();

// Check if the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details from the session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];  // Assuming username is stored in session
$user_level = $_SESSION['user_level'];  // Assuming user_level is stored in session

// Map user level to group
switch ($user_level) {
    case 1:
        $user_group = 'Admin';
        break;
    case 2:
        $user_group = 'Manager';
        break;
    case 3:
        $user_group = 'User';
        break;
    default:
        $user_group = 'Guest';
        break;
}

$page_title = 'View Products';

// Include the database connection
require_once '../includes/config.php';

// Fetch all products
$sql = "SELECT id, name, quantity, buy_price, sale_price, categorie_id, media_id, date FROM products";
$stmt = $pdo->query($sql);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deleting a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $product_id_to_delete = $_POST['delete_id'];

    // Validate the product ID
    if (is_numeric($product_id_to_delete)) {
        $sql = "DELETE FROM products WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':id' => $product_id_to_delete])) {
            $_SESSION['message'] = 'Product deleted successfully!';
            header('Location: view_products.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error deleting product!';
        }
    }
}

// Handle adding a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Collect form data
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $buy_price = $_POST['buy_price'];
    $sale_price = $_POST['sale_price'];
    $categorie_id = $_POST['categorie_id'];
    $media_id = $_POST['media_id'];
    $date = $_POST['date'];

    // Prepare SQL query to insert new product
    $sql = "INSERT INTO products (name, quantity, buy_price, sale_price, categorie_id, media_id, date) 
            VALUES (:name, :quantity, :buy_price, :sale_price, :categorie_id, :media_id, :date)";
    $stmt = $pdo->prepare($sql);

    // Execute the query with the form data
    if ($stmt->execute([
        ':name' => $name,
        ':quantity' => $quantity,
        ':buy_price' => $buy_price,
        ':sale_price' => $sale_price,
        ':categorie_id' => $categorie_id,
        ':media_id' => $media_id,
        ':date' => $date
    ])) {
        $_SESSION['message'] = 'Product added successfully!';
        header('Location: view_products.php');
        exit;
    } else {
        $_SESSION['message'] = 'Error adding product!';
    }
}

// Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $product_id = $_POST['id'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $buy_price = $_POST['buy_price'];
    $sale_price = $_POST['sale_price'];
    $categorie_id = $_POST['categorie_id'];
    $media_id = $_POST['media_id'];
    $date = $_POST['date'];

    // Validate the product ID
    if (!is_numeric($product_id)) {
        die('Invalid product ID!');
    }

    // Update the product details in the database
    $sql = "UPDATE products SET 
            name = :name, 
            quantity = :quantity, 
            buy_price = :buy_price, 
            sale_price = :sale_price,
            categorie_id = :categorie_id, 
            media_id = :media_id, 
            date = :date 
            WHERE id = :id";
    
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([
        ':name' => $name,
        ':quantity' => $quantity,
        ':buy_price' => $buy_price,
        ':sale_price' => $sale_price,
        ':categorie_id' => $categorie_id,
        ':media_id' => $media_id,
        ':date' => $date,
        ':id' => $product_id
    ])) {
        $_SESSION['message'] = 'Product updated successfully!';
        header('Location: view_products.php');
        exit;
    } else {
        $_SESSION['message'] = 'Error updating product!';
    }
}

// Start building the content
$content = '';

$content .= '
    <div class="p-6 bg-gray-50 min-h-screen">
        <h1 class="text-4xl font-bold mb-6 text-gray-700">View Products</h1>
        
        <!-- Add New Product Button -->
        <button onclick="openAddProductModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg mb-6 shadow hover:bg-blue-700 transition-all">
            <i class="fas fa-plus mr-2"></i> Add New Product
        </button>';

if (isset($_SESSION['message'])) {
    $content .= '<div class="text-green-600 p-4 bg-green-100 border border-green-300 rounded mb-4 shadow">
        ' . $_SESSION['message'] . '
    </div>';
    unset($_SESSION['message']);
}

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
                <td class="py-3 px-4 border-b border-gray-300">' . $product['categorie_id'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['media_id'] . '</td>
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
            <td colspan="9" class="py-6 px-4 text-center text-gray-500">No products found</td>
        </tr>';
}

$content .= '
                </tbody>
            </table>
        </div>
    </div>';

?>

<!-- Include the layout -->
<?php include '../layouts/layout.php'; ?>

<!-- Add Product Modal -->
<div id="addProductModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Product</h2>
        <form action="view_products.php" method="POST">
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
                <label for="categorie_id" class="block text-sm font-medium text-gray-600">Category ID</label>
                <input type="number" name="categorie_id" id="categorie_id" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="mb-4">
                <label for="media_id" class="block text-sm font-medium text-gray-600">Media ID</label>
                <input type="number" name="media_id" id="media_id" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
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
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-md w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Edit Product</h2>
        <form action="view_products.php" method="POST">
            <input type="hidden" name="id" id="edit_id">
            <div class="mb-4">
                <label for="edit_name" class="block text-sm font-medium">Name</label>
                <input type="text" name="name" id="edit_name" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="edit_quantity" class="block text-sm font-medium">Quantity</label>
                <input type="number" name="quantity" id="edit_quantity" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="edit_buy_price" class="block text-sm font-medium">Buy Price</label>
                <input type="number" step="0.01" name="buy_price" id="edit_buy_price" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="edit_sale_price" class="block text-sm font-medium">Sale Price</label>
                <input type="number" step="0.01" name="sale_price" id="edit_sale_price" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="edit_categorie_id" class="block text-sm font-medium">Category ID</label>
                <input type="number" name="categorie_id" id="edit_categorie_id" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="edit_media_id" class="block text-sm font-medium">Media ID</label>
                <input type="number" name="media_id" id="edit_media_id" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="mb-4">
                <label for="edit_date" class="block text-sm font-medium">Date</label>
                <input type="datetime-local" name="date" id="edit_date" class="w-full px-4 py-2 border border-gray-300 rounded">
            </div>
            <div class="flex justify-end gap-2">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
                <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
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
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_quantity').value = quantity;
        document.getElementById('edit_buy_price').value = buyPrice;
        document.getElementById('edit_sale_price').value = salePrice;
        document.getElementById('edit_categorie_id').value = categoryId;
        document.getElementById('edit_media_id').value = mediaId;
        document.getElementById('edit_date').value = date.replace(' ', 'T'); // Convert date format for datetime-local input
        document.getElementById('editModal').classList.remove('hidden');
    }

    // Function to close the edit modal
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
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
