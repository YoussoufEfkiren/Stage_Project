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

// User group setup (example):
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

// Dynamic content
$page_title = "Dashboard"; // Adjust based on actual content

// Build dynamic content with statistics
$content = '
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
    <!-- Users -->
    <div class="bg-blue-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-xl font-medium">Total Users</h2>
        <p class="text-4xl font-bold">' . $user_count . '</p>
    </div>
    <!-- Products -->
    <div class="bg-green-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-xl font-medium">Total Products</h2>
        <p class="text-4xl font-bold">' . $product_count . '</p>
    </div>
    <!-- Sales -->
    <div class="bg-yellow-500 text-white p-4 rounded-lg shadow-md">
        <h2 class="text-xl font-medium">Total Sales</h2>
        <p class="text-4xl font-bold">$' . number_format($total_sales, 2) . '</p>
    </div>
</div>
<hr class="my-6 border-gray-300">
'
;

include '../layouts/layout.php';
?>
