<?php
//login.php
session_start();
require_once '../includes/config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign POST data
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    try {
        // Prepare the SQL query to fetch the user based on the username
        $stmt = $pdo->prepare("SELECT id, username, password, user_level FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        // Fetch the user record
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Check if user exists and verify the password
        if ($user && password_verify($password, $user['password'])) {
            // Password is correct, start a session and set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level'];

            // Optionally, update last login time
            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $updateStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            // Redirect to the dashboard or home page
            header('Location: ../layouts/dashboard.php');
            exit;
        } else {
            // Invalid credentials
            $error_message = "Invalid username or password.";
        }
    } catch (PDOException $e) {
        // Log and display the error
        error_log($e->getMessage());
        $error_message = "An error occurred. Please try again later.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="flex items-center justify-center h-screen">
        <div class="bg-white p-8 rounded-lg shadow-md w-96">
            <h2 class="text-2xl font-bold mb-4 text-center">Login</h2>
            <?php if (isset($error_message)): ?>
                <div class="bg-red-500 text-white p-2 rounded mb-4">
                    <?= htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <form action="login.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-semibold text-gray-700">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-2 border rounded mt-2" required>
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-semibold text-gray-700">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border rounded mt-2" required>
                </div>
                <button type="submit" class="w-full bg-blue-500 text-white py-2 rounded hover:bg-blue-600">Login</button>
            </form>
        </div>
    </div>
</body>
</html>
