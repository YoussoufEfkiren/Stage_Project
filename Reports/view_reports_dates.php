<?php
require_once '../includes/config.php';
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Enhanced security check
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Location: ../Authentification_management/login.php');
    exit;
}

// Anti CSRF protection
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid request");
    }
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$sales = [];
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = filter_input(INPUT_POST, 'start_date', FILTER_SANITIZE_STRING);
    $end_date = filter_input(INPUT_POST, 'end_date', FILTER_SANITIZE_STRING);

    if (!$start_date || !$end_date) {
        $error_message = "Both start and end dates must be provided.";
    } else {
        // Validate date format and range
        $start_datetime = DateTime::createFromFormat('Y-m-d', $start_date);
        $end_datetime = DateTime::createFromFormat('Y-m-d', $end_date);
        
        if (!$start_datetime || !$end_datetime) {
            $error_message = "Invalid date format. Please use YYYY-MM-DD.";
        } elseif ($start_datetime > $end_datetime) {
            $error_message = "Start date must be before end date.";
        } else {
            try {
                $stmt = $pdo->prepare("
                    SELECT 
                        s.id AS sale_id,
                        s.qty,
                        s.price AS sale_price,
                        s.date AS sale_date,
                        p.name AS product_name,
                        p.buy_price,
                        p.quantity AS product_quantity,
                        (p.buy_price * s.qty) AS total_spent
                    FROM purchases s
                    INNER JOIN products p ON s.product_id = p.id
                    WHERE s.date BETWEEN :start_date AND :end_date
                    ORDER BY s.date DESC
                ");
                
                $stmt->execute([
                    ':start_date' => $start_date,
                    ':end_date' => $end_date
                ]);
                
                $sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (empty($sales)) {
                    $success_message = "No sales found for the selected date range.";
                }
            } catch (PDOException $e) {
                error_log("Error fetching sales data: " . $e->getMessage());
                $error_message = "An error occurred while fetching sales data. Please try again later.";
            }
        }
    }
}

ob_start();
?>

<div class="p-6">
    <h1 class="text-3xl font-semibold mb-6 text-center">View Purchases Report by Date Range</h1>
    
    <?php if ($error_message): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="mb-6 flex flex-wrap justify-center items-end gap-4" id="reportForm">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
        <div>
            <label for="start_date" class="block text-lg font-medium mb-2">Start Date</label>
            <input type="date" 
                   name="start_date" 
                   id="start_date" 
                   class="border border-gray-300 p-2 rounded w-64" 
                   value="<?php echo isset($start_date) ? htmlspecialchars($start_date) : ''; ?>"
                   required>
        </div>
        <div>
            <label for="end_date" class="block text-lg font-medium mb-2">End Date</label>
            <input type="date" 
                   name="end_date" 
                   id="end_date" 
                   class="border border-gray-300 p-2 rounded w-64" 
                   value="<?php echo isset($end_date) ? htmlspecialchars($end_date) : ''; ?>"
                   required>
        </div>
        <button type="submit" class="bg-blue-500 text-white py-3 px-4 border-b border-gray-300 rounded hover:bg-blue-600 transition">
            Generate Report
        </button>
    </form>

    <?php if (!empty($sales)): ?>
        <div id="report" class="overflow-x-auto mt-6">
            <h2 class="text-2xl font-semibold text-center mb-4">
                Purchases Report from <?php echo htmlspecialchars($start_date); ?> to <?php echo htmlspecialchars($end_date); ?>
            </h2>
            
            <!-- Total Spent Card -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php
                $totalSpent = array_sum(array_column($sales, 'total_spent'));
                $totalQuantitySold = array_sum(array_column($sales, 'qty'));
                $totalQuantityLeft = array_sum(array_column($sales, 'product_quantity'));
                ?>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Total Spent</h3>
                    <p class="text-2xl font-bold"><?php echo number_format($totalSpent, 2); ?> $</p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Items Purchased</h3>
                    <p class="text-2xl font-bold"><?php echo number_format($totalQuantitySold); ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Stock Remaining</h3>
                    <p class="text-2xl font-bold"><?php echo number_format($totalQuantityLeft); ?></p>
                </div>
                <div class="bg-white p-4 rounded-lg shadow">
                    <h3 class="text-gray-500 text-sm">Avg. Unit Cost</h3>
                    <p class="text-2xl font-bold"><?php echo number_format($totalSpent / $totalQuantitySold, 2); ?> $</p>
                </div>
            </div>

            <table class="min-w-full bg-white border-collapse border border-gray-200 mb-6" id="salesTable">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Sale ID</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Product Name</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Quantity</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Unit Cost</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Total Spent</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Stock Left</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Sale Date</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">
                    <?php foreach ($sales as $sale): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_id']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_name']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['qty']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo number_format($sale['buy_price'], 2); ?> $</td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo number_format($sale['total_spent'], 2); ?> $</td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['product_quantity']); ?></td>
                            <td class="py-3 px-4 border-b border-gray-300"><?php echo htmlspecialchars($sale['sale_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="flex justify-center mt-4 gap-4">
                <button onclick="printReport()" class="bg-green-500 text-white py-3 px-4 rounded hover:bg-green-600 transition">
                    Print Report
                </button>
                <button onclick="exportToExcel()" class="bg-blue-500 text-white py-3 px-4 rounded hover:bg-blue-600 transition">
                    Export to Excel
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation remains the same
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);

            if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
                e.preventDefault();
                alert("Please select valid dates.");
                return;
            }

            if (startDate > endDate) {
                e.preventDefault();
                alert("Start date must be before end date.");
                return;
            }
        });
    }

    // Set max date to today for date inputs
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('start_date').max = today;
    document.getElementById('end_date').max = today;
});

