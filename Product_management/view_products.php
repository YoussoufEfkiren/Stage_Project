<?php
session_start();

// Check if the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details from the session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];
$user_level = $_SESSION['user_level'];

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

// Pagination settings
$items_per_page = 6;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Get total number of products
$count_sql = "SELECT COUNT(*) as total FROM products";
$count_result = $pdo->query($count_sql);
$total_products = $count_result->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_products / $items_per_page);

// Fetch products with pagination
$sql = "
    SELECT 
        p.id, 
        p.name, 
        p.quantity, 
        p.buy_price, 
        p.sale_price, 
        p.file_name, 
        p.categorie_id, 
        p.supplier_id,
        c.name AS category_name, 
        s.name AS supplier_name,
        p.date
    FROM products p
    LEFT JOIN categories c ON p.categorie_id = c.id
    LEFT JOIN suppliers s ON p.supplier_id = s.id
    LIMIT :limit OFFSET :offset
";
$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch suppliers
$stmt = $pdo->prepare("SELECT * FROM suppliers");
$stmt->execute();
$suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deleting a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $product_id_to_delete = $_POST['delete_id'];

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
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $buy_price = $_POST['buy_price'];
    $sale_price = $_POST['sale_price'];
    $categorie_id = $_POST['categorie_id'];
    $date = $_POST['date'];
    $supplier_id = $_POST['supplier_id'];

    // Handle file upload
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES['media_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_extensions)) {
        $_SESSION['message'] = 'Invalid file type!';
        header('Location: view_products.php');
        exit;
    }

    $file_name = '';
    if (isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($_FILES['media_image']['name']);
        move_uploaded_file($_FILES['media_image']['tmp_name'], $upload_dir . $file_name);
    }

    $sql = "INSERT INTO products (name, quantity, buy_price, sale_price, categorie_id, file_name, date, supplier_id) 
            VALUES (:name, :quantity, :buy_price, :sale_price, :categorie_id, :file_name, :date, :supplier_id)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([
        ':name' => $name,
        ':quantity' => $quantity,
        ':buy_price' => $buy_price,
        ':sale_price' => $sale_price,
        ':categorie_id' => $categorie_id,
        ':supplier_id' => $supplier_id,
        ':file_name' => $file_name,
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
// Handle updating a product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $buy_price = $_POST['buy_price'];
    $sale_price = $_POST['sale_price'];
    $categorie_id = $_POST['categorie_id'];
    $supplier_id = $_POST['supplier_id'];
    $date = $_POST['date'];

    // Handle file upload
    $file_name = '';
    if (isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $file_name = basename($_FILES['media_image']['name']);
        move_uploaded_file($_FILES['media_image']['tmp_name'], $upload_dir . $file_name);
    }

    $sql = "UPDATE products 
            SET name = :name, quantity = :quantity, buy_price = :buy_price, 
                sale_price = :sale_price, categorie_id = :categorie_id, 
                supplier_id = :supplier_id, date = :date";

    if ($file_name) {
        $sql .= ", file_name = :file_name";
    }

    $sql .= " WHERE id = :product_id";
    $stmt = $pdo->prepare($sql);

    $params = [
        ':name' => $name,
        ':quantity' => $quantity,
        ':buy_price' => $buy_price,
        ':sale_price' => $sale_price,
        ':categorie_id' => $categorie_id,
        ':supplier_id' => $supplier_id,
        ':date' => $date,
        ':product_id' => $product_id
    ];

    if ($file_name) {
        $params[':file_name'] = $file_name;
    }

    if ($stmt->execute($params)) {
        $_SESSION['message'] = 'Product updated successfully!';
        header('Location: view_products.php');
        exit;
    } else {
        $_SESSION['message'] = 'Error updating product!';
    }
}

// Build the page content
$content = '
    <div class="p-6 bg-gray-50 min-h-screen">
        <h1 class="text-4xl font-bold mb-6 text-gray-700">View Products</h1>
        
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
                        <th class="py-3 px-4 text-left border-b border-gray-300">Media Image</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Supplier</th>
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
                <td class="py-3 px-4 border-b border-gray-300">
                    <img src="uploads/' . $product['file_name'] . '" alt="' . $product['name'] . '" class="w-16 h-16 object-cover rounded-lg">
                </td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['supplier_name'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . $product['date'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300 text-center">
                    <button onclick="openUpdateModal(' . $product['id'] . ', \'' . addslashes(htmlspecialchars($product['name'])) . '\', ' . $product['quantity'] . ', ' . $product['buy_price'] . ', ' . $product['sale_price'] . ', ' . $product['categorie_id'] . ', ' . $product['supplier_id'] . ', \'' . $product['date'] . '\')" class="text-blue-500 hover:underline">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="openDeleteModal(' . $product['id'] . ')" class="text-red-500 hover:underline ml-4">
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
        
        <!-- Pagination -->
        <div class="mt-6 flex justify-center items-center space-x-4">
            <a href="?page=1" 
               class="' . ($current_page <= 1 ? 'opacity-50 cursor-not-allowed' : '') . ' bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all">
                First
            </a>
            
            <a href="?page=' . max(1, $current_page - 1) . '" 
               class="' . ($current_page <= 1 ? 'opacity-50 cursor-not-allowed' : '') . ' bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all">
                Previous
            </a>
            
            <span class="text-gray-600">
                Page ' . $current_page . ' of ' . $total_pages . '
            </span>
            
            <a href="?page=' . min($total_pages, $current_page + 1) . '" 
               class="' . ($current_page >= $total_pages ? 'opacity-50 cursor-not-allowed' : '') . ' bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all">
                Next
            </a>
            
            <a href="?page=' . $total_pages . '" 
               class="' . ($current_page >= $total_pages ? 'opacity-50 cursor-not-allowed' : '') . ' bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition-all">
                Last
            </a>
        </div>
    </div>';

?>

<!-- Include the layout -->
<?php include '../layouts/layout.php'; ?>

<!-- Add Product Modal -->
<div id="addProductModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Product</h2>
        <form action="view_products.php" method="POST" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="<div class="mb-4">
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
                <label for="supplier_id" class="block text-sm font-medium">Supplier</label>
                <select id="supplier_id" name="supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
                    <option value="" disabled selected>Select a supplier</option>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['id']; ?>">
                            <?php echo $supplier['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="media_image" class="block text-sm font-medium">Upload Image</label>
                <input type="file" id="media_image" name="media_image" accept="image/*" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-600">Date</label>
                <input type="datetime-local" name="date" id="date" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="add_product" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Add Product</button>
                <button type="button" onclick="closeAddProductModal()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Update Product Modal -->
<!-- Update Product Modal -->
<div id="updateProductModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Update Product</h2>
        <form action="view_products.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="product_id" id="product_id">
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
                <label for="update_supplier_id" class="block text-sm font-medium text-gray-600">Supplier</label>
                <select name="supplier_id" id="update_supplier_id" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
                    <?php foreach ($suppliers as $supplier): ?>
                        <option value="<?php echo $supplier['id']; ?>">
                            <?php echo $supplier['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="update_media_image" class="block text-sm font-medium">Upload New Image (optional)</label>
                <input type="file" id="update_media_image" name="media_image" accept="image/*" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
            </div>
            <div class="mb-4">
                <label for="update_date" class="block text-sm font-medium text-gray-600">Date</label>
                <input type="datetime-local" name="date" id="update_date" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="update_product" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all">Update Product</button>
                <button type="button" onclick="closeUpdateProductModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2 hover:bg-gray-600 transition-all">Cancel</button>
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
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-all">Confirm</button>
                <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Function to open the update modal
    function openUpdateModal(id, name, quantity, buyPrice, salePrice, categoryId, supplierId, date) {
        // Populate the update form fields
        document.getElementById('product_id').value = id;
        document.getElementById('update_name').value = name;
        document.getElementById('update_quantity').value = quantity;
        document.getElementById('update_buy_price').value = buyPrice;
        document.getElementById('update_sale_price').value = salePrice;
        document.getElementById('update_categorie_id').value = categoryId;
        document.getElementById('update_supplier_id').value = supplierId;
        document.getElementById('update_date').value = date;

        // Show the modal
        document.getElementById('updateProductModal').classList.remove('hidden');
    }

    // Function to close the update modal
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
            document.getElementById('updateProductModal'),
            document.getElementById('deleteModal')
        ];

        modals.forEach(modal => {
            if (event.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
</script>