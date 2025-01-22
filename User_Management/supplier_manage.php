<?php
session_start();

// Check user session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Page title
$page_title = 'Manage Suppliers';

// Include database connection
require_once '../includes/config.php';

// Handle Add Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_supplier'])) {
    handleAddSupplier($pdo);
}

// Handle Update Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_supplier'])) {
    handleUpdateSupplier($pdo);
}

// Handle Delete Supplier
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_supplier'])) {
    handleDeleteSupplier($pdo);
}

// Fetch suppliers with associated products
try {
    $suppliers = fetchSuppliers($pdo);
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error fetching suppliers: ' . $e->getMessage();
    $suppliers = [];
}

// Start building the page content
$content = '<div class="container mx-auto p-8">';

// Flash Messages
if (isset($_SESSION['message'])) {
    $messageClass = (strpos(strtolower($_SESSION['message']), 'error') !== false) 
        ? 'bg-red-100 border-red-300 text-red-700' 
        : 'bg-green-100 border-green-300 text-green-700';
    
    $content .= '<div class="' . $messageClass . ' p-4 rounded mb-4 shadow">
        ' . $_SESSION['message'] . '
    </div>';
    unset($_SESSION['message']);
}

// Rest of your HTML remains the same until the functions...

// Database functions
function fetchSuppliers($pdo) {
    try {
        $sql = "
            SELECT 
                s.id, 
                s.name AS supplier_name, 
                s.email, 
                s.contact as phone, 
                s.address, 
                GROUP_CONCAT(DISTINCT p.name SEPARATOR ', ') AS products 
            FROM suppliers s
            LEFT JOIN products p ON s.id = p.supplier_id
            GROUP BY s.id, s.name, s.email, s.contact, s.address
            ORDER BY s.id DESC";
            
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error fetching suppliers: " . $e->getMessage());
        throw $e;
    }
}

function handleAddSupplier($pdo) {
    try {
        $pdo->beginTransaction();
        
        $sql = "INSERT INTO suppliers (name, email, contact, address) 
                VALUES (:name, :email, :phone, :address)";
        
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':name' => $_POST['name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':address' => $_POST['address'],
        ]);

        if ($result) {
            $pdo->commit();
            $_SESSION['message'] = 'Supplier added successfully!';
        } else {
            throw new PDOException("Failed to insert supplier");
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error adding supplier: " . $e->getMessage());
        $_SESSION['message'] = 'Error adding supplier: ' . $e->getMessage();
    }
    
    header('Location: supplier_manage.php');
    exit;
}

function handleUpdateSupplier($pdo) {
    try {
        $pdo->beginTransaction();
        
        $sql = "UPDATE suppliers 
                SET name = :name, 
                    email = :email, 
                    contact = :phone, 
                    address = :address 
                WHERE id = :id";
                
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            ':name' => $_POST['name'],
            ':email' => $_POST['email'],
            ':phone' => $_POST['phone'],
            ':address' => $_POST['address'],
            ':id' => $_POST['id'],
        ]);

        if ($result) {
            $pdo->commit();
            $_SESSION['message'] = 'Supplier updated successfully!';
        } else {
            throw new PDOException("Failed to update supplier");
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error updating supplier: " . $e->getMessage());
        $_SESSION['message'] = 'Error updating supplier: ' . $e->getMessage();
    }
    
    header('Location: supplier_manage.php');
    exit;
}

