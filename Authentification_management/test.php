<?php
require '../includes/config.php'; // Include your config file

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Tables in the database:";
    foreach ($tables as $table) {
        echo "<br>" . $table['Tables_in_inventory_system'];
    }
    echo "Columns in the users table:";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "<br>" . $column['Field'];
    }
    $storedPassword = '$2y$10$ructAx2sd5ZABnSId3MO4e6e55n7EopRVAW7grTK09QRRkkr.Uw4C'; // Replace with the hashed password from the DB
    $inputPassword = 'special'; // Replace with the password you're testing

    if (password_verify($inputPassword, $storedPassword)) {
        echo "Password matches!";
    } else {
        echo "Password does not match=>";
    }
    $newPassword = 'special'; // Replace with the new plain text password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    echo $hashedPassword; // Copy and update this hash in your database


} catch (PDOException $e) {
    die("Error querying database: " . $e->getMessage());
}
?>
