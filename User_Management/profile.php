<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Authentification_management/login.php");
    exit();
}

// Include database connection
require_once '../includes/config.php';

// Fetch user information
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Initialize errors array
$errors = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle name update
    if (!empty($_POST['name'])) {
        $name = htmlspecialchars(trim($_POST['name']));
        $update_query = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$name, $user_id]);
        $_SESSION['user_name'] = $name;
    }

    // Handle username update
    if (!empty($_POST['username'])) {
        $username = htmlspecialchars(trim($_POST['username']));
        $update_query = "UPDATE users SET username = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$username, $user_id]);
        $_SESSION['username'] = $username;
    }

    // Handle password update with strength validation
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 8 && preg_match('/[A-Z]/', $new_password) && preg_match('/[0-9]/', $new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = "UPDATE users SET password = ? WHERE id = ?";
                    $stmt = $pdo->prepare($update_query);
                    $stmt->execute([$hashed_password, $user_id]);
                } else {
                    $errors[] = "New password must be at least 8 characters long and include a number and an uppercase letter.";
                }
            } else {
                $errors[] = "New password and confirmation do not match.";
            }
        } else {
            $errors[] = "Current password is incorrect.";
        }
    }

    // Handle profile image upload with preview
    if (!empty($_FILES['profile_image']['name'])) {
        $image_name = $_FILES['profile_image']['name'];
        $image_tmp = $_FILES['profile_image']['tmp_name'];
        $image_ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_ext, $allowed_ext)) {
            $new_image_name = "profile_" . $user_id . "." . $image_ext;
            $upload_path = "../User_Management/uploads/";

            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $full_path = $upload_path . $new_image_name;

            if (move_uploaded_file($image_tmp, $full_path)) {
                $update_query = "UPDATE users SET image = ? WHERE id = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([$new_image_name, $user_id]);
            } else {
                $errors[] = "Failed to upload image.";
            }
        } else {
            $errors[] = "Unsupported image format.";
        }
    }

    // Redirect if no errors
    if (empty($errors)) {
        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: ../User_Management/profile.php");
        exit();
    }
}

// Set page title
$page_title = "Edit Profile";
ob_start(); // Start output buffering
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Toggle modal visibility
        function toggleModal() {
            document.getElementById('editModal').classList.toggle('hidden');
            document.getElementById('modalBackdrop').classList.toggle('hidden');
        }

        // Display image preview before upload
        function previewImage(input) {
            const file = input.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('profileImagePreview').src = e.target.result;
            }

            reader.readAsDataURL(file);
        }

        // Check password strength dynamically
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthMessage = document.getElementById('passwordStrengthMessage');
            let strength = 'Weak';
            if (password.length >= 8 && /[A-Z]/.test(password) && /[0-9]/.test(password)) {
                strength = 'Strong';
            } else if (password.length >= 6) {
                strength = 'Medium';
            }

            strengthMessage.textContent = `Password strength: ${strength}`;
            strengthMessage.style.color = strength === 'Strong' ? 'green' : (strength === 'Medium' ? 'orange' : 'red');
        }
    </script>
</head>

<body class="bg-gray-50">
    <div class="min-h-screen p-4 md:p-8">
        <div class="max-w-4xl mx-auto">
            <!-- Profile Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <!-- Header Banner -->
                <div class="h-32 bg-gradient-to-r from-blue-500 to-blue-600"></div>

                <!-- Profile Content -->
                <div class="px-6 pb-6">
                    <!-- Profile Image -->
                    <div class="relative -mt-16 mb-4">
                        <img
                            id="profileImagePreview"
                            src="<?php echo $user['image'] ? 'uploads/' . htmlspecialchars($user['image']) : '/api/placeholder/128/128'; ?>"
                            alt="Profile Image"
                            class="w-32 h-32 rounded-full border-4 border-white shadow-lg object-cover">
                    </div>

                    <!-- User Info -->
                    <div class="space-y-4">
                        <h1 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($user['name']); ?></h1>
                        <p class="text-gray-500">@<?php echo htmlspecialchars($user['username']); ?></p>

                        <!-- Edit Profile Button -->
                        <button
                            onclick="toggleModal()"
                            class="mt-6 inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Backdrop -->
            <div id="modalBackdrop" class="hidden fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

            <!-- Edit Modal -->
            <div id="editModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-end sm:items-center justify-center p-4 text-center sm:p-0">
                    <div class="relative transform rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:max-w-lg sm:w-full">
                        <div class="px-6 pt-6 pb-4">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Edit Profile</h3>
                                <button onclick="toggleModal()" class="text-gray-400 hover:text-gray-500">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Profile Update Form -->
                            <form method="POST" enctype="multipart/form-data">
                                <!-- Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        value="<?php echo htmlspecialchars($user['name']); ?>"
                                        class="w-full mt-2 rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <!-- Username -->
                                <div class="mt-4">
                                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                    <input
                                        type="text"
                                        name="username"
                                        id="username"
                                        value="<?php echo htmlspecialchars($user['username']); ?>"
                                        class="w-full mt-2 rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <!-- Password -->
                                <div class="mt-4">
                                    <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
                                    <input
                                        type="password"
                                        name="current_password"
                                        id="current_password"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <div class="mt-4">
                                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password</label>
                                    <input
                                        type="password"
                                        name="new_password"
                                        id="new_password"
                                        onkeyup="checkPasswordStrength()"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <p id="passwordStrengthMessage" class="mt-1 text-sm text-gray-600"></p>
                                </div>

                                <div class="mt-4">
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
                                    <input
                                        type="password"
                                        name="confirm_password"
                                        id="confirm_password"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <!-- Profile Image Upload -->
                                <div class="mt-4">
                                    <label for="profile_image" class="block text-sm font-medium text-gray-700">Profile Image</label>
                                    <input
                                        type="file"
                                        name="profile_image"
                                        id="profile_image"
                                        accept="image/*"
                                        onchange="previewImage(this)"
                                        class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>

                                <!-- Image Preview -->
                                <div class="mt-4 text-center">
                                    <img id="profileImagePreview" class="w-32 h-32 rounded-full mx-auto" src="<?php echo $user['image'] ? 'uploads/' . htmlspecialchars($user['image']) : '/api/placeholder/128/128'; ?>" alt="Profile Preview">
                                </div>

                                <!-- Save Changes -->
                                <div class="mt-6 flex justify-end space-x-4">
                                    <button
                                        type="button"
                                        onclick="toggleModal()"
                                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-lg">
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                        Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
$content = ob_get_clean();
include '../layouts/layout.php';
?>