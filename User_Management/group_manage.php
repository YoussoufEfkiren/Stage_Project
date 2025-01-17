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

// CSRF Protection: Generate a CSRF token if not already set
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Fetch all user groups from the `user_groups` table
$sql = "SELECT id, group_name , group_level, group_status FROM user_groups";
$stmt = $pdo->query($sql);
$groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle deleting a group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    // Check CSRF token
    if (!hash_equals($csrf_token, $_POST['csrf_token'])) {
        die('CSRF validation failed');
    }

    $group_id_to_delete = $_POST['delete_id'];

    // Validate the group ID
    if (is_numeric($group_id_to_delete)) {
        try {
            $sql = "DELETE FROM user_groups WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':id' => $group_id_to_delete]);
            $_SESSION['message'] = 'Group deleted successfully!';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Error deleting group: ' . $e->getMessage();
        }
    }
}

// Handle adding a new group
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_group'])) {
    // Check CSRF token
    if (!hash_equals($csrf_token, $_POST['csrf_token'])) {
        die('CSRF validation failed');
    }

    // Collect form data
    $group_name = $_POST['group_name'];

    // Validate the group name
    if (empty($group_name)) {
        $_SESSION['message'] = 'Group name is required!';
    } else {
        try {
            // Prepare SQL query to insert new group
            $sql = "INSERT INTO user_groups (group_name) VALUES (:group_name)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':group_name' => $group_name]);
            $_SESSION['message'] = 'Group added successfully!';
        } catch (PDOException $e) {
            $_SESSION['message'] = 'Error adding group: ' . $e->getMessage();
        }
    }
}

$content = '
    <div class="p-6 bg-gray-50 min-h-screen">
        <h1 class="text-4xl font-bold mb-6 text-gray-700">Manage Groups</h1>
        
        <!-- Add New Group Button -->
        <button onclick="openAddGroupModal()" class="bg-blue-600 text-white px-6 py-2 rounded-lg mb-6 shadow hover:bg-blue-700 transition-all">
            <i class="fas fa-plus-circle mr-2"></i> Add New Group
        </button>';

if (isset($_SESSION['message'])) {
    $content .= '<div class="text-green-600 p-4 bg-green-100 border border-green-300 rounded mb-4 shadow">
        ' . $_SESSION['message'] . '
    </div>';
    unset($_SESSION['message']);
}

$content .= '
        <div class="overflow-x-auto rounded-lg shadow-lg">
            <table class="min-w-full bg-white border-collapse border border-gray-200">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="py-3 px-4 text-left border-b border-gray-300">ID</th>
                        <th class="py-3 px-4 text-left border-b border-gray-300">Group Name</th>
                        <th class="py-3 px-4 text-center border-b border-gray-300">Group Level</th>
                        <th class="py-3 px-4 text-center border-b border-gray-300">Status</th>
                        <th class="py-3 px-4 text-center border-b border-gray-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700 text-sm">';

if (count($groups) > 0) {
    foreach ($groups as $group) {
        $content .= '
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b border-gray-300">' . $group['id'] . '</td>
                        <td class="py-3 px-4 border-b border-gray-300">' . htmlspecialchars($group['group_name']) . '</td>
                        <td class="py-3 px-4 border-b border-gray-300 text-center">' . $group['group_level'] . '</td>
                        <td class="py-3 px-4 border-b border-gray-300 text-center ' . ($group['group_status'] == 1 ? 'text-green-500' : 'text-red-500') . '">
                            ' . ($group['group_status'] == 1 ? 'Active' : 'Inactive') . '
                        </td>
                        <td class="py-3 px-4 border-b border-gray-300 text-center">
                            <button onclick="openEditModal(' . $group['id'] . ', \'' . addslashes(htmlspecialchars($group['group_name'])) . '\')" class="text-blue-500 hover:underline">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button type="button" onclick="openDeleteModal(' . $group['id'] . ')" class="text-red-500 hover:underline ml-4">
                                <i class="fas fa-trash-alt"></i> Delete
                            </button>
                        </td>
                    </tr>';
    }
} else {
    $content .= '
                    <tr>
                        <td colspan="3" class="py-6 px-4 text-center text-gray-500">No groups found</td>
                    </tr>';
}

$content .= '
                </tbody>
            </table>
        </div>
    </div>';  // End content div
?>

<!-- Modal for adding group -->
<div id="addGroupModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Add New Group</h2>
        <form action="group_manage.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
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

<!-- Modal for deleting group -->
<div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden flex justify-center items-center">
    <div class="bg-white p-6 rounded-lg max-w-sm w-full shadow-lg">
        <h2 class="text-xl font-semibold mb-4">Confirm Deletion</h2>
        <p class="mb-4">Are you sure you want to delete this group?</p>
        <form action="group_manage.php" method="POST" id="deleteForm">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            <input type="hidden" name="delete_id" id="deleteGroupId">
            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
            <button type="button" onclick="closeDeleteModal()" class="bg-gray-500 text-white px-4 py-2 rounded ml-2">Cancel</button>
        </form>
    </div>
</div>

<script>
    // JavaScript for modals
    function openEditModal(id, groupName) {
        document.getElementById('editGroupId').value = id;
        document.getElementById('editGroupName').value = groupName;
        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openDeleteModal(groupId) {
        document.getElementById('deleteGroupId').value = groupId;
        document.getElementById('deleteModal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    function openAddGroupModal() {
        document.getElementById('addGroupModal').classList.remove('hidden');
    }

    function closeAddGroupModal() {
        document.getElementById('addGroupModal').classList.add('hidden');
    }
</script>

<?php
// Include the layout (header, sidebar, footer)
include '../layouts/layout.php';
?>
