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
$stmt_sales = $pdo->query("SELECT SUM(qty * price) FROM purchases");
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
    LEFT JOIN purchases s ON p.id = s.product_id
    GROUP BY c.id
");

$category_stats = [];
while ($row = $stmt_category_stats->fetch(PDO::FETCH_ASSOC)) {
    $category_stats[] = $row;
}

$stmt_sales_by_product = $pdo->query("
    SELECT p.name AS product_name, 
           SUM(s.qty * s.price) AS total_sales
    FROM products p
    LEFT JOIN purchases s ON p.id = s.product_id
    GROUP BY p.id
    ORDER BY total_sales DESC
");

$product_sales_data = [];
while ($row = $stmt_sales_by_product->fetch(PDO::FETCH_ASSOC)) {
    $product_sales_data[] = [
        'product' => $row['product_name'],
        'sales' => $row['total_sales'] ?? 0
    ];
}

// Fetch sales statistics by category
$stmt_sales_by_category = $pdo->query("
    SELECT c.name AS category_name, 
           SUM(s.qty * s.price) AS total_sales
    FROM categories c
    LEFT JOIN products p ON c.id = p.categorie_id
    LEFT JOIN purchases s ON p.id = s.product_id
    GROUP BY c.id
");

$sales_data = [];
while ($row = $stmt_sales_by_category->fetch(PDO::FETCH_ASSOC)) {
    $sales_data[] = [
        'category' => $row['category_name'],
        'sales' => $row['total_sales'] ?? 0
    ];
}

// Fetch products by supplier
$stmt_products_by_supplier = $pdo->query("
    SELECT s.name AS supplier_name, 
           COUNT(p.id) AS product_count
    FROM suppliers s
    LEFT JOIN products p ON s.id = p.supplier_id
    GROUP BY s.id
");

$supplier_data = [];
while ($row = $stmt_products_by_supplier->fetch(PDO::FETCH_ASSOC)) {
    $supplier_data[] = [
        'supplier' => $row['supplier_name'],
        'products' => $row['product_count'] ?? 0
    ];
}

// Dynamic content
$page_title = "Dashboard";

$content = '
<div class="container mx-auto px-4 py-8 bg-gray-50 min-h-screen">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
        <div class="text-sm text-gray-600">
            <span class="font-medium">Welcome, ' . htmlspecialchars($user_name) . '</span>
            <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full">' . $user_group . '</span>
        </div>
    </div>

    <!-- Overview Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">';

// Admin sees all cards
if ($user_level == 1) {
    $content .= '
        <!-- Users Card -->
        <div class="bg-white shadow-md rounded-xl p-6 text-center transform transition hover:scale-105 hover:shadow-xl">
            <div class="bg-cyan-100 text-cyan-800 rounded-full p-3 inline-block mb-4">
                <i class="fa-solid fa-users text-2xl"></i>
            </div>
            <h3 class="text-sm text-gray-500 uppercase tracking-wider mb-2">Total Users</h3>
            <p class="text-3xl font-bold text-cyan-700">' . $user_count . '</p>
        </div>';
}

// Admin and Manager see these cards
if ($user_level <= 2) {
    $content .= '
        <!-- Products Card -->
        <div class="bg-white shadow-md rounded-xl p-6 text-center transform transition hover:scale-105 hover:shadow-xl">
            <div class="bg-orange-100 text-orange-800 rounded-full p-3 inline-block mb-4">
                <i class="fa-solid fa-cogs text-2xl"></i>
            </div>
            <h3 class="text-sm text-gray-500 uppercase tracking-wider mb-2">Total Products</h3>
            <p class="text-3xl font-bold text-orange-700">' . $product_count . '</p>
        </div>

        <!-- Categories Card -->
        <div class="bg-white shadow-md rounded-xl p-6 text-center transform transition hover:scale-105 hover:shadow-xl">
            <div class="bg-yellow-100 text-yellow-800 rounded-full p-3 inline-block mb-4">
                <i class="fa-solid fa-tag text-2xl"></i>
            </div>
            <h3 class="text-sm text-gray-500 uppercase tracking-wider mb-2">Total Categories</h3>
            <p class="text-3xl font-bold text-yellow-700">' . $category_count . '</p>
        </div>';
}

// All users see the Sales Card
$content .= '
        <!-- Sales Card -->
        <div class="bg-white shadow-md rounded-xl p-6 text-center transform transition hover:scale-105 hover:shadow-xl">
            <div class="bg-purple-100 text-purple-800 rounded-full p-3 inline-block mb-4">
                <i class="fa-solid fa-dollar-sign text-2xl"></i>
            </div>
            <h3 class="text-sm text-gray-500 uppercase tracking-wider mb-2">Total Sales</h3>
            <p class="text-3xl font-bold text-purple-700">$' . number_format($total_sales, 2) . '</p>
        </div>';

// Only Admin sees the Suppliers Card
if ($user_level == 1 | $user_level == 2) {
    $content .= '
        <!-- Suppliers Card -->
        <div class="bg-white shadow-md rounded-xl p-6 text-center transform transition hover:scale-105 hover:shadow-xl">
            <div class="bg-green-100 text-green-800 rounded-full p-3 inline-block mb-4">
                <i class="fa-solid fa-truck text-2xl"></i>
            </div>
            <h3 class="text-sm text-gray-500 uppercase tracking-wider mb-2">Total Suppliers</h3>
            <p class="text-3xl font-bold text-green-700">' . $supplier_count . '</p>
        </div>';
}

$content .= '
    </div>';

// Category Statistics Section (visible to Admin and Manager)
if ($user_level <= 2) {
    $content .= '
    <!-- Category Statistics Section -->
    <div class="bg-white shadow-lg rounded-xl p-8 mb-10">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Product Statistics by Category</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">';

    $category_colors = [
        'bg-teal-50 text-teal-800',
        'bg-indigo-50 text-indigo-800', 
        'bg-blue-50 text-blue-800', 
        'bg-yellow-50 text-yellow-800'
    ];

    foreach ($category_stats as $index => $category) {
        $color_class = $category_colors[$index % count($category_colors)];
        $content .= '
            <div class="' . $color_class . ' rounded-xl p-6 shadow-md transform transition hover:scale-105">
                <div class="flex justify-between items-center mb-4">
                    <i class="fa-solid fa-tag text-2xl"></i>
                    <span class="text-sm font-semibold uppercase tracking-wider">' . htmlspecialchars($category['category_name']) . '</span>
                </div>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-sm">Products</span>
                        <span class="font-bold">' . $category['product_count'] . '</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm">Total Sales</span>
                        <span class="font-bold">$' . number_format($category['total_sales'], 2) . '</span>
                    </div>
                </div>
            </div>';
    }

    $content .= '
        </div>
    </div>';
}

// Charts Section
$content .= '
    <!-- Charts Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white shadow-lg rounded-xl p-8">
        <div class="space-y-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Sales by Category</h3>
                <div class="bg-gray-100 rounded-xl p-4">
                    <canvas id="salesByCategoryChart" class="w-full h-64"></canvas>
                </div>
            </div>';

// Only Admin sees the Products by Supplier chart
if ($user_level == 1 | $user_level == 2) {
    $content .= '
            <div>
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Products by Supplier</h3>
                <div class="bg-gray-100 rounded-xl p-4">
                    <canvas id="productsBySupplierChart" class="w-full h-64"></canvas>
                </div>
            </div>';
}

$content .= '
        </div>
        <div>
            <h3 class="text-xl font-semibold text-gray-800 mb-4">Sales by Product</h3>
            <div class="bg-gray-100 rounded-xl p-4">
                <canvas id="salesByProductChart" class="w-full h-96"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Charts.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>';

// Include all chart scripts
$content .= '
// Sales by Category Chart
const salesByCategoryData = {
    labels: ' . json_encode(array_column($sales_data, 'category')) . ',
    datasets: [{
        label: "Total Sales ($)",
        data: ' . json_encode(array_column($sales_data, 'sales')) . ',
        backgroundColor: ["#4CAF50", "#FF9800", "#2196F3", "#FFC107", "#9C27B0"],
        borderColor: "#ffffff",
        borderWidth: 1
    }]
};

const salesByCategoryConfig = {
    type: "bar",
    data: salesByCategoryData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        }
    }
};

new Chart(document.getElementById("salesByCategoryChart"), salesByCategoryConfig);

// Sales by Product Chart
const salesByProductData = {
    labels: ' . json_encode(array_column($product_sales_data, 'product')) . ',
    datasets: [{
        label: "Sales by Product ($)",
        data: ' . json_encode(array_column($product_sales_data, 'sales')) . ',
        backgroundColor: [
            "rgba(255, 99, 132, 0.6)",
            "rgba(54, 162, 235, 0.6)",
            "rgba(255, 206, 86, 0.6)",
            "rgba(75, 192, 192, 0.6)",
            "rgba(153, 102, 255, 0.6)"
        ],
        borderColor: [
            "rgba(255, 99, 132, 1)",
            "rgba(54, 162, 235, 1)",
            "rgba(255, 206, 86, 1)",
            "rgba(75, 192, 192, 1)",
            "rgba(153, 102, 255, 1)"
        ],
        borderWidth: 1
    }]
};

const salesByProductConfig = {
    type: "line",
    data: salesByProductData,
    options: {
        responsive: true,
        scales: {
            x: {
                display: false // Hide x-axis labels
            },
            y: {
                title: {
                    display: true,
                    text: "Sales ($)"
                },
                beginAtZero: true
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
};

new Chart(document.getElementById("salesByProductChart"), salesByProductConfig);';

// Only include Products by Supplier chart for Admin
if ($user_level == 1) {
    $content .= '
// Products by Supplier Chart
const productsBySupplierData = {
    labels: ' . json_encode(array_column($supplier_data, 'supplier')) . ',
    datasets: [{
        label: "Number of Products",
        data: ' . json_encode(array_column($supplier_data, 'products')) . ',
        backgroundColor: ["#E91E63", "#00BCD4", "#FF5722", "#8BC34A", "#3F51B5"],
        borderColor: "#ffffff",
        borderWidth: 1
    }]
};

const productsBySupplierConfig = {
    type: "bar",
    data: productsBySupplierData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: "top"
            }
        }
    }
};

new Chart(document.getElementById("productsBySupplierChart"), productsBySupplierConfig);';
}

$content .= '
</script>
';

include '../layouts/layout.php';
?>