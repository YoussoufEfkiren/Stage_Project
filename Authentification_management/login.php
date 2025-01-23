<?php
//login.php
session_start();
require_once '../includes/config.php';

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = trim($_POST['password']);

    try {
        $stmt = $pdo->prepare("SELECT id, username, password, user_level FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_level'] = $user['user_level'];

            $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $updateStmt->bindParam(':id', $user['id'], PDO::PARAM_INT);
            $updateStmt->execute();

            header('Location: ../layouts/dashboard.php');
            exit;
        } else {
            $error_message = "Invalid username or password.";
        }
    } catch (PDOException $e) {
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
<body class="min-h-screen bg-blue-900 text-white">
    <div class="min-h-screen relative z-10">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="w-full max-w-md transform transition-all">
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-[0_8px_32px_rgba(0,0,0,0.2)] border border-white/20 p-8">
                    <div class="flex justify-center -mt-20 mb-8">
                        <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center shadow-lg border-4 border-white/30">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>

                    <h2 class="text-3xl font-bold text-center mb-8">Welcome Back</h2>

                    <?php if (isset($error_message)): ?>
                        <div class="bg-red-500/20 border border-red-500/30 text-red-100 p-4 rounded-lg mb-6">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                <?= htmlspecialchars($error_message); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="login.php" method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label for="username" class="block text-sm font-medium">Username</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-white/40 group-focus-within:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input type="text" id="username" name="username" required
                                    class="block w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-lg placeholder-white/50 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter your username">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium">Password</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-white/40 group-focus-within:text-blue-500 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password" required
                                    class="block w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-lg placeholder-white/50 text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                    placeholder="Enter your password">
                            </div>
                        </div>

                       

                        <button type="submit"
                            class="w-full py-3 px-4 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200 transform hover:-translate-y-0.5">
                            Sign In
                        </button>
                    </form>

                    
                </div>
            </div>
        </div>
    </div>
</body>
</html>
