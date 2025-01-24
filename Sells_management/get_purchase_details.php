<?php
// get_purchase_details.php

require_once '../includes/config.php';

// Check if 'id' parameter is set
if (isset($_GET['id'])) {
    $purchase_id = $_GET['id'];

    // Query to get purchase details along with product and supplier info
    $query = "
        SELECT p.id, pr.name AS product_name, s.name AS supplier_name, p.qty, p.date, pr.buy_price AS price
        FROM purchases p
        JOIN products pr ON p.product_id = pr.id
        JOIN suppliers s ON pr.supplier_id = s.id
        WHERE p.id = ?
    ";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$purchase_id]);
    $purchase = $stmt->fetch();

    // Return the data as JSON
    echo json_encode($purchase);
} else {
    echo json_encode(['error' => 'Purchase ID not provided.']);
}
