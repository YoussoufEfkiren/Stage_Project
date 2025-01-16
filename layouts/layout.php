<!DOCTYPE html>
<!-- layouts/layout.php -->
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">

        <!-- Sidebar -->
        <aside class="bg-blue-800 text-white w-64 flex flex-col">
            <div class="p-4 text-center">
                <h1 class="text-2xl font-bold">Dashboard</h1>
                <p class="text-sm"><?php echo htmlspecialchars($user_group); ?> Panel</p>
            </div>
            <nav class="flex-grow">
                <ul class="space-y-4 px-4">
                    <?php if ($user_level == 1): ?>
                        <li><a href="../layouts/dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700">Dashboard</a></li>
                        <li>
                            <button id="manage-users-btn" class="w-full text-left py-2 px-4 rounded hover:bg-blue-700 flex justify-between items-center">
                                Manage Particepants
                                <svg id="arrow-icon" class="w-4 h-4 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.294a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="manage-users-submenu" class="hidden space-y-2 mt-2 ml-4">
                                <li><a href="../User_Management/manage_users.php" class="block py-2 px-4 rounded hover:bg-blue-700">Manage Users</a></li>
                                <li><a href="../User_Management/group_manage.php" class="block py-2 px-4 rounded hover:bg-blue-700">Manage Groupes</a></li>
                            </ul>
                        </li>
                        <li><a href="view_reports.php" class="block py-2 px-4 rounded hover:bg-blue-700">View Reports</a></li>
                        <li><a href="settings.php" class="block py-2 px-4 rounded hover:bg-blue-700">System Settings</a></li>
                    <?php elseif ($user_level == 2): ?>
                        <li><a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700">Dashboard</a></li>
                        <li><a href="view_products.php" class="block py-2 px-4 rounded hover:bg-blue-700">View Products</a></li>
                        <li><a href="sales_reports.php" class="block py-2 px-4 rounded hover:bg-blue-700">View Sales Reports</a></li>
                    <?php elseif ($user_level == 3): ?>
                        <li><a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700">Dashboard</a></li>
                        <li><a href="view_products.php" class="block py-2 px-4 rounded hover:bg-blue-700">View Products</a></li>
                        <li><a href="purchase_history.php" class="block py-2 px-4 rounded hover:bg-blue-700">View Purchase History</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>


        <!-- Main Content -->
        <main class="flex-grow">
            <header class="bg-white shadow p-4 flex justify-between items-center">
                <div class="text-xl font-bold">Welcome, <?php echo htmlspecialchars($user_name); ?>!</div>
                <div class="relative">
                    <button id="profile-btn" class="flex items-center space-x-2 bg-gray-200 p-2 rounded hover:bg-gray-300">
                        <img src="https://via.placeholder.com/40" alt="Profile" class="w-8 h-8 rounded-full">
                        <span><?php echo htmlspecialchars($user_name); ?></span>
                    </button>
                    <!-- Updated Profile Menu in Layout -->
                    <div id="profile-menu" class="absolute right-0 mt-2 bg-white shadow-md rounded w-48 hidden">
                        <div class="p-4">
                            <p class="font-bold"><?php echo htmlspecialchars($user_name); ?></p>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user_group); ?></p>
                        </div>
                        <hr>
                        <a href="../User_Management/profile.php" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">View Profile</a>
                        <a href="../Authentification_management/logout.php" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </header>

            <!-- Dynamic Content -->
            <div class="p-6">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle Profile Menu
        const profileBtn = document.getElementById('profile-btn');
        const profileMenu = document.getElementById('profile-menu');

        profileBtn.addEventListener('click', () => {
            profileMenu.classList.toggle('hidden'); // Show/hide the menu
        });

        // Close menu if clicked outside
        document.addEventListener('click', (event) => {
            if (!profileMenu.contains(event.target) && !profileBtn.contains(event.target)) {
                profileMenu.classList.add('hidden'); // Ensure it is hidden
            }
        });
    </script>
    <script>
        // Toggle Manage Users Submenu
        const manageUsersBtn = document.getElementById('manage-users-btn');
        const manageUsersSubmenu = document.getElementById('manage-users-submenu');
        const arrowIcon = document.getElementById('arrow-icon');

        manageUsersBtn.addEventListener('click', () => {
            manageUsersSubmenu.classList.toggle('hidden'); // Show/hide the submenu
            arrowIcon.classList.toggle('rotate-180'); // Rotate the arrow icon
        });
    </script>

</body>
</html>
