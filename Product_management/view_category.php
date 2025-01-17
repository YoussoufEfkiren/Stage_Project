<?php
// view_category.php
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

$page_title = 'View Categories';

// Include the database connection
require_once '../includes/config.php';

// Fetch all categories
$sql = "SELECT id, name FROM categories";
$stmt = $pdo->query($sql);
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':name' => $name])) {
            $_SESSION['message'] = 'Category added successfully!';
            header('Location: view_category.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error adding category!';
        }
    }
}

// Handle updating a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $category_id = $_POST['edit_id'];
    $name = trim($_POST['name']);
    if (!empty($category_id) && !empty($name)) {
        $sql = "UPDATE categories SET name = :name WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':name' => $name, ':id' => $category_id])) {
            $_SESSION['message'] = 'Category updated successfully!';
            header('Location: view_category.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error updating category!';
        }
    }
}

// Handle deleting a category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $category_id_to_delete = $_POST['delete_id'];
    if (is_numeric($category_id_to_delete)) {
        $sql = "DELETE FROM categories WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':id' => $category_id_to_delete])) {
            $_SESSION['message'] = 'Category deleted successfully!';
            header('Location: view_category.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error deleting category!';
        }
    }
}

// Start building the content
$content = '';

$content .= '
    <div class="p-6 bg-gray-50 min-h-screen">
        <h1 class="text-4xl font-bold mb-6 text-gray-700">View Categories</h1>
        
        <!-- Add New Category Button -->
        <button onclick="openAddCategoryModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg mb-6 shadow hover:bg-blue-700 transition-all">
            <i class="fas fa-plus mr-2"></i> Add New Category
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
                        <th class="py-3 px-4 text-left border-b border-gray-300">Category Name</th>
                        <th class="py-3 px-4 text-center border-b border-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">';

if (count($categories) > 0) {
    foreach ($categories as $category) {
        $content .= '
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 border-b border-gray-300">' . $category['id'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($category['name']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300 text-center">
                    <button onclick="openEditCategoryModal(' . $category['id'] . ', \'' . addslashes(htmlspecialchars($category['name'])) . '\')" class="text-blue-500 hover:underline">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="button" onclick="openDeleteCategoryModal(' . $category['id'] . ')" class="text-red-500 hover:underline ml-4">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </td>
            </tr>';
    }
} else {
    $content .= '
        <tr>
            <td colspan="3" class="py-6 px-4 text-center text-gray-500">No categories found</td>
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

<!-- Add Category Modal -->
<div id="addCategoryModal" class="fixed inset-0 flex items-center justify-center bg-gray-900 bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
        <h2 class="text-xl font-semibold text-gray-700 mb-4">Add New Category</h2>
        <form action="view_category.php" method="POST">
            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-600">Category Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-2 focus:ring-blue-400" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="add_category" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition-all">Add Category</button>
                <button type="button" onclick="closeAddCategoryModal()" class="bg-gray-400 text-white px-4 py-2 rounded ml-2 hover:bg-gray-500 transition-all">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Category Modal -->
<div id="editCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-md w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Edit Category</h2>
        <form action="view_category.php" method="POST">
            <input type="hidden" name="edit_id" id="edit_category_id">
            <div class="mb-4">
                <label for="edit_name" class="block text-sm font-medium">Category Name</label>
                <input type="text" name="name" id="edit_name" class="w-full px-4 py-2 border border-gray-300 rounded" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
                <button type="button" onclick="closeEditCategoryModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Category Modal -->
<div id="deleteCategoryModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
        <p class="mb-4">Are you sure you want to delete this category?</p>
        <form action="view_category.php" method="POST" id="deleteCategoryForm">
            <input type="hidden" name="delete_id" id="delete_category_id">
            <div class="flex justify-end">
                <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
                <button type="button" onclick="closeDeleteCategoryModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Cancel</button>
            </div </form>
    </div>
</div>

<script>
    function openEditCategoryModal(id, name) {
        document.getElementById('edit_category_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('editCategoryModal').classList.remove('hidden');
    }

    function closeEditCategoryModal() {
        document.getElementById('editCategoryModal').classList.add('hidden');
    }

    function openAddCategoryModal() {
        document.getElementById('addCategoryModal').classList.remove('hidden');
    }

    function closeAddCategoryModal() {
        document.getElementById('addCategoryModal').classList.add('hidden');
    }

    function openDeleteCategoryModal(categoryId) {
        document.getElementById('delete_category_id').value = categoryId;
        document.getElementById('deleteCategoryModal').classList.remove('hidden');
    }

    function closeDeleteCategoryModal() {
        document.getElementById('deleteCategoryModal').classList.add('hidden');
    }

    window.onclick = function(event) {
        let modals = [
            document.getElementById('addCategoryModal'),
            document.getElementById('editCategoryModal'),
            document.getElementById('deleteCategoryModal')
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