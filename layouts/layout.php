<?php
// Check if session is active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Default values in case the session variables are not set
$user_group = $_SESSION['user_group'] ?? 'Guest';
$user_level = $_SESSION['user_level'] ?? 0; // Assume 0 as the default level
$user_name = $_SESSION['user_name'] ?? 'Guest';
?>
<?php
// Inclure la configuration de la base de données
require_once '../includes/config.php';

// Obtenir les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Vérifier si l'utilisateur a une image de profil
$profile_image = !empty($user['profile_image']) ? $user['profile_image'] : 'default-profile.jpg';
?>
<?php
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Check if the 'group' field exists
if (!$user || !isset($user['group'])) {
    $user['group'] = 'No Group'; // Default value if 'group' is not found
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                        <li>
                            <a href="../layouts/dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        </li>
                        <!-- Manage Participants Dropdown -->
                        <li>
                            <button id="participants-btn" class="w-full text-left py-2 px-4 rounded hover:bg-blue-700 flex justify-between items-center">
                                <span class="flex items-center">
                                    <i class="fas fa-users mr-2"></i> Manage Participants
                                </span>
                                <svg id="participants-arrow-icon" class="w-4 h-4 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.294a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="participants-submenu" class="hidden space-y-2 mt-2 ml-4">
                                <li>
                                    <a href="../User_Management/manage_users.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-user-cog mr-2"></i> Manage Users
                                    </a>
                                </li>
                                <li>
                                    <a href="../User_Management/group_manage.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-user-plus mr-2"></i> Manage Groups
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <li>
                            <a href="../Product_management/view_products.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-boxes mr-2"></i> View Products
                            </a>
                        </li>
                        <li>
                            <a href="../Product_management/view_category.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-list-alt mr-2"></i> View Category
                            </a>
                        </li>
                        <!-- Reports Dropdown for level 1 -->
                        <li>
                            <button id="reports-btn-level1" class="w-full text-left py-2 px-4 rounded hover:bg-blue-700 flex justify-between items-center">
                                <span class="flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i> Reports
                                </span>
                                <svg id="reports-arrow-icon-level1" class="w-4 h-4 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.294a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="reports-submenu-level1" class="hidden space-y-2 mt-2 ml-4">
                                <li>
                                    <a href="/Reports/view_reports.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-eye mr-2"></i> View Reports
                                    </a>
                                </li>
                                <li>
                                    <a href="/Reports/view_reports_dates.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-calendar-alt mr-2"></i> View Reports by Dates
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!-- Sells for level 1 -->
                        <li>
                            <button id="sells-btn" class="w-full text-left py-2 px-4 rounded hover:bg-blue-700 flex justify-between items-center">
                                <span class="flex items-center">
                                    <i class="fas fa-hand-holding-usd mr-2"></i> Sells
                                </span>
                                <svg id="sells-arrow-icon" class="w-4 h-4 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.294a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="sells-submenu" class="hidden space-y-2 mt-2 ml-4">
                                <li>
                                    <a href="../Product_management/sells.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-hand-holding-usd mr-2"></i> View Sells
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php elseif ($user_level == 2): ?>
                        <li>
                            <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="../Product_management/view_products.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-boxes mr-2"></i> View Products
                            </a>
                        </li>
                        <li>
                            <a href="../Product_management/view_category.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-list-alt mr-2"></i> View Category
                            </a>
                        </li>
                    <?php elseif ($user_level == 3): ?>
                        <li>
                            <a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                           <a href="../Product_management/sells.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                 <i class="fas fa-hand-holding-usd mr-2"></i> Sells
                             </a>
                        </li>

                        <!-- Reports Dropdown for level 3 only -->
                        <li>
                            <button id="reports-btn-level1" class="w-full text-left py-2 px-4 rounded hover:bg-blue-700 flex justify-between items-center">
                                <span class="flex items-center">
                                    <i class="fas fa-file-alt mr-2"></i> Reports
                                </span>
                                <svg id="reports-arrow-icon-level1" class="w-4 h-4 transform transition-transform" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.292 7.292a1 1 0 011.414 0L10 10.586l3.293-3.294a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <ul id="reports-submenu-level1" class="hidden space-y-2 mt-2 ml-4">
                                <li>
                                    <a href="/Reports/view_reports.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-eye mr-2"></i> View Reports
                                    </a>
                                </li>
                                <li>
                                    <a href="/Reports/view_reports_dates.php" class="block py-2 px-4 rounded hover:bg-blue-700 flex items-center">
                                        <i class="fas fa-calendar-alt mr-2"></i> View Reports by Dates
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

       <!-- Main Content -->
<main class="flex-grow">
    <header class="bg-white shadow p-4 flex justify-between items-center">
        <div class="text-xl font-bold">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</div>
        <div class="relative">
            <button id="profile-btn" class="flex items-center space-x-2 bg-gray-200 p-2 rounded hover:bg-gray-300">
                <!-- Utilisation de l'image du profil -->
                <img src="../uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile" class="w-8 h-8 rounded-full">
                <span><?php echo htmlspecialchars($user['name']); ?></span>
            </button>
            <div id="profile-menu" class="absolute right-0 mt-2 bg-white shadow-md rounded w-48 hidden">
                <div class="p-4">
                    <p class="font-bold"><?php echo htmlspecialchars($user['name']); ?></p>
                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user['group']); ?></p>
                </div>
                <hr>
                <a href="../User_Management/profile.php" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">View Profile</a>
                <a href="../Authentification_management/logout.php" class="block px-4 py-2 text-sm text-blue-600 hover:bg-gray-100">Logout</a>
            </div>
        </div>
    </header>
    <div class="p-6">
        <?php echo $content; ?>
    </div>
