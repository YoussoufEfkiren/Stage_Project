<?php
session_start();
require_once '../includes/config.php';

// Check if the user is logged in and has the necessary permissions (admin level)
if (!isset($_SESSION['user_level']) || $_SESSION['user_level'] != 1) {
    header('Location: ../login.php'); // Redirect to login page if not an admin
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle the form submission to update settings
    if (isset($_POST['site_name'])) {
        $site_name = htmlspecialchars(trim($_POST['site_name']));
        // Update the site name in the database or config file
    }

    if (isset($_POST['default_user_role'])) {
        $default_user_role = (int)$_POST['default_user_role'];
        // Update the default user role in the database or config file
    }

    if (isset($_POST['smtp_server'])) {
        $smtp_server = htmlspecialchars(trim($_POST['smtp_server']));
        // Update SMTP server settings in the config
    }

    // Add additional settings handling as needed

    // Save settings to the database or a config file
    // Optionally, display a success message
    $message = "Settings updated successfully!";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="bg-blue-800 text-white w-64 flex flex-col">
            <div class="p-4 text-center">
                <h1 class="text-2xl font-bold">System Settings</h1>
            </div>
            <nav class="flex-grow">
                <ul class="space-y-4 px-4">
                    <li>
                        <a href="../layouts/dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                            <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                        </a>
                    </li>
                    <!-- Add other sidebar links as necessary -->
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow p-6">
            <header class="bg-white shadow p-4">
                <h2 class="text-2xl font-bold">System Settings</h2>
            </header>

            <?php if (isset($message)): ?>
                <div class="bg-green-500 text-white p-2 rounded mb-4">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <form action="settings.php" method="POST">
                <!-- General Settings -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h3 class="text-xl font-semibold mb-4">General Settings</h3>
                    <div class="mb-4">
                        <label for="site_name" class="block text-sm font-semibold text-gray-700">Site Name</label>
                        <input type="text" id="site_name" name="site_name" class="w-full p-2 border rounded mt-2" value="<?= htmlspecialchars($site_name ?? '') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="site_description" class="block text-sm font-semibold text-gray-700">Site Description</label>
                        <textarea id="site_description" name="site_description" class="w-full p-2 border rounded mt-2" rows="4"><?= htmlspecialchars($site_description ?? '') ?></textarea>
                    </div>
                </div>

                <!-- User Management Settings -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h3 class="text-xl font-semibold mb-4">User Management Settings</h3>
                    <div class="mb-4">
                        <label for="default_user_role" class="block text-sm font-semibold text-gray-700">Default User Role</label>
                        <select id="default_user_role" name="default_user_role" class="w-full p-2 border rounded mt-2">
                            <option value="1" <?= isset($default_user_role) && $default_user_role == 1 ? 'selected' : ''; ?>>Admin</option>
                            <option value="2" <?= isset($default_user_role) && $default_user_role == 2 ? 'selected' : ''; ?>>Manager</option>
                            <option value="3" <?= isset($default_user_role) && $default_user_role == 3 ? 'selected' : ''; ?>>User</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="password_policy" class="block text-sm font-semibold text-gray-700">Password Policy</label>
                        <textarea id="password_policy" name="password_policy" class="w-full p-2 border rounded mt-2" rows="4"><?= htmlspecialchars($password_policy ?? '') ?></textarea>
                    </div>
                </div>

                <!-- Email Settings -->
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <h3 class="text-xl font-semibold mb-4">Email Settings</h3>
                    <div class="mb-4">
                        <label for="smtp_server" class="block text-sm font-semibold text-gray-700">SMTP Server</label>
                        <input type="text" id="smtp_server" name="smtp_server" class="w-full p-2 border rounded mt-2" value="<?= htmlspecialchars($smtp_server ?? '') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="smtp_username" class="block text-sm font-semibold text-gray-700">SMTP Username</label>
                        <input type="text" id="smtp_username" name="smtp_username" class="w-full p-2 border rounded mt-2" value="<?= htmlspecialchars($smtp_username ?? '') ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="smtp_password" class="block text-sm font-semibold text-gray-700">SMTP Password</label>
                        <input type="password" id="smtp_password" name="smtp_password" class="w-full p-2 border rounded mt-2" value="<?= htmlspecialchars($smtp_password ?? '') ?>" required>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 text-white py-2 px-6 rounded hover:bg-blue-600">Save Settings</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
