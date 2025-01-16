<?php
// manage_users.php

session_start();

// Check if the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch user details from the session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];  // Assuming username is stored in session
$user_level = $_SESSION['user_level'];  // Assuming user_level is stored in session


// Assume $user_level contains a numeric value from the session or database, e.g., 1, 2, or 3
switch ($user_level) {
    case 1:
        $user_group = 'Admin';  // Map 1 to Admin
        break;
    case 2:
        $user_group = 'Manager';  // Map 2 to Manager
        break;
    case 3:
        $user_group = 'User';  // Map 3 to User
        break;
    default:
        $$user_group = 'Guest';  // Default case if the user_level doesn't match
        break;
}

$page_title = 'Manage Users';

// Include the database connection
require_once '../includes/config.php';

// Fetch all users
$sql = "SELECT id, name, username, user_level , last_login FROM users";
$stmt = $pdo->query($sql);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deleting a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $user_id_to_delete = $_POST['delete_id'];

    // Validate the user ID
    if (is_numeric($user_id_to_delete)) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':id' => $user_id_to_delete])) {
            $_SESSION['message'] = 'User deleted successfully!';
            header('Location: manage_users.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error deleting user!';
        }
    }
}

// Handle adding a new user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    // Collect form data
    $name = $_POST['name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_level = $_POST['user_level'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Prepare SQL query to insert new user
    $sql = "INSERT INTO users (name, username, password, user_level) VALUES (:name, :username, :password, :user_level)";
    $stmt = $pdo->prepare($sql);

    // Execute the query with the form data
    if ($stmt->execute([':name' => $name, ':username' => $username, ':password' => $hashed_password, ':user_level' => $user_level])) {
        $_SESSION['message'] = 'User added successfully!';
        header('Location: manage_users.php');
        exit;
    } else {
        $_SESSION['message'] = 'Error adding user!';
    }
}

// Handle form submission to update user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $user_id = $_POST['id'];
    $name = $_POST['name'];
    $username = $_POST['username'];
    $user_level = $_POST['user_level'];

    // Validate the user ID
    if (!is_numeric($user_id)) {
        die('Invalid user ID!');
    }

    // Update the user details in the database
    $sql = "UPDATE users SET name = :name, username = :username, user_level = :user_level WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([':name' => $name, ':username' => $username, ':user_level' => $user_level, ':id' => $user_id])) {
        $_SESSION['message'] = 'User updated successfully!';
        header('Location: manage_users.php');
        exit;
    } else {
        $_SESSION['message'] = 'Error updating user!';
    }
}

// Handle deleting a user
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $user_id_to_delete = $_POST['delete_id'];

    // Validate the user ID
    if (is_numeric($user_id_to_delete)) {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':id' => $user_id_to_delete])) {
            $_SESSION['message'] = 'User deleted successfully!';
            header('Location: manage_users.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error deleting user!';
        }
    }
}


// Build content
$content = '
    <div class="p-4">
        <h1 class="text-3xl font-semibold mb-6">Manage Users</h1>
        
        <!-- Add New User Button -->
        <button onclick="openAddUserModal()" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">Add New User</button>';

