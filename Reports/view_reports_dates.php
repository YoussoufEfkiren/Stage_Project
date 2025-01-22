<?php
require_once '../includes/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentification_management/login.php');
    exit;
}

$sales = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve the start and end dates from the form
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Fetch the sales data between the two dates
    try {
        $stmt = $pdo->prepare("
            SELECT sales.id AS sale_id, sales.qty, sales.price AS sale_price, sales.date AS sale_date, 
                   products.name AS product_name, products.buy_price, products.quantity AS product_quantity
            FROM sales
            INNER JOIN products ON sales.product_id = products.id
            WHERE sales.date BETWEEN :start_date AND :end_date
        ");
        $stmt->bindParam(':start_date', $start_date);
        $stmt->bindParam(':end_date', $end_date);
        $stmt->execute();
        $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Error fetching sales data: " . $e->getMessage());
    }
}

ob_start();
?>
<!-- Form to select the date range -->
<div class="p-6">
    <h1 class="text-3xl font-semibold mb-6 text-center">View Sales Report by Date Range</h1>
    <form method="POST" class="mb-6 flex flex-wrap justify-center items-end gap-4">
        <div>
            <label for="start_date" class="block text-lg font-medium mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="border border-gray-300 p-2 rounded w-64" required>
        </div>
        <div>
            <label for="end_date" class="block text-lg font-medium mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" class="border border-gray-300 p-2 rounded w-64" required>
        </div>
        <button type="submit" class="bg-blue-500 text-white py-3 px-4 border-b border-gray-300 rounded hover:bg-blue-600 transition">Generate Report</button>
    </form>

    <!-- Display sales data if available -->
    <?php if (!empty($sales)): ?>
        <div id="report" class="overflow-x-auto mt-6">
            <h2 class="text-2xl font-semibold text-center mb-4">
                Sales Report from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
            </h2>
            <table class="min-w-full bg-white border-collapse border border-gray-200" id="salesTable">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Sale ID</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Product Name</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Quantity Sold</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Sale Price</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Product Buy Price</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Stock Left</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Sale Date</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    <?php 
                    $totalSales = 0; // Initialize total sales variable
                    foreach ($sales as $sale): 
                        $totalSales += $sale['sale_price'] * $sale['qty']; // Accumulate total sales
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['qty']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_price']); ?> $</td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['buy_price']); ?> $</td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_quantity'] - $sale['qty']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Total Sales -->
            <!-- Total Sales -->
            <div class="text-center mt-4">
                <h3 class="text-xl font-bold text-blue-800 total-sales">
                    Total Sales: <?php echo number_format($totalSales, 2); ?> $
                </h3>
            </div>


            <div class="flex justify-center mt-4">
                <button onclick="printTable()" class="bg-green-500 text-white py-3 px-4 border-b border-gray-300 rounded hover:bg-green-600 transition">
                    Print Report
                </button>
            </div>
        </div>
    <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        <p class="text-red-500 text-center">No sales found for the selected date range.</p>
    <?php endif; ?>
</div>

<!-- Styles for printing -->
<style>
    @media print {
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 20px;
    }

    #salesTable {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    #salesTable th, #salesTable td {
        border: 1px solid #000;
        padding: 8px;
        text-align: left;
    }

    #salesTable th {
        background-color: #1e3a8a;
        color: white;
    }

    #salesTable tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .total-sales {
        font-size: 20px;
        font-weight: bold;
        color: #1e3a8a;
        margin-top: 20px;
    }

    button {
        display: none;
    }

    h1, h2, .total-sales {
        display: block;
    }
}

</style>

<script>
    function printTable() {
    var printWindow = window.open('', '', 'width=800,height=600');
    var tableContent = document.getElementById('salesTable').outerHTML;
    var totalSales = document.querySelector('.total-sales').outerHTML;
    var dateRange = document.querySelector('h2').outerHTML;

    printWindow.document.write(`
        <html>
            <head>
                <title>Sales Report</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 20px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #1e3a8a;
                        color: white;
                    }
                    tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .total-sales {
                        font-size: 24px;
                        font-weight: bold;
                        color: #1e3a8a;
                        margin-top: 20px;
                    }
                </style>
            </head>
            <body>
                <h1>Sales Report</h1>
                ${dateRange}
                ${tableContent}
                ${totalSales}
            </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
    printWindow.close();
}

</script>



<?php
$content = ob_get_clean();
$page_title = 'Sales Report by Date';

// Include layout
require_once '../layouts/layout.php';
?>