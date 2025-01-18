<?php
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

$sql = "
    SELECT p.id, p.name, p.quantity, p.buy_price, p.sale_price, p.categorie_id, p.media_id, p.date,
           c.name AS category_name, m.file_name AS media_name
    FROM products p
    LEFT JOIN categories c ON p.categorie_id = c.id
    LEFT JOIN media m ON p.media_id = m.id
";

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
                        <th class="py-3 px-4 text-left border-b border-gray-300">Media</th>
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
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($product['category_name']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($product['media_name']) . '</td>
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

echo $content;
?>

<!-- Modal for adding a new product -->
<div id="addProductModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-xl font-semibold mb-4">Add New Product</h2>
        <form method="POST" action="view_products.php">
            <label for="name" class="block mb-2 text-gray-700">Name:</label>
            <input type="text" id="name" name="name" class="w-full p-2 border border-gray-300 rounded mb-4" required>

            <label for="quantity" class="block mb-2 text-gray-700">Quantity:</label>
            <input type="number" id="quantity" name="quantity" class="w-full p-2 border border-gray-300 rounded mb-4" required>

            <label for="buy_price" class="block mb-2 text-gray-700">Buy Price:</label>
            <input type="number" step="0.01" id="buy_price" name="buy_price" class="w-full p-2 border border-gray-300 rounded mb-4" required>

            <label for="sale_price" class="block mb-2 text-gray-700">Sale Price:</label>
            <input type="number" step="0.01" id="sale_price" name="sale_price" class="w-full p-2 border border-gray-300 rounded mb-4" required>

            <label for="categorie_id" class="block mb-2 text-gray-700">Category:</label>
            <select id="categorie_id" name="categorie_id" class="w-full p-2 border border-gray-300 rounded mb-4" required>
                <?php
                $categories = $pdo->query("SELECT id, name FROM categories")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($categories as $category) {
                    echo '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
                }
                ?>
            </select>

            <label for="media_id" class="block mb-2 text-gray-700">Media:</label>
            <select id="media_id" name="media_id" class="w-full p-2 border border-gray-300 rounded mb-4" required>
                <?php
                $media = $pdo->query("SELECT id, file_name FROM media")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($media as $item) {
                    echo '<option value="' . $item['id'] . '">' . $item['file_name'] . '</option>';
                }
                ?>
            </select>

            <label for="date" class="block mb-2 text-gray-700">Date:</label>
            <input type="date" id="date" name="date" class="w-full p-2 border border-gray-300 rounded mb-4" required>

            <div class="flex justify-end space-x-4">
                <button type="button" class="bg-gray-500 text-white px-4 py-2 rounded" onclick="closeAddProductModal()">Cancel</button>
                <button type="submit" name="add_product" class="bg-blue-600 text-white px-4 py-2 rounded">Add Product</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddProductModal() {
        document.getElementById('addProductModal').classList.remove('hidden');
    }

    function closeAddProductModal() {
        document.getElementById('addProductModal').classList.add('hidden');
    }
</script>