</main>
    </div>

    <!-- JavaScript -->
    <script>
    const profileBtn = document.getElementById('profile-btn');
    const profileMenu = document.getElementById('profile-menu');
    profileBtn.addEventListener('click', () => {
        profileMenu.classList.toggle('hidden');
    });
    document.addEventListener('click', (event) => {
        if (!profileMenu.contains(event.target) && !profileBtn.contains(event.target)) {
            profileMenu.classList.add('hidden');
        }
    });

    // Toggle Reports Dropdown for level 1
    const reportsBtnLevel1 = document.getElementById('reports-btn-level1');
    const reportsSubmenuLevel1 = document.getElementById('reports-submenu-level1');
    const reportsArrowIconLevel1 = document.getElementById('reports-arrow-icon-level1');
    
    if (reportsBtnLevel1) {
        reportsBtnLevel1.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent event from bubbling up
            reportsSubmenuLevel1.classList.toggle('hidden');
            reportsArrowIconLevel1.classList.toggle('rotate-180');
        });
    }

    // Toggle Sells Dropdown for level 1
    const sellsBtnLevel1 = document.getElementById('sells-btn-level1');
    const sellsSubmenuLevel1 = document.getElementById('sells-submenu-level1');
    const sellsArrowIconLevel1 = document.getElementById('sells-arrow-icon-level1');

    if (sellsBtnLevel1) {
        sellsBtnLevel1.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent event from bubbling up
            sellsSubmenuLevel1.classList.toggle('hidden');
            sellsArrowIconLevel1.classList.toggle('rotate-180');
        });
    }

    // Toggle Reports Dropdown for level 3
    const reportsBtnLevel3 = document.getElementById('reports-btn-level3');
    const reportsSubmenuLevel3 = document.getElementById('reports-submenu-level3');
    const reportsArrowIconLevel3 = document.getElementById('reports-arrow-icon-level3');
    
    if (reportsBtnLevel3) {
        reportsBtnLevel3.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent event from bubbling up
            reportsSubmenuLevel3.classList.toggle('hidden');
            reportsArrowIconLevel3.classList.toggle('rotate-180');
        });
    }
    // Toggle Manage Participants Dropdown for level 1
const participantsBtn = document.getElementById('participants-btn');
const participantsSubmenu = document.getElementById('participants-submenu');
const participantsArrowIcon = document.getElementById('participants-arrow-icon');

if (participantsBtn) {
    participantsBtn.addEventListener('click', (event) => {
        event.stopPropagation(); // Prevent event from bubbling up
        participantsSubmenu.classList.toggle('hidden');
        participantsArrowIcon.classList.toggle('rotate-180');
    });
}

// Close the dropdown when clicking outside
document.addEventListener('click', (event) => {
    if (!participantsSubmenu.contains(event.target) && !participantsBtn.contains(event.target)) {
        participantsSubmenu.classList.add('hidden');
        participantsArrowIcon.classList.remove('rotate-180');
    }
});


    // Toggle Sells Dropdown for level 3
    const sellsBtnLevel3 = document.getElementById('sells-btn-level3');
    const sellsSubmenuLevel3 = document.getElementById('sells-submenu-level3');
    const sellsArrowIconLevel3 = document.getElementById('sells-arrow-icon-level3');

    if (sellsBtnLevel3) {
        sellsBtnLevel3.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent event from bubbling up
            sellsSubmenuLevel3.classList.toggle('hidden');
            sellsArrowIconLevel3.classList.toggle('rotate-180');
        });
    }

    // Close all dropdowns when clicking outside
    document.addEventListener('click', (event) => {
        // Close Reports for level 1
        if (!reportsSubmenuLevel1.contains(event.target) && !reportsBtnLevel1.contains(event.target)) {
            reportsSubmenuLevel1.classList.add('hidden');
            reportsArrowIconLevel1.classList.remove('rotate-180');
        }
        
        // Close Sells for level 1
        if (!sellsSubmenuLevel1.contains(event.target) && !sellsBtnLevel1.contains(event.target)) {
            sellsSubmenuLevel1.classList.add('hidden');
            sellsArrowIconLevel1.classList.remove('rotate-180');
        }

        // Close Reports for level 3
        if (!reportsSubmenuLevel3.contains(event.target) && !reportsBtnLevel3.contains(event.target)) {
            reportsSubmenuLevel3.classList.add('hidden');
            reportsArrowIconLevel3.classList.remove('rotate-180');
        }

        // Close Sells for level 3
        if (!sellsSubmenuLevel3.contains(event.target) && !sellsBtnLevel3.contains(event.target)) {
            sellsSubmenuLevel3.classList.add('hidden');
            sellsArrowIconLevel3.classList.remove('rotate-180');
        }
    });
</script>

</body>
</html>