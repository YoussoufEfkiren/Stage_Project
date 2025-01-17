<?php
// view_category.php

// Include necessary files and session handling
require_once '../includes/config.php';

// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentification_management/login.php');
    exit;
}

// Fetch all categories from the database
try {
    $stmt = $pdo->query("SELECT id, name FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}

// Set content to include in the dashboard layout
ob_start();
?>
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">View Categories</h1>
    <table class="min-w-full bg-white shadow-md rounded">
        <thead class="bg-blue-800 text-white">
            <tr>
                <th class="py-2 px-4 text-left">#</th>
                <th class="py-2 px-4 text-left">Category Name</th>
                <th class="py-2 px-4 text-left">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($categories) > 0): ?>
                <?php foreach ($categories as $category): ?>
                    <tr class="border-t">
                        <td class="py-2 px-4"><?php echo htmlspecialchars($category['id']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($category['name']); ?></td>
                        <td class="py-2 px-4">
                            <a href="edit_category.php?id=<?php echo $category['id']; ?>" class="text-blue-600 hover:underline">Edit</a> |
                            <a href="delete_category.php?id=<?php echo $category['id']; ?>" class="text-red-600 hover:underline" onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
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
<?php
$content = ob_get_clean();
$page_title = 'View Categories';

// Include the layout
require_once '../layouts/layout.php';
