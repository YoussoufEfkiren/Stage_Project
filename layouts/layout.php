<?php
// Check if session is active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default values in case the session variables are not set
$user_group = $_SESSION['user_group'] ?? 'Guest';
$user_level = $_SESSION['user_level'] ?? 0;
$user_name = $_SESSION['user_name'] ?? 'Guest';

// Determine role based on user level
$roles = [
    1 => 'Admin',
    2 => 'Manager',
    3 => 'User',
];
$user_role = $roles[$user_level] ?? 'Guest';

// Include database configuration
require_once '../includes/config.php';

// Get user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check for profile image
$profile_image = !empty($user['image']) ? $user['image'] : 'default-profile.jpg';

// Check if the 'group' field exists
if (!$user || !isset($user['group'])) {
    $user['group'] = 'No Group';
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
        }

        .sidebar-transition {
            transition: width 0.3s ease-in-out;
        }
    </style>
</head>

<body class="h-full">
    <div class="flex h-screen bg-gray-100">
        <!-- Sidebar -->
        <aside id="sidebar"
            class="bg-gradient-to-b from-blue-800 to-blue-600 text-white w-64 flex flex-col shadow-xl transition-all duration-300 ease-in-out sidebar-transition">
            <div class="p-6 text-center">
                <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-sm text-blue-200 mt-1"><?php echo htmlspecialchars($user_role); ?> Panel</p>
            </div>
            <nav class="flex-grow overflow-y-auto">
                <ul class="space-y-2 px-4">
                    <?php if ($user_level == 1): ?>
                        <li>
                            <a href="../layouts/dashboard.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i
                                    class="fas fa-tachometer-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Dashboard</span>
                            </a>
                        </li>
                        <!-- Manage Participants Dropdown -->
                        <li>
                            <button id="participants-btn"
                                class="w-full text-left py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex justify-between items-center group">
                                <span class="flex items-center">
                                    <i class="fas fa-users mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                    <span class="group-hover:translate-x-1 transition-transform">Manage Participants</span>
                                </span>
                                <svg id="participants-arrow-icon"
                                    class="w-4 h-4 transform transition-transform duration-200"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="participants-submenu" class="hidden space-y-2 mt-2 ml-6">
                                <li>
                                    <a href="../User_Management/manage_users.php"
                                        class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                        <i
                                            class="fas fa-user-cog mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                        <span class="group-hover:translate-x-1 transition-transform">Manage Users</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../User_Management/group_manage.php"
                                        class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                        <i
                                            class="fas fa-user-plus mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                        <span class="group-hover:translate-x-1 transition-transform">Manage Groups</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li>
                            <a href="../Product_management/view_products.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i class="fas fa-boxes mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">View Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="../Product_management/view_category.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i class="fas fa-list-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">View Category</span>
                            </a>
                        </li>
                        <!-- Reports Dropdown for level 1 -->
                        <li>
                            <button id="reports-btn-level1"
                                class="w-full text-left py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex justify-between items-center group">
                                <span class="flex items-center">
                                    <i
                                        class="fas fa-file-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                    <span class="group-hover:translate-x-1 transition-transform">Reports</span>
                                </span>
                                <svg id="reports-arrow-icon-level1"
                                    class="w-4 h-4 transform transition-transform duration-200"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="reports-submenu-level1" class="hidden space-y-2 mt-2 ml-6">
                                <li>
                                    <a href="../Reports/view_reports.php"
                                        class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                        <i
                                            class="fas fa-eye mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                        <span class="group-hover:translate-x-1 transition-transform">View Reports</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../Reports/view_reports_dates.php"
                                        class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                        <i
                                            class="fas fa-calendar-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                        <span class="group-hover:translate-x-1 transition-transform">View Reports by
                                            Dates</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Suppliers Management link -->
                        <li>
                            <a href="../User_management/supplier_manage.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i class="fas fa-list-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Supplier Management</span>
                            </a>
                        </li>
                        <li>
                            <a href="../Sells_management/sells.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group 
                                        <?php echo basename($_SERVER['PHP_SELF']) === 'sells.php' ? 'bg-blue-700 text-white' : ''; ?>">
                                <i
                                    class="fas fa-hand-holding-usd mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Sells</span>
                            </a>
                        </li>

                    <?php elseif ($user_level == 2): ?>
                        <li>
                            <a href="../layouts/dashboard.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i
                                    class="fas fa-tachometer-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="../Product_management/view_products.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i class="fas fa-boxes mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">View Products</span>
                            </a>
                        </li>
                        <li>
                            <a href="../Product_management/view_category.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i class="fas fa-list-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">View Category</span>
                            </a>
                        </li>
                        <!-- Suppliers Management link -->
                        <li>
                            <a href="../User_management/supplier_manage.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i class="fas fa-list-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Supplier Management</span>
                            </a>
                        </li>
                    <?php elseif ($user_level == 3): ?>
                        <li>
                            <a href="../layouts/dashboard.php"
                                class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                <i
                                    class="fas fa-tachometer-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Dashboard</span>
                            </a>
                        </li>
                        <!-- Reports Dropdown for level 3 -->
                        <li>
                            <button id="reports-btn-level3"
                                class="w-full text-left py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex justify-between items-center group">
                                <span class="flex items-center">
                                    <i
                                        class="fas fa-file-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                    <span class="group-hover:translate-x-1 transition-transform">Reports</span>
                                </span>
                                <svg id="reports-arrow-icon-level3"
                                    class="w-4 h-4 transform transition-transform duration-200"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="reports-submenu-level3" class="hidden space-y-2 mt-2 ml-6">
                                <li>
                                    <a href="../Reports/view_reports.php"
                                        class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                        <i
                                            class="fas fa-eye mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                        <span class="group-hover:translate-x-1 transition-transform">View Reports</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="../Reports/view_reports_dates.php"
                                        class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group">
                                        <i
                                            class="fas fa-calendar-alt mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                        <span class="group-hover:translate-x-1 transition-transform">View Reports by
                                            Dates</span>
                                    </a>
                                </li>


                            </ul>
                        <li>
                            <a href="../Sells_management/sells.php" class="block py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out flex items-center group 
              <?php echo basename($_SERVER['PHP_SELF']) === 'sells.php' ? 'bg-blue-700 text-white' : ''; ?>">
                                <i
                                    class="fas fa-hand-holding-usd mr-3 text-blue-300 group-hover:text-white transition-colors"></i>
                                <span class="group-hover:translate-x-1 transition-transform">Sells</span>
                            </a>
                        </li>

                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="p-4 border-t border-blue-700">
            </div>
        </aside>
        <main class="flex-grow overflow-hidden">
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center space-x-4">
                        <button id="collapse-sidebar"
                            class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                            <i class="fas fa-bars text-gray-600"></i>
                        </button>

                        <h2 class="text-2xl font-semibold text-gray-800">Welcome,
                            <?php echo htmlspecialchars($user['name']); ?>!</h2>
                        <!-- Collapse button moved here -->

                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative inline-block text-left">
                        <div>
                            <button type="button" id="profile-btn"
                                class="flex items-center space-x-3 group focus:outline-none">
                                <div
                                    class="flex items-center space-x-3 bg-gray-100 rounded-full px-4 py-2 hover:bg-gray-200 transition-colors duration-200">
                                    <img src="../User_Management/uploads/<?php echo htmlspecialchars($profile_image); ?>"
                                        alt="Profile"
                                        class="w-10 h-10 rounded-full object-cover border-2 border-white shadow-sm">
                                    <span
                                        class="text-gray-700 font-medium"><?php echo htmlspecialchars($user['name']); ?></span>
                                    <svg class="w-5 h-5 text-gray-500 group-hover:text-gray-700 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </button>
                        </div>

                        <!-- Profile Dropdown Menu -->
                        <div id="profile-menu"
                            class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100">
                            <div class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo htmlspecialchars($user['name']); ?></p>
                                <p class="text-sm text-gray-500 truncate">
                                    <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            </div>
                            <div class="py-1">
                                <a href="../User_Management/profile.php"
                                    class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-circle mr-2"></i> View Profile
                                </a>
                                <a href="../Authentification_management/logout.php"
                                    class="group flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div class="p-6 overflow-y-auto h-[calc(100vh-4rem)]">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const collapseSidebarBtn = document.getElementById('collapse-sidebar');
            const collapseSidebarIcon = collapseSidebarBtn.querySelector('i');

            let isCollapsed = false;

            // Function to toggle the sidebar width and icon
            function toggleSidebar() {
                isCollapsed = !isCollapsed;
                sidebar.style.width = isCollapsed ? '5rem' : '16rem'; // Adjust width on collapse/expand
                collapseSidebarIcon.classList.toggle('fa-bars', isCollapsed);  // Toggle "bars" icon
                collapseSidebarIcon.classList.toggle('fa-times', !isCollapsed);  // Toggle "times" icon

                // Toggle visibility of text elements within sidebar
                const textElements = sidebar.querySelectorAll('.text-center, nav ul li a span, nav ul li button span');
                textElements.forEach(el => {
                    el.style.display = isCollapsed ? 'none' : 'inline';  // Hide text on collapse
                });
            }

            collapseSidebarBtn.addEventListener('click', toggleSidebar);  // Event listener for sidebar toggle
        });


        // Profile dropdown functionality
        const profileBtn = document.getElementById('profile-btn');
        const profileMenu = document.getElementById('profile-menu');
        let isProfileMenuOpen = false;

        function toggleProfileMenu() {
            isProfileMenuOpen = !isProfileMenuOpen;
            profileMenu.classList.toggle('hidden', !isProfileMenuOpen);
        }

        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            toggleProfileMenu();
        });

        // Close profile menu when clicking outside
        document.addEventListener('click', (e) => {
            if (isProfileMenuOpen && !profileMenu.contains(e.target)) {
                toggleProfileMenu();
            }
        });

        // Prevent menu close when clicking inside the menu
        profileMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // Dropdown toggles for sidebar
        const dropdowns = {
            'participants-btn': 'participants-submenu',
            'reports-btn-level1': 'reports-submenu-level1',
            'reports-btn-level3': 'reports-submenu-level3',
            'sells-btn': 'sells-submenu'
        };

        Object.entries(dropdowns).forEach(([btnId, submenuId]) => {
            const btn = document.getElementById(btnId);
            const submenu = document.getElementById(submenuId);

            if (btn && submenu) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const arrowIcon = btn.querySelector('svg');
                    submenu.classList.toggle('hidden');
                    arrowIcon?.classList.toggle('rotate-180');
                });
            }
        });

        // Close dropdowns when clicking outside
        document.addEventListener('click', () => {
            Object.values(dropdowns).forEach(submenuId => {
                const submenu = document.getElementById(submenuId);
                if (submenu && !submenu.classList.contains('hidden')) {
                    submenu.classList.add('hidden');
                    const btn = document.getElementById(Object.keys(dropdowns).find(key => dropdowns[key] === submenuId));
                    const arrowIcon = btn?.querySelector('svg');
                    arrowIcon?.classList.remove('rotate-180');
                }
            });
        });
    </script>
</body>

</html>