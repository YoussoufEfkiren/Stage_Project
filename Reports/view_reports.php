<?php
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

// Fetch all sales with product details
try {
    // Modified query to join sales with products to get all required information
    $stmt = $pdo->query("
        SELECT sales.id AS sale_id, sales.qty, sales.price AS sale_price, sales.date AS sale_date, 
               products.name AS product_name, products.buy_price, products.quantity AS product_quantity 
        FROM sales
        INNER JOIN products ON sales.product_id = products.id
    ");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching sales data: " . $e->getMessage());
}

// Set content to include in the dashboard layout
ob_start();
?>
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Sales Information</h1>
    <table class="min-w-full bg-white shadow-md rounded">
        <thead class="bg-blue-800 text-white">
            <tr>
                <th class="py-2 px-4 text-left">Sale ID</th>
                <th class="py-2 px-4 text-left">Product Name</th>
                <th class="py-2 px-4 text-left">Quantity Sold</th>
                <th class="py-2 px-4 text-left">Sale Price</th>
                <th class="py-2 px-4 text-left">Product Buy Price</th>
                <th class="py-2 px-4 text-left">Stock Left</th>
                <th class="py-2 px-4 text-left">Sale Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($sales) > 0): ?>
                <?php foreach ($sales as $sale): ?>
                    <tr class="border-t">
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['qty']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['sale_price']); ?> $</td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['buy_price']); ?> $</td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['product_quantity'] - $sale['qty']); ?></td>
                        <td class="py-2 px-4"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-gray-500">No sales found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php
$content = ob_get_clean();
$page_title = 'Sales Information';

// Include the layout
require_once '../layouts/layout.php';
