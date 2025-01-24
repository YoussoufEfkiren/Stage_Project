<?php
// sells.php

// Include database connection
require_once '../includes/config.php';

$page_title = "Gestion des Achats";
$message = '';
$receipt = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Verify product stock
    $query = "SELECT p.id, p.name, p.buy_price AS price, p.quantity, p.supplier_id FROM products p WHERE p.id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    // Verify supplier info from supplier_id
    $supplierQuery = "SELECT name FROM suppliers WHERE id = ?";
    $supplierStmt = $pdo->prepare($supplierQuery);
    $supplierStmt->execute([$product['supplier_id']]);
    $supplier = $supplierStmt->fetch();

    if ($product) {
        if ($product['quantity'] >= $quantity) {
            // Update stock and log purchase
            $updateQuery = "UPDATE products SET quantity = quantity + ? WHERE id = ?";
            $insertQuery = "INSERT INTO purchases (product_id, qty, date) VALUES (?, ?, NOW())";

            // Update stock
            $stmt1 = $pdo->prepare($updateQuery);
            $stmt1->execute([$quantity, $product_id]);

            // Log the purchase
            $stmt2 = $pdo->prepare($insertQuery);
            $stmt2->execute([$product_id, $quantity]);

            // Generate receipt content
            $total_price = $product['price'] * $quantity;
            $receipt = "
                <div class='receipt'>
                    <h2>Receipt</h2>
                    <p><strong>Product:</strong> {$product['name']}</p>
                    <p><strong>Price:</strong> {$product['price']}</p>
                    <p><strong>Quantity:</strong> {$quantity}</p>
                    <p><strong>Total Price:</strong> $total_price</p>
                    <p><strong>Supplier:</strong> {$supplier['name']}</p>
                    <button onclick='window.print()' class='bg-blue-500 text-white p-2 rounded'>Print Receipt</button>
                    <button onclick='closeModal()' class='bg-gray-500 text-white p-2 rounded mt-2'>Done</button>
                </div>
            ";

            $message = "<div class='text-green-500'>Achat enregistré avec succès !</div>";
        } else {
            $message = "<div class='text-red-500'>Stock insuffisant !</div>";
        }
    } else {
        $message = "<div class='text-red-500'>Produit introuvable !</div>";
    }
}

// Fetch products
$productsQuery = "SELECT id, name, quantity FROM products";
$productsStmt = $pdo->query($productsQuery);

// Fetch purchases
$purchasesQuery = "
    SELECT p.id, p.product_id, pr.name AS product_name, p.qty, p.date
    FROM purchases p
    JOIN products pr ON p.product_id = pr.id
";
$purchasesStmt = $pdo->query($purchasesQuery);

// Start output buffering
ob_start();

?>
<h1 class="text-2xl font-bold mb-4">Gestion des Achats</h1>

<!-- Purchase Form -->
<form method="POST" class="mb-6">
    <label for="product_id" class="block text-gray-700">Sélectionner un produit :</label>
    <select id="product_id" name="product_id" class="w-full p-2 border rounded mb-4">
        <?php while ($row = $productsStmt->fetch()): ?>
            <option value="<?= $row['id'] ?>"><?= $row['name'] ?> (Stock: <?= $row['quantity'] ?>)</option>
        <?php endwhile; ?>
    </select>

    <label for="quantity" class="block text-gray-700">Quantité :</label>
    <input type="number" id="quantity" name="quantity" class="w-full p-2 border rounded mb-4" min="1" required>

    <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded">Enregistrer l'achat</button>
</form>

<!-- Display message -->
<?= $message ?>

<!-- Display receipt in a modal -->
<?php if ($receipt): ?>
    <div id="receiptModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-lg w-1/2">
            <?= $receipt ?>
        </div>
    </div>
<?php endif; ?>

<!-- Product List -->
<h2 class="text-xl font-bold mt-6 mb-4">Liste des Produits</h2>
<table class="w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border p-2">ID</th>
            <th class="border p-2">Nom</th>
            <th class="border p-2">Quantité</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $productsStmt = $pdo->query($productsQuery); // Re-fetch the products
        while ($row = $productsStmt->fetch()):
        ?>
            <tr>
                <td class="border p-2 text-center"><?= $row['id'] ?></td>
                <td class="border p-2"><?= $row['name'] ?></td>
                <td class="border p-2 text-center"><?= $row['quantity'] ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Purchase List -->
<h2 class="text-xl font-bold mt-6 mb-4">Liste des Achats</h2>
<table class="w-full border-collapse border border-gray-300">
    <thead>
        <tr class="bg-gray-200">
            <th class="border p-2">ID Achat</th>
            <th class="border p-2">Nom du Produit</th>
            <th class="border p-2">Quantité</th>
            <th class="border p-2">Date</th>
            <th class="border p-2">Actions</th> <!-- New column for the button -->
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $purchasesStmt->fetch()): ?>
            <tr>
                <td class="border p-2 text-center"><?= $row['id'] ?></td>
                <td class="border p-2"><?= $row['product_name'] ?></td>
                <td class="border p-2 text-center"><?= $row['qty'] ?></td>
                <td class="border p-2 text-center"><?= $row['date'] ?></td>
                <td class="border p-2 text-center">
                    <button onclick="showDetails(<?= $row['id'] ?>)" class="bg-blue-500 text-white p-2 rounded">Details</button>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<!-- Modal for showing purchase details -->
<div id="detailsModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center hidden">
    <div class="bg-white p-6 rounded shadow-lg w-1/2">
        <h3 class="text-xl font-bold mb-4">Purchase Details</h3>
        <p><strong>Product:</strong> <span id="product-name"></span></p>
        <p><strong>Supplier:</strong> <span id="supplier-name"></span></p>
        <p><strong>Quantity:</strong> <span id="purchase-quantity"></span></p>
        <p><strong>Date:</strong> <span id="purchase-date"></span></p>
        <p><strong>Price:</strong> $<span id="purchase-price"></span></p>
        <button onclick="closeDetailsModal()" class="bg-gray-500 text-white p-2 rounded mt-4">Close</button>
    </div>
</div>

<script>
    function showDetails(purchaseId) {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', `get_purchase_details.php?id=${purchaseId}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                const data = JSON.parse(xhr.responseText);
                document.getElementById('product-name').textContent = data.product_name;
                document.getElementById('supplier-name').textContent = data.supplier_name;
                document.getElementById('purchase-quantity').textContent = data.qty;
                document.getElementById('purchase-date').textContent = data.date;
                document.getElementById('purchase-price').textContent = data.price;

                // Show modal
                document.getElementById('detailsModal').classList.remove('hidden');
            }
        };
        xhr.send();
    }

    // Close details modal
    function closeDetailsModal() {
        document.getElementById('detailsModal').classList.add('hidden');
    }

    // Close the receipt modal
    function closeModal() {
        document.getElementById('receiptModal').style.display = 'none';
    }

    // Custom print function
    window.print = function() {
        var content = document.querySelector('#receiptModal .receipt').innerHTML;
        var printWindow = window.open('', '', 'height=400,width=600');
        printWindow.document.write('<html><head><title>Receipt</title></head><body>');
        printWindow.document.write(content);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.print();
    }
</script>

<?php
$content = ob_get_clean(); // Store the content in the variable $content
// Include the layout
include '../layouts/layout.php';
?>