if (isset($_SESSION['message'])) {
    $content .= '<div class="text-green-500 p-4">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

// Render users in a table
$content .= '
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr>
                    <th class="py-2 px-4 border border-gray-300">ID</th>
                    <th class="py-2 px-4 border border-gray-300">Name</th>
                    <th class="py-2 px-4 border border-gray-300">Username</th>
                    <th class="py-2 px-4 border border-gray-300">User Level</th>
                    <th class="py-2 px-4 border border-gray-300">Last Login</th>
                    <th class="py-2 px-4 border border-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody>';

if (count($users) > 0) {
    foreach ($users as $user) {
        $content .= '
            <tr>
                <td class="py-2 px-4 border border-gray-300">' . $user['id'] . '</td>
                <td class="py-2 px-4 border border-gray-300">' . htmlspecialchars($user['name']) . '</td>
                <td class="py-2 px-4 border border-gray-300">' . htmlspecialchars($user['username']) . '</td>
                <td class="py-2 px-4 border border-gray-300">' . htmlspecialchars($user['user_level']) . '</td>
                <td class="py-2 px-4 border border-gray-300">' . htmlspecialchars($user['last_login']) . '</td>
                <td class="py-2 px-4 border border-gray-300">
                    <button onclick="openEditModal(' . $user['id'] . ', \'' . addslashes(htmlspecialchars($user['name'])) . '\', \'' . addslashes(htmlspecialchars($user['username'])) . '\', ' . $user['user_level'] . ')" class="text-blue-500 hover:text-blue-700">Edit</button>
                    <button type="button" onclick="openDeleteModal(' . $user['id'] . ')" class="text-red-500 hover:text-red-700 ml-4">Delete</button>
                </td>
            </tr>';
    }
} else {
    $content .= '<tr><td colspan="3" class="text-red-500 text-center">No users found</td></tr>';
}

$content .= '
            </tbody>
        </table>
    </div>';  // End content div
?>

<!-- Add User Modal -->
<div id="addUserModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Add New User</h2>
        <form action="manage_users.php" method="POST">
            <div class="mb-4">
                <label for="name" class="block font-semibold">Name</label>
                <input type="text" name="name" id="name" class="w-full px-4 py-2 border border-gray-300 rounded mt-2" required>
            </div>
            <div class="mb-4">
                <label for="username" class="block font-semibold">Username</label>
                <input type="text" name="username" id="username" class="w-full px-4 py-2 border border-gray-300 rounded mt-2" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block font-semibold">Password</label>
                <input type="password" name="password" id="password" class="w-full px-4 py-2 border border-gray-300 rounded mt-2" required>
            </div>
            <div class="mb-4">
                <label for="user_level" class="block font-semibold">User Level</label>
                <select name="user_level" id="user_level" class="w-full px-4 py-2 border border-gray-300 rounded mt-2" required>
                    <option value="1">Admin</option>
                    <option value="2">Manager</option>
                    <option value="3">User</option>
                </select>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="add_user" class="bg-blue-500 text-white px-4 py-2 rounded">Add User</button>
                <button type="button" onclick="closeAddUserModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for editing user -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Edit User</h2>
        <form action="manage_users.php" method="POST">
            <input type="hidden" name="id" id="editUserId">
            <div class="mb-4">
                <label for="editName" class="block text-sm font-medium">Name</label>
                <input type="text" name="name" id="editName" class="border border-gray-300 p-2 w-full">
            </div>
            <div class="mb-4">
                <label for="editUsername" class="block text-sm font-medium">Username</label>
                <input type="text" name="username" id="editUsername" class="border border-gray-300 p-2 w-full">
            </div>
            <div class="mb-4">
                <label for="editUserLevel" class="block text-sm font-medium">User Level</label>
                <select name="user_level" id="editUserLevel" class="border border-gray-300 p-2 w-full">
                    <option value="1">Admin</option>
                    <option value="2">Manager</option>
                    <option value="3">User</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Save Changes</button>
            <button type="button" onclick="closeEditModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
        <p class="mb-4">Are you sure you want to delete this user?</p>
        <form action="manage_users.php" method="POST" id="deleteForm">
            <input type="hidden" name="delete_id" id="deleteUserId">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
            <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
        </form>
    </div>
</div>

<script>
    // Function to open the edit modal
    function openEditModal(id, name, username, userLevel) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editUsername').value = username;
        document.getElementById('editUserLevel').value = userLevel;
        document.getElementById('editModal').classList.remove('hidden');
    }

    // Function to close the edit modal
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
    // Open the delete modal and set the user ID to delete
    function openDeleteModal(userId) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    // Close the delete modal without submitting
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Open the Add User Modal
    function openAddUserModal() {
        document.getElementById('addUserModal').classList.remove('hidden');
    }

    // Close the Add User Modal
    function closeAddUserModal() {
        document.getElementById('addUserModal').classList.add('hidden');
    }
</script>

<?php
// Include the layout (header, sidebar, footer)
include '../layouts/layout.php';
?>