function handleDeleteSupplier($pdo) {
    try {
        $pdo->beginTransaction();
        
        // First delete related products
        $sql = "DELETE FROM products WHERE supplier_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':id' => $_POST['id']]);
        
        // Then delete the supplier
        $sql = "DELETE FROM suppliers WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([':id' => $_POST['id']]);

        if ($result) {
            $pdo->commit();
            $_SESSION['message'] = 'Supplier deleted successfully!';
        } else {
            throw new PDOException("Failed to delete supplier");
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting supplier: " . $e->getMessage());
        $_SESSION['message'] = 'Error deleting supplier: ' . $e->getMessage();
    }
    
    header('Location: supplier_manage.php');
    exit;
}

// The rest of your code (HTML/JS) remains the same...

// Start building the page content
$content = '<div class="container mx-auto p-8">';

// Flash Messages
if (isset($_SESSION['message'])) {
    $content .= '<div class="bg-green-100 border border-green-300 text-green-700 p-4 rounded mb-4 shadow">
        ' . $_SESSION['message'] . '
    </div>';
    unset($_SESSION['message']);
}

$content .= '
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Supplier Management</h1>
        <button class="bg-blue-500 text-white px-4 py-2 rounded shadow hover:bg-blue-600" 
            onclick="ModalManager.showModal(\'addModal\')">
            Add Supplier
        </button>
    </div>

    <div class="overflow-x-auto rounded-lg shadow-lg">
        <table class="min-w-full bg-white border-collapse border border-gray-200">
            <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                <tr>
                    <th class="py-3 px-4 text-left border-b border-gray-300">ID</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Name</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Email</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Phone</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Address</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Products</th>
                    <th class="py-3 px-4 text-left border-b border-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 text-sm">';

if (count($suppliers) > 0) {
    foreach ($suppliers as $supplier) {
        $content .= '
            <tr class="hover:bg-gray-50">
                <td class="py-3 px-4 border-b border-gray-300">' . $supplier['id'] . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($supplier['supplier_name']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($supplier['email']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($supplier['phone']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($supplier['address']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($supplier['products']) . '</td>
                <td class="py-3 px-4 border-b border-gray-300 text-center">
                    <button onclick="ModalManager.setEditModalData(' . 
                        $supplier['id'] . ', \'' . 
                        addslashes(htmlspecialchars($supplier['supplier_name'])) . '\', \'' . 
                        addslashes(htmlspecialchars($supplier['email'])) . '\', \'' . 
                        addslashes(htmlspecialchars($supplier['phone'])) . '\', \'' . 
                        addslashes(htmlspecialchars($supplier['address'])) . '\')" 
                        class="text-blue-500 hover:underline">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button onclick="ModalManager.setDeleteModalData(' . $supplier['id'] . ')" 
                        class="text-red-500 hover:underline ml-4">
                        <i class="fas fa-trash-alt"></i> Delete
                    </button>
                </td>
            </tr>';
    }
} else {
    $content .= '
            <tr>
                <td colspan="6" class="py-6 px-4 text-center text-gray-500">No suppliers found</td>
            </tr>';
}

$content .= '
            </tbody>
        </table>
    </div>
</div>';

// Add Modal
$content .= '
<div id="addModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded shadow-lg w-1/3">
        <h2 class="text-xl font-bold mb-4">Add Supplier</h2>
        <form method="POST">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" 
                    onclick="ModalManager.closeModal(\'addModal\')">Cancel</button>
                <button type="submit" name="add_supplier" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Add
                </button>
            </div>
        </form>
    </div>
</div>';

// Edit Modal
$content .= '
<div id="editModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded shadow-lg w-1/3">
        <h2 class="text-xl font-bold mb-4">Edit Supplier</h2>
        <form method="POST">
            <input type="hidden" name="id" id="editSupplierId">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="editName" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="editEmail" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" id="editPhone" class="w-full p-2 border rounded" required>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" id="editAddress" class="w-full p-2 border rounded" required></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" 
                    onclick="ModalManager.closeModal(\'editModal\')">Cancel</button>
                <button type="submit" name="update_supplier" class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>';

// Delete Modal
$content .= '
<div id="deleteModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white p-6 rounded shadow-lg w-1/3">
        <h2 class="text-xl font-bold mb-4 text-red-600">Delete Supplier</h2>
        <p class="mb-4 text-gray-700">Are you sure you want to delete this supplier?</p>
        <form method="POST">
            <input type="hidden" name="id" id="deleteSupplierId">
            <div class="flex justify-end space-x-2">
                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400" 
                    onclick="ModalManager.closeModal(\'deleteModal\')">Cancel</button>
                <button type="submit" name="delete_supplier" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                    Delete
                </button>
            </div>
        </form>
    </div>
</div>';

// JavaScript for Modal Management
$content .= '
<script>
const ModalManager = {
    showModal(modalId) {
        document.getElementById(modalId).classList.remove("hidden");
    },
    closeModal(modalId) {
        document.getElementById(modalId).classList.add("hidden");
    },
    setEditModalData(id, name, email, phone, address) {
        document.getElementById("editSupplierId").value = id;
        document.getElementById("editName").value = name;
        document.getElementById("editEmail").value = email;
        document.getElementById("editPhone").value = phone;
        document.getElementById("editAddress").value = address;
        this.showModal("editModal");
    },
    setDeleteModalData(id) {
        document.getElementById("deleteSupplierId").value = id;
        this.showModal("deleteModal");
    }
};
</script>';



include '../layouts/layout.php';