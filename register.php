<?php
session_start();
require_once 'database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $database = new Database();
    $db = $database->getConnection();
    
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match!";
    } else {
        $check = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->rowCount() > 0) {
            $error = "Username or email already taken!";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $role = 'user';
            try {
                $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
                $stmt = $db->prepare($query);
                if ($stmt->execute([$username, $email, $hashed, $role])) {
                    $success = "Registration successful! <a href='login.php' class='font-semibold underline hover:no-underline'>Login here</a>";
                } else {
                    $error = "Registration failed. Please try again.";
                }
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BlogApp</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .transition-smooth {
            transition: all 0.2s ease;
        }
        .password-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen flex flex-col">

    <!-- Navigation -->
    <header class="bg-white/80 backdrop-blur-sm shadow-sm sticky top-0 z-10 border-b border-gray-200/50">
        <nav class="container mx-auto px-6 py-4 flex justify-between items-center">
            <div class="logo">
                <a href="index.php" class="text-2xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent hover:opacity-80 transition">
                    <i class="fas fa-blog text-indigo-600"></i> BlogApp
                </a>
            </div>
            <div class="nav-links flex gap-6">
                <a href="index.php" class="text-gray-600 hover:text-indigo-600 transition flex items-center gap-1">
                    <i class="fas fa-home text-sm"></i> Home
                </a>
                <a href="login.php" class="text-gray-600 hover:text-indigo-600 transition flex items-center gap-1">
                    <i class="fas fa-sign-in-alt text-sm"></i> Login
                </a>
                <a href="register.php" class="text-indigo-600 font-semibold flex items-center gap-1 border-b-2 border-indigo-600 pb-0.5">
                    <i class="fas fa-user-plus text-sm"></i> Register
                </a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <div class="flex-grow flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <!-- Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 transition-all duration-300 hover:shadow-2xl">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-8 text-center">
                    <i class="fas fa-user-plus text-4xl text-white opacity-90 mb-2"></i>
                    <h2 class="text-2xl font-bold text-white">Create New Account</h2>
                    <p class="text-indigo-100 text-sm mt-1">Join the BlogApp community</p>
                </div>

                <div class="p-6 md:p-8">
                    <!-- Alert Messages -->
                    <?php if($error): ?>
                        <div class="mb-6 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg flex items-start gap-3 animate-pulse">
                            <i class="fas fa-exclamation-circle mt-0.5"></i>
                            <span><?php echo $error; ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if($success): ?>
                        <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-r-lg flex items-start gap-3">
                            <i class="fas fa-check-circle mt-0.5"></i>
                            <span><?php echo $success; ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="" class="space-y-5">
                        <!-- Username -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2 flex items-center gap-2">
                                <i class="fas fa-user text-indigo-500 text-sm"></i> Username
                            </label>
                            <input type="text" name="username" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-smooth outline-none"
                                   placeholder="Choose a username">
                        </div>
                        <!-- Email -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2 flex items-center gap-2">
                                <i class="fas fa-envelope text-indigo-500 text-sm"></i> Email Address
                            </label>
                            <input type="email" name="email" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-smooth outline-none"
                                   placeholder="you@example.com">
                        </div>
                        <!-- Password with toggle -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2 flex items-center gap-2">
                                <i class="fas fa-lock text-indigo-500 text-sm"></i> Password (min. 6 characters)
                            </label>
                            <div class="relative">
                                <input type="password" name="password" id="password" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-smooth outline-none pr-10"
                                       placeholder="••••••">
                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-indigo-600 transition">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>
                        <!-- Confirm Password -->
                        <div>
                            <label class="block text-gray-700 font-medium mb-2 flex items-center gap-2">
                                <i class="fas fa-check-circle text-indigo-500 text-sm"></i> Confirm Password
                            </label>
                            <div class="relative">
                                <input type="password" name="confirm_password" id="confirmPassword" required 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-smooth outline-none pr-10"
                                       placeholder="••••••">
                                <button type="button" id="toggleConfirmPassword" class="absolute inset-y-0 right-3 flex items-center text-gray-400 hover:text-indigo-600 transition">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" 
                                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 shadow-md hover:shadow-lg">
                            <i class="fas fa-user-plus"></i> Register
                        </button>
                    </form>

                    <p class="text-center text-gray-500 text-sm mt-6">
                        Already have an account? 
                        <a href="login.php" class="text-indigo-600 hover:text-indigo-800 font-medium transition">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-white/80 backdrop-blur-sm border-t border-gray-200/50 py-6 mt-auto">
        <div class="container mx-auto px-6 text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> BlogApp. All rights reserved.</p>
        </div>
    </footer>

    <!-- Password Toggle Script -->
    <script>
        // Toggle for main password
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye-slash');
            this.querySelector('i').classList.toggle('fa-eye');
        });

        // Toggle for confirm password
        const toggleConfirm = document.getElementById('toggleConfirmPassword');
        const confirmInput = document.getElementById('confirmPassword');
        toggleConfirm.addEventListener('click', function() {
            const type = confirmInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmInput.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye-slash');
            this.querySelector('i').classList.toggle('fa-eye');
        });
    </script>
</body>
</html>