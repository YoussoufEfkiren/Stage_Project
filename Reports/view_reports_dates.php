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
    <form method="POST" class="mb-6 flex flex-col items-center">
        <div class="mb-4">
            <label for="start_date" class="block text-lg font-medium mb-2">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="border p-2 rounded" required>
        </div>

        <div class="mb-4">
            <label for="end_date" class="block text-lg font-medium mb-2">End Date</label>
            <input type="date" name="end_date" id="end_date" class="border p-2 rounded" required>
        </div>

        <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded hover:bg-blue-600 transition">Generate Report</button>
    </form>

    <!-- Display sales data if available -->
    <?php if (!empty($sales)): ?>
        <div id="report" class="overflow-x-auto mt-6">
            <h2 class="text-2xl font-semibold text-center mb-4">Sales Report from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?></h2>
            <table class="min-w-full bg-white shadow-md rounded-lg">
                <thead class="bg-blue-800 text-white">
                    <tr>
                        <th class="py-3 px-6 text-left">Sale ID</th>
                        <th class="py-3 px-6 text-left">Product Name</th>
                        <th class="py-3 px-6 text-left">Quantity Sold</th>
                        <th class="py-3 px-6 text-left">Sale Price</th>
                        <th class="py-3 px-6 text-left">Product Buy Price</th>
                        <th class="py-3 px-6 text-left">Stock Left</th>
                        <th class="py-3 px-6 text-left">Sale Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $sale): ?>
                        <tr class="border-t">
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['qty']); ?></td>
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['sale_price']); ?> $</td>
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['buy_price']); ?> $</td>
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['product_quantity'] - $sale['qty']); ?></td>
                            <td class="py-2 px-6"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="flex justify-center mt-4">
                <button onclick="window.print()" class="bg-green-500 text-white py-2 px-6 rounded hover:bg-green-600 transition">Print Report</button>
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

        #report {
            width: 100%;
            margin: 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #4CAF50;
            color: white;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        button {
            display: none;
        }
    }
</style>
<?php
$content = ob_get_clean();
$page_title = 'Sales Report by Date';

// Include layout
require_once '../layouts/layout.php';
?>
