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
    $stmt = $pdo->query("
        SELECT sales.id AS sale_id, sales.qty, sales.price AS sale_price, sales.date AS sale_date, 
               products.name AS product_name, products.buy_price, products.quantity AS product_quantity 
        FROM sales
        INNER JOIN products ON sales.product_id = products.id
    ");
    $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch total quantity left in stock
    $stockStmt = $pdo->query("SELECT SUM(quantity) AS total_stock_left FROM products");
    $totalStockLeft = $stockStmt->fetch(PDO::FETCH_ASSOC)['total_stock_left'];
} catch (PDOException $e) {
    die("Error fetching data: " . $e->getMessage());
}

// Initialize totals
$totalSales = 0;
$totalProfit = 0;
$totalQuantitySold = 0;

foreach ($sales as $sale) {
    $totalSales += $sale['sale_price'] * $sale['qty'];
    $totalProfit += ($sale['sale_price'] - $sale['buy_price']) * $sale['qty'];
    $totalQuantitySold += $sale['qty'];
}

// Set content to include in the dashboard layout
ob_start();
?>
<div class="p-6">
    <h1 class="text-3xl font-bold mb-6 text-gray-800">Sales Reports</h1>

    <!-- Print Button -->
    <div class="mb-4">
        <button 
            onclick="printTable()" 
            class="bg-green-500 text-white px-6 py-2 rounded shadow hover:bg-green-600 transition">
            Print Table
        </button>
    </div>

    <!-- Styled Sales Table -->
    <div class="overflow-x-auto mb-6">
        <table id="salesTable" class="min-w-full bg-white border-collapse border border-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Sale ID</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Product Name</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Quantity Sold</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Sale Price</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Product Buy Price</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Stock Left (Per Product)</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Sale Date</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">
                <?php if (count($sales) > 0): ?>
                    <?php foreach ($sales as $sale): ?>
                        <tr class="hover:bg-gray-100 transition">
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['qty']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_price']); ?> $</td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['buy_price']); ?> $</td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_quantity']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-6 text-gray-500">No sales found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Totals Table -->
    <div class="overflow-x-auto">
    <table id="totalsTable" class="min-w-full bg-white border-collapse border border-gray-200">
        <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
            <tr>
                <th class="py-3 px-4 text-left border-b border-gray-300">Metric</th>
                <th class="py-3 px-4 text-left border-b border-gray-300">Value</th>
            </tr>
        </thead>
        <tbody class="text-gray-700 text-sm">
            <tr class="hover:bg-gray-100 transition">
                <td class="py-3 px-4 border-b border-gray-300">Total Sales</td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo number_format($totalSales, 2); ?> $</td>
            </tr>
            <tr class="hover:bg-gray-100 transition">
                <td class="py-3 px-4 border-b border-gray-300">Total Profit</td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo number_format($totalProfit, 2); ?> $</td>
            </tr>
            <tr class="hover:bg-gray-100 transition">
                <td class="py-3 px-4 border-b border-gray-300">Total Quantity Sold</td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo $totalQuantitySold; ?> units</td>
            </tr>
            <tr class="hover:bg-gray-100 transition">
                <td class="py-3 px-4 border-b border-gray-300">Total Stock Left</td>
                <td class="py-3 px-4 border-b border-gray-300"><?php echo $totalStockLeft; ?> units</td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    function printTable() {
        // Get content of both tables
        const salesTableContent = document.getElementById('salesTable').outerHTML;
        const totalsTableContent = document.getElementById('totalsTable').outerHTML;

        // Open a new window for printing
        const printWindow = window.open('', '', 'width=800,height=600');
        printWindow.document.write(`
            <html>
                <head>
                    <title>Print Sales Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
                        th { background-color: #1e3a8a; color: white; }
                        tr:nth-child(even) { background-color: #f9f9f9; }
                        tr:hover { background-color: #e2e8f0; }
                        .totals { font-weight: bold; color: #333; margin-top: 20px; }
                        h1 { text-align: center; margin-bottom: 20px; }
                    </style>
                </head>
                <body>
                    <h1>Sales Reports</h1>
                    <h2>Sales Table</h2>
                    ${salesTableContent}
                    <h2>Totals Table</h2>
                    ${totalsTableContent}
                </body>
            </html>
        `);

        // Close document and initiate print
        printWindow.document.close();
        printWindow.print();
    }
</script>


<?php
$content = ob_get_clean();
$page_title = 'Sales Information';

// Include the layout
require_once '../layouts/layout.php';
