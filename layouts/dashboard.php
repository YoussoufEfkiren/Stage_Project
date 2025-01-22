<?php
//dashboard.php

session_start();

// Ensure that the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch the user level and other details from the session
$user_level = $_SESSION['user_level'];
$user_name = $_SESSION['username']; // Display the username
$user_group = ''; // Define user group based on level

// User group setup based on user level:
if ($user_level == 1) {
    $user_group = 'Admin';
} elseif ($user_level == 2) {
    $user_group = 'Manager';
} else {
    $user_group = 'User';
}

// Include the database connection file
require_once('../includes/config.php');  // Include the database connection

// Fetch the count of users
$stmt_users = $pdo->query("SELECT COUNT(*) FROM users");
$user_count = $stmt_users->fetchColumn();

// Fetch the count of products
$stmt_products = $pdo->query("SELECT COUNT(*) FROM products");
$product_count = $stmt_products->fetchColumn();

// Fetch the total sales amount
$stmt_sales = $pdo->query("SELECT SUM(qty * price) FROM sales");
$total_sales = $stmt_sales->fetchColumn();

// Fetch the count of suppliers
$stmt_suppliers = $pdo->query("SELECT COUNT(*) FROM suppliers");
$supplier_count = $stmt_suppliers->fetchColumn();

// Fetch the count of categories
$stmt_categories = $pdo->query("SELECT COUNT(*) FROM categories");
$category_count = $stmt_categories->fetchColumn();

// Fetch statistics by categories
$stmt_category_stats = $pdo->query("
    SELECT c.name AS category_name, 
           COUNT(p.id) AS product_count, 
           SUM(s.qty * s.price) AS total_sales
    FROM categories c
    LEFT JOIN products p ON c.id = p.categorie_id
    LEFT JOIN sales s ON p.id = s.product_id
    GROUP BY c.id
");

$category_stats = [];
while ($row = $stmt_category_stats->fetch(PDO::FETCH_ASSOC)) {
    $category_stats[] = $row;
}

// Dynamic content
$page_title = "Dashboard"; // Adjust based on actual content

// Build dynamic content with statistics and icons inside cards
$content = '
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <!-- Users -->
    <div class="bg-cyan-600 text-white p-3 rounded-lg shadow-md flex flex-col items-center">
        <i class="fa-solid fa-users text-3xl mb-2"></i> <!-- Icon for users -->
        <h2 class="text-xs font-semibold">Total Users</h2>
        <p class="text-xl font-bold">' . $user_count . '</p>
    </div>
    <!-- Products -->
    <div class="bg-orange-600 text-white p-3 rounded-lg shadow-md flex flex-col items-center">
        <i class="fa-solid fa-cogs text-3xl mb-2"></i> <!-- Icon for products -->
        <h2 class="text-xs font-semibold">Total Products</h2>
        <p class="text-xl font-bold">' . $product_count . '</p>
    </div>
    <!-- Sales -->
    <div class="bg-purple-600 text-white p-3 rounded-lg shadow-md flex flex-col items-center">
        <i class="fa-solid fa-dollar-sign text-3xl mb-2"></i> <!-- Icon for sales -->
        <h2 class="text-xs font-semibold">Total Sales</h2>
        <p class="text-xl font-bold">$' . number_format($total_sales, 2) . '</p>
    </div>
    <!-- Suppliers -->
    <div class="bg-green-600 text-white p-3 rounded-lg shadow-md flex flex-col items-center">
        <i class="fa-solid fa-truck text-3xl mb-2"></i> <!-- Icon for suppliers -->
        <h2 class="text-xs font-semibold">Total Suppliers</h2>
        <p class="text-xl font-bold">' . $supplier_count . '</p>
    </div>
    <!-- Categories -->
    <div class="bg-yellow-600 text-white p-3 rounded-lg shadow-md flex flex-col items-center">
        <i class="fa-solid fa-tag text-3xl mb-2"></i> <!-- Icon for categories -->
        <h2 class="text-xs font-semibold">Total Categories</h2>
        <p class="text-xl font-bold">' . $category_count . '</p>
    </div>
</div>


<hr class="my-6 border-gray-300">

<h2 class="text-xl font-semibold mb-4">Product Statistics by Category</h2>

<!-- Category Statistics Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
';

$icons = ['fa-box', 'fa-tag', 'fa-chart-line', 'fa-cogs']; // Icons for categories
$colors = ['bg-teal-700', 'bg-indigo-600', 'bg-blue-600', 'bg-yellow-600']; // Colors for categories

foreach ($category_stats as $index => $category) {
    $button_color = $colors[$index % count($colors)];  // Cycle through colors
    $icon = $icons[$index % count($icons)];  // Cycle through icons
    $content .= '
    <div class="' . $button_color . ' text-white p-4 rounded-lg shadow-md flex flex-col items-start">
        <i class="fa-solid ' . $icon . ' text-3xl mb-3"></i>
        <h3 class="text-lg font-semibold text-left">' . htmlspecialchars($category['category_name']) . '</h3>
        <ul class="mt-2 space-y-1 text-left">
            <li class="text-sm">Products: <span class="text-start">' . $category['product_count'] . '</span></li>
            <li class="text-sm">Total Sales: <span class="text-start">$' . number_format($category['total_sales'], 2) . '</span></li>
        </ul>
    </div>';
}

$content .= '</div>'; // Close category stats grid

include '../layouts/layout.php';
?>