function printReport() {
    const printWindow = window.open('', '', 'width=1000,height=800');
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    if (!startDate || !endDate) {
        alert("Please select both start and end dates.");
        return;
    }

    const formattedStartDate = new Date(startDate).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
    const formattedEndDate = new Date(endDate).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

    // Get sales data and totals
    const salesData = <?php echo json_encode($sales); ?>;
    const totalSpent = <?php echo json_encode($totalSpent); ?>;
    const totalQuantitySold = <?php echo json_encode($totalQuantitySold); ?>;
    const totalQuantityLeft = <?php echo json_encode($totalQuantityLeft); ?>;
    const avgUnitCost = <?php echo json_encode($totalSpent / $totalQuantitySold); ?>;

    printWindow.document.write(`
        <html>
            <head>
                <title>Purchases Report</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    table, th, td { border: 1px solid #ddd; }
                    th, td { padding: 8px; text-align: left; }
                    th { background-color: #f4f4f4; }
                    .header { text-align: center; font-size: 1.5em; font-weight: bold; }
                    .totals-table { margin-top: 20px; }
                </style>
            </head>
            <body>
                <h1 class="header">Purchases Report</h1>
                <p class="header">From: ${formattedStartDate} to ${formattedEndDate}</p>
                <table>
                    <thead>
                        <tr>
                            <th>Sale ID</th>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Spent</th>
                            <th>Stock Left</th>
                            <th>Sale Date</th>
                        </tr>
                    </thead>
                    <tbody>
    `);

    // Append sale data to table
    salesData.forEach(function (sale) {
        printWindow.document.write(`
            <tr>
                <td>${sale.sale_id}</td>
                <td>${sale.product_name}</td>
                <td>${sale.qty}</td>
                <td>${sale.buy_price}</td>
                <td>${sale.total_spent}</td>
                <td>${sale.product_quantity}</td>
                <td>${sale.sale_date}</td>
            </tr>
        `);
    });

    printWindow.document.write(`
                    </tbody>
                </table>
                <table class="totals-table">
                    <thead>
                        <tr>
                            <th>Total Spent</th>
                            <th>Items Purchased</th>
                            <th>Stock Remaining</th>
                            <th>Avg. Unit Cost</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>${totalSpent.toFixed(2)} $</td>
                            <td>${totalQuantitySold}</td>
                            <td>${totalQuantityLeft}</td>
                            <td>${avgUnitCost.toFixed(2)} $</td>
                        </tr>
                    </tbody>
                </table>
            </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}

function exportToExcel() {
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;

    if (!startDate || !endDate) {
        alert("Please select both start and end dates.");
        return;
    }

    const formattedStartDate = new Date(startDate).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
    const formattedEndDate = new Date(endDate).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });

    // Get sales data and totals
    const salesData = <?php echo json_encode($sales); ?>;
    const totalSpent = <?php echo json_encode($totalSpent); ?>;
    const totalQuantitySold = <?php echo json_encode($totalQuantitySold); ?>;
    const totalQuantityLeft = <?php echo json_encode($totalQuantityLeft); ?>;
    const avgUnitCost = <?php echo json_encode($totalSpent / $totalQuantitySold); ?>;

    // Prepare Excel content
    let excelContent = `
        <table border="1" style="border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 14px;">
            <thead style="background-color: #f4f4f4; font-weight: bold; text-align: center;">
                <tr>
                    <th colspan="7" style="font-size: 18px; padding: 10px;">Purchases Report</th>
                </tr>
                <tr>
                    <th colspan="7" style="font-size: 14px; padding: 8px;">From: ${formattedStartDate} To: ${formattedEndDate}</th>
                </tr>
                <tr style="background-color: #d9d9d9;">
                    <th style="padding: 8px;">Sale ID</th>
                    <th style="padding: 8px;">Product Name</th>
                    <th style="padding: 8px;">Quantity</th>
                    <th style="padding: 8px;">Unit Cost</th>
                    <th style="padding: 8px;">Total Spent</th>
                    <th style="padding: 8px;">Stock Left</th>
                    <th style="padding: 8px;">Sale Date</th>
                </tr>
            </thead>
            <tbody>
    `;

    salesData.forEach(function (sale, index) {
        const rowColor = index % 2 === 0 ? '#f9f9f9' : '#ffffff';
        excelContent += `
            <tr style="background-color: ${rowColor}; text-align: center;">
                <td style="padding: 8px;">${sale.sale_id}</td>
                <td style="padding: 8px;">${sale.product_name}</td>
                <td style="padding: 8px;">${sale.qty}</td>
                <td style="padding: 8px;">${sale.buy_price}</td>
                <td style="padding: 8px;">${sale.total_spent}</td>
                <td style="padding: 8px;">${sale.product_quantity}</td>
                <td style="padding: 8px;">${sale.sale_date}</td>
            </tr>
        `;
    });

    // Append totals
    excelContent += `
            </tbody>
            <tfoot>
                <tr style="background-color: #f4f4f4; font-weight: bold; text-align: center;">
                    <td colspan="7" style="padding: 10px; font-size: 16px;">Totals</td>
                </tr>
                <tr style="background-color: #e0e0e0; text-align: center;">
                    <td colspan="4" style="padding: 8px; text-align: right;"><strong>Total Spent:</strong></td>
                    <td style="padding: 8px;">${totalSpent.toFixed(2)} $</td>
                    <td colspan="2" style="padding: 8px;"><strong>Avg. Unit Cost:</strong> ${avgUnitCost.toFixed(2)} $</td>
                </tr>
                <tr style="background-color: #e0e0e0; text-align: center;">
                    <td colspan="4" style="padding: 8px; text-align: right;"><strong>Items Purchased:</strong></td>
                    <td style="padding: 8px;">${totalQuantitySold}</td>
                    <td colspan="2" style="padding: 8px;"><strong>Stock Remaining:</strong> ${totalQuantityLeft}</td>
                </tr>
            </tfoot>
        </table>
    `;

    // Convert content to Excel and download
    const excelFile = new Blob([excelContent], { type: 'application/vnd.ms-excel' });
    const downloadLink = document.createElement('a');
    downloadLink.href = URL.createObjectURL(excelFile);
    downloadLink.download = 'Purchases_Report.xls';
    downloadLink.click();
}
</script>

<?php
$content = ob_get_clean();
$page_title = 'Purchases Report by Date';
require_once '../layouts/layout.php';
?>