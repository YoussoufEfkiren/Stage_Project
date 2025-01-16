<?php
// group_manage.php

session_start();

// Check if the user is logged in and has a valid session
if (!isset($_SESSION['user_id'])) {
    header('Location: ../Authentification_management/login.php');
    exit;
}

// Fetch user details from the session
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['username'];  // Assuming username is stored in session
$user_level = $_SESSION['user_level'];  // Assuming user_level is stored in session

// Optionally, you can fetch the user group or role from the database
// Example for fetching user group based on user level
switch ($user_level) {
    case 1:
        $user_group = 'Admin';
        break;
    case 2:
        $user_group = 'Manager';
        break;
    case 3:
        $user_group = 'User';
        break;
    default:
        $user_group = 'Guest';
}

$page_title = 'Manage Groups';

// Include the database connection
require_once '../includes/config.php';

// Fetch all user groups from the `user_groups` table
$sql = "SELECT id, group_name FROM user_groups";
$stmt = $pdo->query($sql);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deleting a group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $group_id_to_delete = $_POST['delete_id'];

    // Validate the group ID
    if (is_numeric($group_id_to_delete)) {
        $sql = "DELETE FROM user_groups WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute([':id' => $group_id_to_delete])) {
            $_SESSION['message'] = 'Group deleted successfully!';
            header('Location: group_manage.php');
            exit;
        } else {
            $_SESSION['message'] = 'Error deleting group!';
        }
    }
}

// Handle adding a new group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_group'])) {
    // Collect form data
    $group_name = $_POST['group_name'];

    // Prepare SQL query to insert new group
    $sql = "INSERT INTO user_groups (group_name) VALUES (:group_name)";
    $stmt = $pdo->prepare($sql);

    // Execute the query with the form data
    if ($stmt->execute([':group_name' => $group_name])) {
        $_SESSION['message'] = 'Group added successfully!';
        header('Location: group_manage.php');
        exit;
    } else {
        $_SESSION['message'] = 'Error adding group!';
    }
}

// Build content
$content = '
    <div class="p-4">
        <h1 class="text-3xl font-semibold mb-6">Manage Groups</h1>';

if (isset($_SESSION['message'])) {
    $content .= '<div class="text-green-500 p-4">' . $_SESSION['message'] . '</div>';
    unset($_SESSION['message']);
}

// Render groups in a table
$content .= '
        <table class="w-full table-auto border-collapse">
            <thead>
                <tr>
                    <th class="py-2 px-4 border border-gray-300">ID</th>
                    <th class="py-2 px-4 border border-gray-300">Group Name</th>
                    <th class="py-2 px-4 border border-gray-300">Actions</th>
                </tr>
            </thead>
            <tbody>';

if (count($groups) > 0) {
    foreach ($groups as $group) {
        $content .= '
            <tr>
                <td class="py-2 px-4 border border-gray-300">' . $group['id'] . '</td>
                <td class="py-2 px-4 border border-gray-300">' . htmlspecialchars($group['group_name']) . '</td>
                <td class="py-2 px-4 border border-gray-300">
                
                </td>
            </tr>';
    }
} else {
    $content .= '<tr><td colspan="3" class="text-red-500 text-center">No groups found</td></tr>';
}

$content .= '
            </tbody>
        </table>
    </div>';  // End content div
?>

<!-- Add Group Modal -->
<div id="addGroupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Add New Group</h2>
        <form action="group_manage.php" method="POST">
            <div class="mb-4">
                <label for="group_name" class="block font-semibold">Group Name</label>
                <input type="text" name="group_name" id="group_name" class="w-full px-4 py-2 border border-gray-300 rounded mt-2" required>
            </div>
            <div class="flex justify-end">
                <button type="submit" name="add_group" class="bg-blue-500 text-white px-4 py-2 rounded">Add Group</button>
                <button type="button" onclick="closeAddGroupModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal for editing group -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Edit Group</h2>
        <form action="group_manage.php" method="POST">
            <input type="hidden" name="id" id="editGroupId">
            <div class="mb-4">
                <label for="editGroupName" class="block text-sm font-medium">Group Name</label>
                <input type="text" name="group_name" id="editGroupName" class="border border-gray-300 p-2 w-full">
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
        <p class="mb-4">Are you sure you want to delete this group?</p>
        <form action="group_manage.php" method="POST" id="deleteForm">
            <input type="hidden" name="delete_id" id="deleteGroupId">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
            <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
        </form>
    </div>
</div>

<script>
    // Function to open the edit modal
    function openEditModal(id, groupName) {
        document.getElementById('editGroupId').value = id;
        document.getElementById('editGroupName').value = groupName;
        document.getElementById('editModal').classList.remove('hidden');
    }

    // Function to close the edit modal
    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Open the delete modal and set the group ID to delete
    function openDeleteModal(groupId) {
        document.getElementById('deleteGroupId').value = groupId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    // Close the delete modal without submitting
    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Open the Add Group Modal
    function openAddGroupModal() {
        document.getElementById('addGroupModal').classList.remove('hidden');
    }

    // Close the Add Group Modal
    function closeAddGroupModal() {
        document.getElementById('addGroupModal').classList.add('hidden');
    }
</script>

<?php
// Include the layout (header, sidebar, footer)
include '../layouts/layout.php';
?>
