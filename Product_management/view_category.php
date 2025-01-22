<?php
// view_category.php

require_once '../includes/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentification_management/login.php');
    exit;
}

// Fetch all categories
try {
    $stmt = $pdo->query("SELECT id, name,description FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Handle Add Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['category_name'] ?? '');
    $description = trim($_POST['category_description'] ?? '');
    if ($name) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name), description VALUES (:name, :description)");
            $stmt->execute(['name' => $name]);
        } catch (PDOException $e) {
            die("Error adding category: " . $e->getMessage());
        }
    }
    header('Location: view_category.php');
    exit;
}

// Handle Delete Category
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmt->execute(['id' => $id]);
    } catch (PDOException $e) {
        die("Error deleting category: " . $e->getMessage());
    }
    header('Location: view_category.php');
    exit;
}

// Handle Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    $id = $_POST['edit_id'];
    $name = trim($_POST['edit_name'] ?? '');
    $description = trim($_POST['edit_description'] ?? '');

    if ($id && $name) {
        try {
            $stmt = $pdo->prepare("UPDATE categories SET name = :name , description = :description WHERE id = :id");
            $stmt->execute(['name' => $name,'description' => $description, 'id' => $id]);
        } catch (PDOException $e) {
            die("Error updating category: " . $e->getMessage());
        }
    }
    header('Location: view_category.php');
    exit;
}

// Set content to include in the dashboard layout
ob_start();
?>
<div class="p-6">
    <h1 class="text-3xl font-semibold mb-6 text-blue-800">Manage Categories</h1>

    <!-- Add Category Button -->
    <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
            class="bg-blue-600 text-white px-6 py-2 rounded-lg mb-6 shadow hover:bg-blue-700 transition-all">
            <i class="fas fa-plus mr-2"></i>
            Add Category
    </button>

    <!-- Categories Table -->
    <div class="mt-6 overflow-x-auto">
        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="py-3 px-4 text-left border-b border-gray-300">#</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Category Name</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Description</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($categories)): ?>
                    <?php foreach ($categories as $category): ?>
                        <tr class="border-t hover:bg-gray-100">
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($category['id']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($category['description']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300">
                                <button onclick="openEditModal(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>')" 
                                        class="text-blue-500 hover:underline">
                                        <i class="fas fa-edit"></i>Edit</button> 
                                <button onclick="openDeleteModal(<?php echo $category['id']; ?>)" 
                                        class="text-red-500 hover:underline ml-4">
                                        <i class="fas fa-trash-alt"></i>Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-4 text-gray-500">No categories found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Add Category</h2>
            <form method="POST">
                <input type="text" name="category_name" placeholder="Category Name" 
                       class="w-full p-2 border mb-4 rounded" required>
                <input type="text" name="category_description" placeholder="Category Description" 
                       class="w-full p-2 border mb-4 rounded" required>
                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="document.getElementById('addModal').classList.add('hidden')" 
                            class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" name="add_category" 
                            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Add</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4">Edit Category</h2>
            <form method="POST">
                <input type="hidden" name="edit_id" id="edit_id">
                <input type="text" name="edit_name" id="edit_name" 
                       placeholder="Category Name" class="w-full p-2 border mb-4 rounded" required>
                <input type="text" name="edit_description" id="edit_description" 
                       placeholder="Category Description" class="w-full p-2 border mb-4 rounded" required>
                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="document.getElementById('editModal').classList.add('hidden')" 
                            class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" name="edit_category" 
                            class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
        <div class="bg-white p-6 rounded shadow-lg w-96">
            <h2 class="text-xl font-semibold mb-4 text-red-600">Delete Category</h2>
            <p class="mb-4">Are you sure you want to delete this category?</p>
            <form method="GET">
                <input type="hidden" name="delete" id="delete_id">
                <div class="flex justify-end space-x-2">
                    <button type="button" 
                            onclick="document.getElementById('deleteModal').classList.add('hidden')" 
                            class="bg-gray-400 text-white px-4 py-2 rounded">Cancel</button>
                    <button type="submit" 
                            class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">Delete</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function openDeleteModal(id) {
            document.getElementById('delete_id').value = id;
            document.getElementById('deleteModal').classList.remove('hidden');
        }
    </script>
</div>
<?php
$content = ob_get_clean();
$page_title = 'View Categories';
require_once '../layouts/layout.php';
